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
class Sttl_ImportOrders_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function getCsvData() {
		$filename = Mage::getModel('core/session')->getFileName();	
		$path = Mage::getBaseDir('media') . DS . 'orderimport';
		$csvObject  = new Varien_File_Csv();		
	
		$csvData = $csvObject->getData($path.'/'.$filename);			
		
		$data = array();
		try {
			while (list($key, $value) = each($csvData)) {                
				if ($key == 0) {
					$fields = $value;                    
				} else {
					try {
						if (is_array($value) && count($value)>0) {							
							foreach($value as $key => $va) {		        				
								$data[$fields[$key]] = $va;
							}
						}
					} catch (Exception $e) {
						$this->hasErrors = true;
						Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
						$this->_redirect('*/*');
					}
				}
			}            
	    } catch (Exception $e) {
            $this->hasErrors = true;
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            $this->_redirect('*/*');
        }
		return $data;		
	}
	public function getCsvFields() {
		$filename = Mage::getModel('core/session')->getFileName();	
		$path = Mage::getBaseDir('media') . DS . 'orderimport';
		$csvObject  = new Varien_File_Csv();			
	
		$csvData = $csvObject->getData($path.'/'.$filename);			
		
		$data = array();
		try {			
			while (list($key, $value) = each($csvData)) {                
				if ($key == 0) {
					$fields = $value;                    
				}	        	
			}            
	    } catch (Exception $e) {
            $this->hasErrors = true;
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            $this->_redirect('*/*');
        }		
		return $fields;
	}
	public function getDefaultFields() {
		$data = Array ('Store','Website','Order Number','Order Date','Order Payment Method','Order Shipping Description','Order Shipping Method','Order Email','Order Comment','Order Currency','Order Subtotal','Order Tax','Order Shipping','Order Discount','Order Grand Total','Order Base Grand Total','Order Paid','Order Refunded','Order Due','Customer First Name','Customer Last Name','Customer Email','Shipping First Name','Shipping Last Name','Shipping Company','Shipping Street','Shipping Zip','Shipping City','Shipping State','Shipping State Name','Shipping Country','Shipping Country Name','Shipping Phone Number','Shipping Fax','Billing First Name','Billing Last Name','Billing Company','Billing Street','Billing Zip','Billing City','Billing State','Billing State Name','Billing Country','Billing Country Name','Billing Phone Number','Billing Fax','Item','Item SKU','Item Qty Ordered','Invoice Item SKU','Invoice Item Qty','Invoice','Invoice Date','Invoice Number','Invoice Email','Invoice Comment','Shipment Item SKU','Shipment Item Qty','Shipment','Shipment Date','Shipment Number','Shipment Email','Shipment Comment','Track Title','Track Code','Track Number','Name On Card','Credit Card Type','Credit Card Number','Expiration Date Month','Expiration Date Year','Card Verification Number' ) ;
		return $data;
	}
}