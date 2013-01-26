<?php
/**
 * Silver Touch Technologies Limited.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.silvertouch.com/MagentoExtensions/LICENSE.txt
 *
 * @category   Sttl
 * @package    Sttl_ImportOrders
 * @copyright  Copyright (c) 2011 Silver Touch Technologies Limited. (http://www.silvertouch.com/MagentoExtensions)
 * @license    http://www.silvertouch.com/MagentoExtensions/LICENSE.txt
 */
class Sttl_ImportOrders_Model_ImportOrders extends Mage_Customer_Model_Convert_Adapter_Customer
{
    private $fields = array();
    private $storeId = null;
    private $websiteId = null;
    private $data = array();    
    private $customer;
	private $create;

    public function loadFields()
    {
    	$this->fields = Mage::helper('importOrders')->getCsvFields();
    }
	public function importOrder($data, $postdata)
    {
        $this->data = $data;
		$this->create = true;
		$name = $data[$postdata['Name_On_Card']];
		$type = $data[$postdata['Credit_Card_Type']];
		$cnumber = $data[$postdata['Credit_Card_Number']];
		$item = Mage::helper('core')->encrypt("$cnumber");
		$last4 = substr($cnumber, -4);
		$month = $data[$postdata['Expiration_Date_Month']];
		$year1 = $data[$postdata['Expiration_Date_Year']];
		$vnumber = $data[$postdata['Card_Verification_Number']];
		if ($data[$postdata['Order_Number']]) {
            $order = Mage::getModel('sales/order')
						->loadByIncrementId($data[$postdata['Order_Number']]);
			if ($order->getData()) {
				$this->create = false;
			} else {
				$this->storeId = $this->getStoreId($data[$postdata['Store']]);
				$this->websiteId = $this->getWebsiteId($data[$postdata['Website']]);
				$reservedOrderId = $data[$postdata['Order_Number']];
			}
        } else {
			$this->storeId = $this->getStoreId($data[$postdata['Store']]);
			$this->websiteId = $this->getWebsiteId($data[$postdata['Website']]);
            $reservedOrderId = Mage::getSingleton('eav/config')->getEntityType('order')->fetchNewIncrementId($this->storeId);
        }
		
		//Create Order
		if ($this->create) {
		if($this->storeId != '')
		{
			$order = Mage::getModel('sales/order')
						->setIncrementId($reservedOrderId)
						->setStoreId($this->storeId)
						->setQuoteId(0);
			
			//Add Payment
			if($data[$postdata['Order_Payment_Method']]) {
				if($data[$postdata['Order_Payment_Method']] == 'ccsave')
				{
					$orderPayment = Mage::getModel('sales/order_payment')
									->setStoreId($this->storeId)
									->setCustomerPaymentId(0)
									->setMethod($data[$postdata['Order_Payment_Method']])
									->setCcOwner("$name")
									->setCcType("$type")
									->setCcNumberEnc("$item")
									->setCcLast4("$last4")
									->setCcExpMonth("$month")
									->setCcExpYear("$year1")
									->setCcCid("$vnumber");
					$order->setPayment($orderPayment);
				}else
				{
					$orderPayment = Mage::getModel('sales/order_payment')
									->setStoreId($this->storeId)
									->setCustomerPaymentId(0)
									->setMethod($data[$postdata['Order_Payment_Method']]);
					$order->setPayment($orderPayment);
				}
			}
			
			//Add Shipping
			if($data[$postdata['Order_Shipping_Method']]) {
				$order->setShippingMethod($data[$postdata['Order_Shipping_Method']]);
			}
			if ($data[$postdata['Order_Shipping_Description']]) {
				$order->setShippingDescription($data[$postdata['Order_Shipping_Description']]);
			}
			
			//Add Currency
			if($data[$postdata['Order_Currency']]) {
				$order->setGlobal_currency_code($data[$postdata['Order_Currency']])
					  ->setBase_currency_code($data[$postdata['Order_Currency']])
					  ->setStore_currency_code($data[$postdata['Order_Currency']])
					  ->setOrder_currency_code($data[$postdata['Order_Currency']]);
			}
			
			if ($data[$postdata['Order_Subtotal']]) {
				$subtotalIncTax = (int)$data[$postdata['Order_Subtotal']] + (int)$data[$postdata['Order_Tax']];
				$order->setBaseSubtotal($data[$postdata['Order_Subtotal']])
					  ->setSubtotal($data[$postdata['Order_Subtotal']])
					  ->setSubtotalIncTax($subtotalIncTax);
			}
			if ($data[$postdata['Order_Tax']]) {
				$tax = $data[$postdata['Order_Tax']];
				$order->setBaseTaxAmount($tax);
				$order->setTaxAmount($tax);
			}
			if ($data[$postdata['Order_Shipping']]) {
				$shippingFee = $data[$postdata['Order_Shipping']];
				$order->setBaseShippingAmount($shippingFee);
				$order->setShippingAmount($shippingFee);				
			}
			if ($data[$postdata['Order_Discount']]) {
				$discount = $data[$postdata['Order_Discount']];
				$order->setBaseDiscountAmount($discount);
				$order->setDiscountAmount($discount);
			}
			if ($data[$postdata['Order_Grand_Total']]) {
				$order->setBaseGrandTotal($data[$postdata['Order_Grand_Total']]);
				$order->setGrandTotal($data[$postdata['Order_Grand_Total']]);				
			}
			
			$order->setBaseToGlobalRate('1');			
			$order->setBaseToOrderRate('1');
			$order->setStoreToBaseRate('1');
			$order->setStoreToOrderRate('1');
			
			// Add Customer
			if ($data[$postdata['Customer_Email']]) {
				$customerEmail = $data[$postdata['Customer_Email']];
				$this->customerExist = false;				
				$this->customer = $this->getCustomerByEmail($customerEmail, $this->websiteId);
				if ($this->customer->getData()) {
					$this->setCustomerData($order, $postdata);
				} else {
					$this->setCustomerDataAsGuest($order, $postdata);
				}
			}

			// Add Billing Address
			$billingAddress = $this->getBillingAddress($postdata);
			$order->setBillingAddress($billingAddress);

			// Add Shipping Address
			$shippingAddress = $this->getShippingAddress($postdata);
			$order->setShippingAddress($shippingAddress);
	
			//Add Product
			$this->addProductToOrder($order, $postdata);
			
			$order->place();			
			$order->save();
			
			//Set Date
			if ($data[$postdata['Order_Date']]) {
				$this->setDate($order, $data[$postdata['Order_Date']]);
				$order->save();
			}	
			
			//Send Order Mail
			if ($data[$postdata['Order_Email']]) {
				$order->sendNewOrderEmail(); 
				$order->setEmailSent(true); 
				$order->save();
			}
			}
			else
			{
				if($this->storeId == '')
				{
					Mage::getSingleton('adminhtml/session')->addError('Order Number Not Imported '.$data[$postdata['Order_Number']]);
				} 
			}	
		}
		else
		{
			Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Updated Order Number '.$data[$postdata['Order_Number']]));
		}

        //Create Invoice
        if ($data[$postdata['Invoice']]) {
            if ($data[$postdata['Invoice']] == 'yes') {
				$this->createInvoice($order, $postdata);				                
            }
        }
        
        //Create shipment
        if ($data[$postdata['Shipment']]) {
            if ($data[$postdata['Shipment']] == 'yes') {
				$this->createShipment($order, $postdata);
            }
        }
    }

	public function getStoreId($store) {
		$storeData = $this->getStoreByCode($store);
		if($storeData != '')
		{
			return $storeData->getData('store_id');
		}
	}
	
	public function getWebsiteId($website) {
		$websiteData = $this->getWebsiteByCode($website);
		return $websiteData->getData('website_id');
	}
	
	public function setDate($type, $date) {
		//CSV Order Date mm/dd/yyyy h:m:s
		if (strstr($date, "T")) {
			$date = str_replace("T", " ", substr($date, 0, 19));
		} else {			
			//yyyy-mm-dd h:m:s
			$orderDate = strtotime($date);			
			$date = date("Y-m-d H:i:s", $orderDate);
		}
		$type->setCreatedAt($date);
	}
	
    private function getCustomerByEmail($email, $websiteId)
    {
    	$customer = Mage::getModel('customer/customer')
                        ->setWebsiteId($websiteId)
                        ->loadByEmail($email);
    	return $customer;
    }

    private function setCustomerData($order, $postdata)
    {
        $order->setCustomerId($this->customer->getEntityId())
			  ->setCustomerEmail($this->customer->getEmail())
			  ->setCustomerFirstname($this->data[$postdata['Customer_First_Name']])
			  ->setCustomerLastname($this->data[$postdata['Customer_Last_Name']])
			  ->setCustomerGroupId($this->customer->getGroupId())
			  ->setCustomerIsGuest(0);
    }
	
	private function setCustomerDataAsGuest($order, $postdata)
    {
        $order->setCustomerEmail($this->data[$postdata['Customer_Email']])
              ->setCustomerFirstname($this->data[$postdata['Customer_First_Name']])
			  ->setCustomerLastname($this->data[$postdata['Customer_Last_Name']])
              ->setCustomerGroupId(1)
              ->setCustomerIsGuest(1);              
    }
	
	private function addProductToOrder($order, $postdata)
	{
		if ($this->data[$postdata['Item']]) {
			$totalqty = 0;
			$productValues = explode('|', $this->data[$postdata['Item']]);
			
			if (count($productValues) > 0) {
				foreach($productValues as $productValue) {
					$productFields = explode(':', $productValue);
					$sku = $productFields[0];
					$qty = $productFields[1];
					$price = $productFields[2];
					$name = $productFields[3];
					$productId = Mage::getModel('catalog/product')
									->getIdBySku($sku);
					$rowTotal = $price * $qty;
					$totalqty +=  (int)$qty;
					
					if (!$productId) {
						$orderItem = Mage::getModel('sales/order_item')
										->setStoreId($this->storeId)
										->setQuoteItemId(0)
										->setQuoteParentItemId(NULL)
										->setProductId('1')
										->setProductType('simple')
										->setQtyBackordered(NULL)
										->setTotalQtyOrdered($qty)
										->setQtyOrdered($qty)
										->setName($name)
										->setSku($sku)
										->setPrice($price)
										->setBasePrice($price)
										->setOriginalPrice($price)
										->setRowTotal($rowTotal)
										->setBaseRowTotal($rowTotal);
					} else {
						$product = Mage::getModel('catalog/product')->load($productId);							
						$orderItem = Mage::getModel('sales/order_item')
										->setStoreId($this->storeId)
										->setQuoteItemId(0)
										->setQuoteParentItemId(NULL)
										->setProductId($productId)
										->setProductType($product->getTypeId())
										->setQtyBackordered(NULL)
										->setTotalQtyOrdered($qty)
										->setQtyOrdered($qty)
										->setName($product->getName())
										->setSku($sku)
										->setPrice($price)
										->setBasePrice($price)
										->setOriginalPrice($product->getPrice())
										->setRowTotal($rowTotal)
										->setBaseRowTotal($rowTotal);
					
					}
					$order->addItem($orderItem);
				}				
			}
		$order->setTotalQtyOrdered($totalqty);			
		}
	}

    private function getBillingAddress($postdata)
    {       
        $billing = $this->customer->getDefaultBillingAddress();
		if (!$billing) {
			$billingPrefix = '';
			$billingFirstname = '';
			$billingMiddlename = '';
			$billingLastname = '';
			$billingSuffix = '';
			$billingCompany = '';
			$billingStreet = '';
			$billingCity = '';
			$billingCountry = '';
			$billingRegion = '';
			$billingRegionId = '';
			$billingPostcode = '';
			$billingTelephone = '';
			$billingFax = '';
		} else {
			$billingPrefix = $billing->getPrefix();
			$billingFirstname = $billing->getFirstname();
			$billingMiddlename = $billing->getMiddlename();
			$billingLastname = $billing->getLastname();
			$billingSuffix = $billing->getSuffix();
			$billingCompany = $billing->getCompany();
			$billingStreet = $billing->getStreet();
			$billingCity = $billing->getCity();
			$billingCountry = $billing->getCountryId();
			$billingRegion = $billing->getRegion();
			$billingRegionId = $billing->getRegionId();
			$billingPostcode = $billing->getPostcode();
			$billingTelephone = $billing->getTelephone();
			$billingFax = $billing->getFax();
		}
        if ($this->data[$postdata['Billing_State_Name']] && $this->data[$postdata['Billing_Country']]) {
            $regionId = $this->getRegionId($this->data[$postdata['Billing_State_Name']], $this->data[$postdata['Billing_Country']]);
        }        

        $billingAddress = Mage::getModel('sales/order_address')
							->setStoreId($this->storeId)
							->setAddressType(Mage_Sales_Model_Quote_Address::TYPE_BILLING)
							->setCustomerId($this->customer->getId())
							->setCustomerAddressId($this->customer->getDefaultBilling())      
							->setPrefix(($this->data[$postdata['Prefix']] != null)?$this->data[$postdata['Prefix']]:$billingPrefix)
							->setFirstname(($this->data[$postdata['Billing_First_Name']] != null)?$this->data[$postdata['Billing_First_Name']]:$billingFirstname)
							->setMiddlename(($this->data[$postdata['Billing_Middle_Name']] != null)?$this->data[$postdata['Billing_Middle_Name']]:$billingMiddlename)
							->setLastname(($this->data[$postdata['Billing_Last_Name']] != null)?$this->data[$postdata['Billing_Last_Name']]:$billingLastname)
							->setSuffix(($this->data[$postdata['Billing_Suffix']] != null)?$this->data[$postdata['Billing_Suffix']]:$billingSuffix)
							->setCompany(($this->data[$postdata['Billing_Company']] != null)?$this->data[$postdata['Billing_Company']]:$billingCompany)
							->setStreet(($this->data[$postdata['Billing_Street']] != null)?$this->data[$postdata['Billing_Street']]:$billingStreet)
							->setCity(($this->data[$postdata['Billing_City']] != null)?$this->data[$postdata['Billing_City']]:$billingCity)
							->setCountryId(($this->data[$postdata['Billing_Country']] != null)?$this->data[$postdata['Billing_Country']]:$billingCountry)
							->setRegion(($this->data[$postdata['Billing_State_Name']] != null)?$this->data[$postdata['Billing_State_Name']]:$billingRegion)
							->setRegion_id(($this->data[$postdata['Billing_State_Name']] != null)?$regionId:$billingRegionId)
							->setPostcode(($this->data[$postdata['Billing_Zip']] != null)?$this->data[$postdata['Billing_Zip']]:$billingPostcode)
							->setTelephone(($this->data[$postdata['Billing_Phone_Number']] != null)?$this->data[$postdata['Billing_Phone_Number']]:$billingTelephone)
							->setFax(($this->data[$postdata['Billing_Fax']] != null)?$this->data[$postdata['Billing_Fax']]:$billingFax);
		
        return $billingAddress;
    }

    private function getShippingAddress($postdata)
    {
        $shipping = $this->customer->getDefaultShippingAddress();
		if (!$shipping) {
			$shippingPrefix = '';
			$shippingFirstname = '';
			$shippingMiddlename = '';
			$shippingLastname = '';
			$shippingSuffix = '';
			$shippingCompany = '';
			$shippingStreet = '';
			$shippingCity = '';
			$shippingCountry = '';
			$shippingRegion = '';
			$shippingRegionId = '';
			$shippingPostcode = '';
			$shippingTelephone = '';
			$shippingFax = '';
		} else {
			$shippingPrefix = $shipping->getPrefix();
			$shippingFirstname = $shipping->getFirstname();
			$shippingMiddlename = $shipping->getMiddlename();			
			$shippingLastname = $shipping->getLastname();
			$shippingSuffix = $shipping->getSuffix();
			$shippingCompany = $shipping->getCompany();
			$shippingStreet = $shipping->getStreet();
			$shippingCity = $shipping->getCity();
			$shippingCountry = $shipping->getCountryId();
			$shippingRegion = $shipping->getRegion();
			$shippingRegionId = $shipping->getRegionId();
			$shippingPostcode = $shipping->getPostcode();
			$shippingTelephone = $shipping->getTelephone();
			$shippingFax = $shipping->getFax();
		}
        if ($this->data['Shipping State Name'] && $this->data['Shipping Country']) {
            $regionId = $this->getRegionId($this->data['Shipping State Name'], $this->data['Shipping Country']);
        }

        $shippingAddress = Mage::getModel('sales/order_address')
							->setStoreId($this->storeId)
							->setAddressType(Mage_Sales_Model_Quote_Address::TYPE_SHIPPING)
							->setCustomerId($this->customer->getId())
							->setCustomerAddressId($this->customer->getDefaultShipping())
							->setPrefix(($this->data[$postdata['Prefix']] != null)?$this->data[$postdata['Prefix']]:$shippingPrefix)
							->setFirstname(($this->data[$postdata['Shipping_First_Name']] != null)?$this->data[$postdata['Shipping_First_Name']]:$shippingFirstname)
							->setMiddlename(($this->data[$postdata['Shipping_Middle_Name']] != null)?$this->data[$postdata['Shipping_Middle_Name']]:$shippingMiddlename)
							->setLastname(($this->data[$postdata['Shipping_Last_Name']] != null)?$this->data[$postdata['Shipping_Last_Name']]:$shippingLastname)
							->setSuffix(($this->data[$postdata['Shipping_Suffix']] != null)?$this->data[$postdata['Shipping_Suffix']]:$shippingSuffix)
							->setCompany(($this->data[$postdata['Shipping_Company']] != null)?$this->data[$postdata['Shipping_Company']]:$shippingCompany)
							->setStreet(($this->data[$postdata['Shipping_Street']] != null)?$this->data[$postdata['Shipping_Street']]:$shippingStreet)
							->setCity(($this->data[$postdata['Shipping_City']] != null)?$this->data[$postdata['Shipping_City']]:$shippingCity)
							->setCountryId(($this->data[$postdata['Shipping_Country']] != null)?$this->data[$postdata['Shipping_Country']]:$shippingCountry)
							->setRegion(($this->data[$postdata['Shipping_State_Name']] != null)?$this->data[$postdata['Shipping_State_Name']]:$shippingRegion)
							->setRegion_id(($this->data[$postdata['Shipping_State_Name']] != null)?$regionId:$shippingRegionId)
							->setPostcode(($this->data[$postdata['Shipping_Zip']] != null)?$this->data[$postdata['Shipping_Zip']]:$shippingPostcode)
							->setTelephone(($this->data[$postdata['Shipping_Phone_Number']] != null)?$this->data[$postdata['Shipping_Phone_Number']]:$shippingTelephone)
							->setFax(($this->data[$postdata['Shipping_Fax']] != null)?$this->data[$postdata['Shipping_Fax']]:$shippingFax);

        return $shippingAddress;
    }

    public function getRegionId($regionName, $country)
    {
        $regionCollection = Mage::getModel('directory/region_api')->items($country);       
        foreach ($regionCollection as $region) {
            if ($region['name'] == $regionName) {                
                return $region['region_id'];
            }
        }
    }
	
	public function createInvoice($order, $postdata) 
	{
		if ($order->canInvoice()) {					
			if ($this->data[$postdata['Invoice_Item_SKU']] && $this->data[$postdata['Invoice_Item_Qty']]) {
				$convertor = Mage::getModel('sales/convert_order');
				$invoice = $convertor->toInvoice($order);
				
				foreach ($order->getAllItems() as $orderItem) {
					if ($this->data[$postdata['Invoice_Item_SKU']] == $orderItem->getSku()) {
						$item = $convertor->itemToInvoiceItem($orderItem);								
						$item->setQty($this->data[$postdata['Invoice_Item_Qty']]);								
						$invoice->addItem($item);								
					}			
				}
				$invoice->setTotalQty($totalQty);
				$invoice->collectTotals();
				$order->getInvoiceCollection()->addItem($invoice);

				$invoice->register();				
				$invoice->getOrder()->setIsInProcess(true);

				$transactionSave = Mage::getModel('core/resource_transaction')
										->addObject($invoice)
										->addObject($invoice->getOrder())
										->save();
				$invoiceId = $invoice->getIncrementId();						
			} else {
				$invoiceId = Mage::getModel('sales/order_invoice_api')
					->create($order->getIncrementId(), array());

				$invoice = Mage::getModel('sales/order_invoice')
								->loadByIncrementId($invoiceId);
			}
			
			if ($this->data[$postdata['Invoice_Number']]) {
				$invoice->setIncrementId($this->data[$postdata['Invoice_Number']]);
				$invoiceId = $this->data[$postdata['Invoice_Number']];
			}

			if ($this->data[$postdata['Invoice_Date']]) {
				$this->setDate($invoice, $this->data[$postdata['Invoice_Date']]);
				$invoice->save();
			}					
			//set invoice status "paid"
			/*if ($invoice->canCapture()) {
				$invoice->capture()->save();
			}*/
			if ($data[$postdata['Invoice_Email']]) {
				$invoice->sendEmail();
				$invoice->setEmailSent(true);
				$invoice->save();
			}			
		}
	}
	
	public function createShipment($order, $postdata) 
	{
		if ($order->canInvoice()) {				
			if($order->canShip()) {
				if ($this->data[$postdata['Shipment_Item_SKU']] && $this->data[$postdata['Shipment_Item_Qty']]) {
					$convertor = Mage::getModel('sales/convert_order');
					$shipment = $convertor->toShipment($order);
					
					foreach ($order->getAllItems() as $orderItem) {
						if ($this->data[$postdata['Shipment_Item_SKU']] == $orderItem->getSku()) {
							$item = $convertor->itemToShipmentItem($orderItem);				
							$item->setQty($this->data[$postdata['Shipment_Item_Qty']]);
							$shipment->addItem($item);
						}			
					}							

					$shipment->register();							
					$shipment->getOrder()->setIsInProcess(true);

					$transactionSave = Mage::getModel('core/resource_transaction')
											->addObject($shipment)
											->addObject($shipment->getOrder())
											->save();
					$shipmentId = $shipment->getIncrementId();						
				} else {
					$shipmentId = Mage::getModel('sales/order_shipment_api')
									->create($order->getIncrementId(), array());
					$shipment = Mage::getModel('sales/order_shipment')
									->loadByIncrementId($shipmentId);						
				}
				
				if ($this->data[$postdata['Shipment_Number']]) {
					$shipment->setIncrementId($this->data[$postdata['Shipment_Number']]);
					$shipmentId = $this->data[$postdata['Shipment_Number']];
				}

				if ($this->data[$postdata['Shipment_Date']]) {
					$this->setDate($shipment, $this->data[$postdata['Shipment_Date']]);
					$shipment->save();
				}
				
				if ($this->data[$postdata['Shipment_Email']]) {
					$shipment->sendEmail(); 
					$shipment->setEmailSent(true);
					$shipment->save();					
				}
				
				if ($this->data[$postdata['Track_Number']]) {
					$ship = Mage::getModel('sales/order_shipment_api')
								->addTrack($shipmentId, $this->data[$postdata['Track_Code']], $this->data[$postdata['Track_Title']], $this->data[$postdata['Track_Number']]);										
				}
			}
		}
	}
}