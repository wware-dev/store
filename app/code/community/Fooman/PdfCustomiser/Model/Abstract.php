<?php

/**
 * @author     Kristof Ringleff
 * @package    Fooman_PdfCustomiser
 * @copyright  Copyright (c) 2009 Fooman Limited (http://www.fooman.co.nz)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

abstract class Fooman_PdfCustomiser_Model_Abstract extends Mage_Sales_Model_Order_Pdf_Abstract
{
    /**
     * set to true to try to merge pdfs created by PdfCustomiser with other pdfs based on Zend_Pdf
     */
    const COMPAT_MODE = false;

    public $pages = array();

    /**
     * keep compatible with the Zend_Pdf way of doing things
     * collect input for later processing with render()
     *
     * @param array $input    array of items to print
     * @param array $orderIds array of order ids
     *
     * @return Fooman_PdfCustomiser_Model_Abstract | Zend_Pdf
     */
    public function getPdf($input = array(), $orderIds = null)
    {
        if (self::COMPAT_MODE) {
            try {
                $newPdf = new Zend_Pdf();
                $extractor = new Zend_Pdf_Resource_Extractor();

                if (!empty($orderIds)) {
                    $origPdf = $this->renderPdf(null, $orderIds, null, true);
                } else {
                    $origPdf = $this->renderPdf($input, $orderIds, null, true);
                }
                if ($origPdf->getPdfAnyOutput()) {
                    $pdfString = $origPdf->Output('output.pdf', 'S');

                    $tcpdf = Zend_Pdf::parse($pdfString);

                    foreach ($tcpdf->pages as $p) {
                        $newPdf->pages[] = $extractor->clonePage($p);
                    }
                }
                return $newPdf;

            } catch (Exception $e) {
                Mage::logException($e);
            }
        } else {
            $this->pages[] = array(
                'instance'    => $this,
                'objectArray' => $input,
                'orderIds'    => $orderIds
            );
            return $this;
        }
    }

    /**
     *
     *
     * @param bool $newSegmentOnly
     * @param null $outputStream
     * @param bool $toFileName
     * @param bool $toBrowser
     *
     * @return bool | string | void
     */
    public function render($newSegmentOnly = false, $outputStream = null, $toFileName = false, $toBrowser = false)
    {
        if (self::COMPAT_MODE) {
            return parent::render($newSegmentOnly, $outputStream);
        } else {
            $pdf = null;
            foreach ($this->pages as $printObjects) {
                if (!empty($printObjects['orderIds'])) {
                    $pdf = $printObjects['instance']->renderPdf(array(), $printObjects['orderIds'], $pdf, true);
                } else {
                    $pdf = $printObjects['instance']->renderPdf($printObjects['objectArray'], null, $pdf, true);
                }
            }

            if ($pdf->getPdfAnyOutput()) {
                if ($toFileName) {
                    if ($toBrowser) {
                        return $pdf->Output($toFileName, 'D');
                        exit;
                    } else {
                        return $pdf->Output($toFileName, 'F');
                    }
                } else {
                    return $pdf->Output('output.pdf', 'S');
                }
            } else {
                return false;
            }
        }
    }

    /**
     * retrieve order associated with sales object
     *
     * @param $salesObject
     *
     * @return Mage_Sales_Model_Order
     */
    public function getOrder ($salesObject)
    {
        if ($salesObject instanceof Mage_Sales_Model_Order) {
            return $salesObject;
        } else {
            return $salesObject->getOrder();
        }
    }

    /**
     * adjust sort order of global pdf config nodes
     * based on settings in database
     *
     * @param $helper
     *
     */
    protected function adjustPdfTotalsConfig(Fooman_PdfCustomiser_Helper_Pdf $helper)
    {
        if (!$helper->hasAdjustedTotalsSort) {
            $existingConfig = Mage::getConfig()->getNode('global/pdf/totals');
            foreach ($existingConfig as $totals) {
                foreach ($totals as $total=> $settings) {
                    $newSortOrder = Mage::getStoreConfig('sales_pdf/totals/' . (string)$total, $helper->getStoreId());
                    if ($newSortOrder) {
                        Mage::getConfig()->setNode('global/pdf/totals/' . $total . '/sort_order', $newSortOrder, true);
                    }
                }
            }
        }
        $helper->hasAdjustedTotalsSort = true;
    }

    /**
     * prepare totals for display
     *
     * @param Fooman_PdfCustomiser_Helper_Pdf $helper
     * @param                                 $salesObject
     *
     * @return array
     */
    public function PrepareTotals(Fooman_PdfCustomiser_Helper_Pdf $helper, $salesObject)
    {
        $totals = array();
        if (!$helper->displayTotals()) {
            return $totals;
        }

        $order = $this->getOrder($salesObject);
        $this->adjustPdfTotalsConfig($helper);
        $pdfTotals = $this->_getTotalsList($salesObject);

        foreach ($pdfTotals as $pdfTotal) {
            $pdfTotal->setOrder($order)->setSource($salesObject);
            $sortOrder = $pdfTotal->getSortOrder();
            switch ($pdfTotal->getSourceField()){
                case 'subtotal':
                    //Prepare Subtotal
                    if ($pdfTotal->canDisplay() && strtoupper($pdfTotal->getSortOrder())!='NO') {
                        if (Mage::getStoreConfig('tax/sales_display/subtotal', $helper->getStoreId())
                            == Mage_Tax_Model_Config::DISPLAY_TYPE_INCLUDING_TAX
                        ) {
                            if ($this->_hiddenTaxAmount == 0 && $salesObject->getBaseSubtotalInclTax()) {
                                $totals[$sortOrder][] = array(
                                    'label'     => Mage::helper('sales')->__('Order Subtotal') . ':',
                                    'amount'    => $salesObject->getSubtotalInclTax(),
                                    'baseAmount'=> $salesObject->getBaseSubtotalInclTax()
                                );
                            } else {
                                $totals[$sortOrder][] = array(
                                    'label'     => Mage::helper('sales')->__('Order Subtotal') . ':',
                                    'amount'    => $salesObject->getSubtotal()
                                        + $salesObject->getTaxAmount()
                                        + $this->_hiddenTaxAmount
                                        - $salesObject->getFoomanSurchargeTaxAmount()
                                        - $salesObject->getShippingTaxAmount()
                                        - $salesObject->getCodTaxAmount(),
                                    'baseAmount'=> $salesObject->getBaseSubtotal()
                                        + $salesObject->getBaseTaxAmount()
                                        + $this->_baseHiddenTaxAmount
                                        - $salesObject->getBaseFoomanSurchargeTaxAmount()
                                        - $salesObject->getBaseShippingTaxAmount()
                                        - $salesObject->getBaseCodTaxAmount()
                                );
                            }
                        } elseif (Mage::getStoreConfig('tax/sales_display/subtotal', $helper->getStoreId())
                            == Mage_Tax_Model_Config::DISPLAY_TYPE_BOTH
                        ) {
                            if ($this->_hiddenTaxAmount == 0 && $salesObject->getBaseSubtotalInclTax()) {
                                $totals[$sortOrder][] = array(
                                    'label'     => Mage::helper('sales')->__('Order Subtotal') . ' '
                                        . Mage::helper('tax')->__('Incl. Tax') . ':',
                                    'amount'    => $salesObject->getSubtotalInclTax(),
                                    'baseAmount'=> $salesObject->getBaseSubtotalInclTax()
                                );
                            } else {
                                $totals[$sortOrder][] = array(
                                    'label'     => Mage::helper('sales')->__('Order Subtotal') . ' '
                                        . Mage::helper('tax')->__('Incl. Tax') . ':',
                                    'amount'    => $salesObject->getSubtotal()
                                        + $salesObject->getTaxAmount()
                                        + $this->_hiddenTaxAmount
                                        - $salesObject->getFoomanSurchargeTaxAmount()
                                        - $salesObject->getShippingTaxAmount()
                                        - $salesObject->getCodTaxAmount(),
                                    'baseAmount'=> $salesObject->getBaseSubtotal()
                                        + $salesObject->getBaseTaxAmount()
                                        + $this->_baseHiddenTaxAmount
                                        - $salesObject->getBaseFoomanSurchargeTaxAmount()
                                        - $salesObject->getBaseShippingTaxAmount()
                                        - $salesObject->getBaseCodTaxAmount()
                                );
                            }
                            $totals[$sortOrder][] = array(
                                'label'     => Mage::helper('sales')->__('Order Subtotal') . ' '
                                    . Mage::helper('tax')->__('Excl. Tax') . ':',
                                'amount'    => $salesObject->getSubtotal(),
                                'baseAmount'=> $salesObject->getBaseSubtotal()
                            );
                        } else {
                            $totals[$sortOrder][] = array(
                                'label'     => Mage::helper('sales')->__('Order Subtotal') . ':',
                                'amount'    => $salesObject->getSubtotal(),
                                'baseAmount'=> $salesObject->getBaseSubtotal()
                            );
                        }
                    }
                    break;
                case 'discount_amount':
                    //Prepare Discount
                    //Prepare positive or negative Discount to display with minus sign
                    if ($pdfTotal->canDisplay() && strtoupper($pdfTotal->getSortOrder())!='NO') {
                        $sign = ((float)$salesObject->getDiscountAmount()>0)?-1:1;

                        if (Mage::getStoreConfig('tax/sales_display/shipping', $helper->getStoreId())
                            == Mage_Tax_Model_Config::DISPLAY_TYPE_INCLUDING_TAX
                        ) {
                            $totals[$sortOrder][] = array(
                                    'label'=> trim(Mage::helper('sales')->__('Discount').' '. $order->getCouponCode()). ':',
                                    'amount'=>$sign*$salesObject->getDiscountAmount(),
                                    'baseAmount'=>$sign*$salesObject->getBaseDiscountAmount()
                            );
                        } elseif (Mage::getStoreConfig('tax/sales_display/shipping', $helper->getStoreId())
                            == Mage_Tax_Model_Config::DISPLAY_TYPE_BOTH
                        ) {
                            $totals[$sortOrder][] = array(
                                'label'     => Mage::helper('sales')->__('Discount') . ' '
                                    . Mage::helper('tax')->__('Incl. Tax') . ':',
                                'amount'    => $sign * $salesObject->getDiscountAmount(),
                                'baseAmount'=> $sign * $salesObject->getBaseDiscountAmount()
                            );
                            $totals[$sortOrder][] = array(
                                'label'     => Mage::helper('sales')->__('Discount') . ' '
                                    . Mage::helper('tax')->__('Excl. Tax') . ':',
                                'amount'    => $sign * $salesObject->getDiscountAmount()
                                    + $this->_hiddenTaxAmount,
                                'baseAmount'=> $sign * $salesObject->getBaseDiscountAmount()
                                    + $this->_baseHiddenTaxAmount
                            );
                        } else {
                            $totals[$sortOrder][] = array(
                                'label'     => trim(Mage::helper('sales')->__('Discount') . ' '
                                    . $order->getCouponCode()) . ':',
                                'amount'    => $sign * $salesObject->getDiscountAmount()
                                    + $this->_hiddenTaxAmount,
                                'baseAmount'=> $sign * $salesObject->getBaseDiscountAmount()
                                    + $this->_baseHiddenTaxAmount
                            );
                        }
                    }
                    break;
                case 'tax_amount':
                    //Prepare Tax
                    if (((float)$salesObject->getTaxAmount() > 0
                        && !Mage::getStoreConfigFlag('tax/sales_display/grandtotal')
                        || Mage::getStoreConfigFlag('sales_pdf/all/allonly1grandtotal', $helper->getStoreId()))
                        && strtoupper($pdfTotal->getSortOrder())!='NO'
                    ) {
                        //Magento looses information of tax rates if an order
                        //is split into multiple invoices
                        //so only display summary if both tax amounts equal
                        if (Mage::getStoreConfig('tax/sales_display/full_summary', $helper->getStoreId())
                            && $order->getTaxAmount() == $salesObject->getTaxAmount()
                        ) {
                            $filteredTaxrates = array();
                            //need to filter out doubled up taxrates on edited/reordered items -> Magento bug
                            foreach ($order->getFullTaxInfo() as $taxrate) {
                                foreach ($taxrate['rates'] as $rate) {
                                    $taxId = str_replace(array('%', ' '), '', $rate['code']);
                                    if (!isset($rate['title'])) {
                                        $rate['title'] = $taxId;
                                    }
                                    $filteredTaxrates[$taxId] = array('id'        => $rate['code'],
                                                                      'percent'   => $rate['percent'],
                                                                      'amount'    => $taxrate['amount'],
                                                                      'baseAmount'=> $taxrate['base_amount'],
                                                                      'title'     => $rate['title']
                                    );
                                }
                            }
                            foreach ($filteredTaxrates as $taxId => $filteredTaxrate) {
                                $totals[$sortOrder][] = array(
                                    'label'     => (strpos($filteredTaxrate['title'], "%") === false ?
                                        $filteredTaxrate['title'] . ' [' . sprintf(
                                            "%01.2f%%", $filteredTaxrate['percent']
                                        ) . ']' : $filteredTaxrate['title']) . ':',
                                    'amount'    => (float)$filteredTaxrate['amount'],
                                    'baseAmount'=> (float)$filteredTaxrate['baseAmount']
                                );
                            }
                        } else {
                            $totals[$sortOrder][] = array(
                                'label'     => $helper->getTranslatedString('Tax', 'sales') . ":",
                                'amount'    => (float)$salesObject->getTaxAmount(),
                                'baseAmount'=> (float)$salesObject->getBaseTaxAmount()
                            );
                        }
                    } elseif (Mage::getStoreConfig('tax/sales_display/zero_tax', $helper->getStoreId())) {
                            $totals[$sortOrder][] = array(
                                'label'     => $helper->getTranslatedString('Tax', 'sales') . ":",
                                'amount'    => (float)0,
                                'baseAmount'=> (float)0
                            );
                    }                    
                    break;
                case 'shipping_amount':
                    //Prepare Shipping
                    if ($pdfTotal->canDisplay() && strtoupper($pdfTotal->getSortOrder())!='NO') {
                        if (Mage::getStoreConfig('tax/sales_display/shipping', $helper->getStoreId())
                            == Mage_Tax_Model_Config::DISPLAY_TYPE_INCLUDING_TAX
                        ) {
                            $totals[$sortOrder][] = array(
                                'label'     =>
                                str_replace(' &amp; ', ' & ', Mage::helper('sales')->__('Shipping & Handling')) . ':',
                                'amount'    => $salesObject->getShippingAmount()
                                    + $order->getShippingTaxAmount(),
                                'baseAmount'=> $salesObject->getBaseShippingAmount()
                                    + $order->getBaseShippingTaxAmount()
                            );
                        } elseif (Mage::getStoreConfig('tax/sales_display/shipping', $helper->getStoreId())
                            == Mage_Tax_Model_Config::DISPLAY_TYPE_BOTH
                        ) {
                            $totals[$sortOrder][] = array(
                                'label'     =>
                                str_replace(' &amp; ', ' & ', Mage::helper('sales')->__('Shipping & Handling'))
                                    . ' ' . Mage::helper('tax')->__('Incl. Tax') . ':',
                                'amount'    => $salesObject->getShippingAmount()
                                    + $order->getShippingTaxAmount(),
                                'baseAmount'=> $salesObject->getBaseShippingAmount()
                                    + $order->getBaseShippingTaxAmount()
                            );
                            $totals[$sortOrder][] = array(
                                'label'     =>
                                str_replace(' &amp; ', ' & ', Mage::helper('sales')->__('Shipping & Handling'))
                                    . ' ' . Mage::helper('tax')->__('Excl. Tax') . ':',
                                'amount'    => $salesObject->getShippingAmount(),
                                'baseAmount'=> $salesObject->getBaseShippingAmount()
                            );
                        } else {
                            $totals[$sortOrder][] = array(
                                'label'     =>
                                str_replace(' &amp; ', ' & ', Mage::helper('sales')->__('Shipping & Handling')) . ':',
                                'amount'    => $salesObject->getShippingAmount(),
                                'baseAmount'=> $salesObject->getBaseShippingAmount()
                            );
                        }
                    }                    
                    break;                 
                case 'adjustment_positive':
                    //Prepare AdjustmentPositive
                    if (
                        $salesObject instanceof Mage_Sales_Model_Order_Creditmemo
                        && $pdfTotal->canDisplay()
                        && strtoupper($pdfTotal->getSortOrder())!='NO'
                    ) {
                        $totals[$sortOrder][] = array(
                            'label'      => Mage::helper('sales')->__('Adjustment Refund') . ':',
                            'amount'     => $salesObject->getAdjustmentPositive(),
                            'baseAmount' => $salesObject->getBaseAdjustmentPositive()
                        );
                    }                    
                    break;
                case 'adjustment_negative':
                    //Prepare AdjustmentNegative
                    if (
                        $salesObject instanceof Mage_Sales_Model_Order_Creditmemo
                        && $pdfTotal->canDisplay()
                        && strtoupper($pdfTotal->getSortOrder())!='NO'
                    ) {
                        $totals[$sortOrder][] = array(
                            'label'      => Mage::helper('sales')->__('Adjustment Fee') . ':',
                            'amount'     => $salesObject->getAdjustmentNegative(),
                            'baseAmount' => $salesObject->getBaseAdjustmentNegative()
                        );
                    }                    
                    break;
                case 'fooman_surcharge_amount':
                    //Prepare Fooman Surcharge
                    if ($pdfTotal->canDisplay() && strtoupper($pdfTotal->getSortOrder())!='NO') {
                        if (Mage::getStoreConfig('tax/sales_display/shipping', $helper->getStoreId())
                            == Mage_Tax_Model_Config::DISPLAY_TYPE_INCLUDING_TAX
                        ) {
                            $totals[$sortOrder][] = array(
                                    'label'=>$order->getFoomanSurchargeDescription().':',
                                    'amount'=>$salesObject->getFoomanSurchargeAmount()
                                        + $salesObject->getFoomanSurchargeTaxAmount(),
                                    'baseAmount'=>$salesObject->getBaseFoomanSurchargeAmount()
                                        + $salesObject->getBaseFoomanSurchargeTaxAmount()
                            );
                        } elseif (Mage::getStoreConfig('tax/sales_display/shipping', $helper->getStoreId())
                            == Mage_Tax_Model_Config::DISPLAY_TYPE_BOTH
                        ) {
                            $totals[$sortOrder][] = array(
                                    'label'=>$order->getFoomanSurchargeDescription().':',
                                    'amount'=>$salesObject->getFoomanSurchargeAmount()
                                        + $salesObject->getFoomanSurchargeTaxAmount(),
                                    'baseAmount'=>$salesObject->getBaseFoomanSurchargeAmount()
                                        + $salesObject->getBaseFoomanSurchargeTaxAmount()
                            );
                            $totals[$sortOrder][] = array(
                                    'label'=>$order->getFoomanSurchargeDescription().':',
                                    'amount'=>$salesObject->getFoomanSurchargeAmount(),
                                    'baseAmount'=>$salesObject->getBaseFoomanSurchargeAmount()
                            );
                        } else {
                            $totals[$sortOrder][] = array(
                                    'label'=>$order->getFoomanSurchargeDescription().':',
                                    'amount'=>$salesObject->getFoomanSurchargeAmount(),
                                    'baseAmount'=>$salesObject->getBaseFoomanSurchargeAmount()
                            );
                        }
                    }                    
                    break;
                case 'customer_credit_amount':
                    //Prepare MageWorx Customer Credit
                    if ($pdfTotal->canDisplay() && strtoupper($pdfTotal->getSortOrder())!='NO') {
                        $sign = $pdfTotal->getAmountPrefix();
                        $totals[$sortOrder][] = array(
                            'label'=>Mage::helper('customercredit')->__('Internal Credit').':',
                            'amount'=>$sign*$salesObject->getCustomerCreditAmount(),
                            'baseAmount'=>$sign*$salesObject->getBaseCustomerCreditAmount()
                        );
                    }
                    break;
                case 'customer_balance_amount':
                    //Prepare Enterprise Store Credit
                    if ($pdfTotal->canDisplay() && strtoupper($pdfTotal->getSortOrder())!='NO') {
                        $sign = ((float)$salesObject->getCustomerBalanceAmount()>0)?-1:1;
                        $totals[$sortOrder][] = array(
                            'label'     => str_replace(
                                ' &amp; ', ' & ', Mage::helper('enterprise_giftcardaccount')->__('Store Credit')
                            ) . ':',
                            'amount'    => $sign * $salesObject->getCustomerBalanceAmount(),
                            'baseAmount'=> $sign * $salesObject->getBaseCustomerBalanceAmount()
                        );
                    }
                    break;
                case 'gift_cards_amount':
                    //Prepare Enterprise Gift Cards
                    if ($pdfTotal->canDisplay() && strtoupper($pdfTotal->getSortOrder())!='NO') {
                        $sign = ((float)$salesObject->getGiftCardsAmount()>0)?-1:1;
                        $totals[$sortOrder][] = array(
                            'label'     =>
                            str_replace(' &amp; ', ' & ', Mage::helper('enterprise_giftcardaccount')->__('Gift Cards'))
                                . ':',
                            'amount'    => $sign * $salesObject->getGiftCardsAmount(),
                            'baseAmount'=> $sign * $salesObject->getBaseGiftCardsAmount()
                        );
                    }                    
                    break;  
                case 'reward_currency_amount':
                    //Prepare Enterprise paid from reward points
                    if ($pdfTotal->canDisplay() && strtoupper($pdfTotal->getSortOrder())!='NO') {
                        $sign = ((float)$salesObject->getRewardCurrencyAmount()>0)?-1:1;
                        $totals[$sortOrder][] = array(
                            'label'     => str_replace(
                                ' &amp; ',
                                ' & ',
                                Mage::helper('enterprise_reward')->formatReward($salesObject->getRewardPointsBalance())
                            ) . ':',
                            'amount'    => $sign * $salesObject->getRewardCurrencyAmount(),
                            'baseAmount'=> $sign * $salesObject->getBaseRewardCurrencyAmount()
                        );
                    }                    
                    break;
                case 'giftcert_amount':
                    //Unirgy Giftcert Extension
                    if ($pdfTotal->canDisplay() && strtoupper($pdfTotal->getSortOrder())!='NO') {
                        $sign = ((float)$salesObject->getGiftcertAmount()>0)?-1:1;
                        $totals[$sortOrder][] = array(
                            'label'     => str_replace(
                                ' &amp; ',
                                ' & ',
                                Mage::helper('ugiftcert')->__('Gift Certificates (%s)', $order->getGiftcertCode())
                            ) . ':',
                            'amount'    => $sign * $order->getGiftcertAmount(),
                            'baseAmount'=> $sign * $order->getBaseGiftcertAmount()
                        );
                    }
                    break;
                case 'customer_balance_total_refunded':
                    //dealt with separately
                    break;
                case 'grand_total':
                    //dealt with separately
                    break;
                default:
                    //unknown total
                    if ($pdfTotal->canDisplay() && strtoupper($pdfTotal->getSortOrder())!='NO') {
                        $tmpPdfTotalAmounts = $pdfTotal->getTotalsForDisplay();
                        if (isset($tmpPdfTotalAmounts['amount'])) {
                            $tmpPdfTotalAmount = $tmpPdfTotalAmounts['amount'];
                            $sign = ($tmpPdfTotalAmount > 0) ? -1 : 1;
                            $totals[$sortOrder][] = array(
                                'label'      => $pdfTotal->getTitle(),
                                'amount'     => $sign * $tmpPdfTotalAmount,
                                'baseAmount' => Mage::app()->getStore()->roundPrice(
                                    $sign * $tmpPdfTotalAmount * $order->getBaseToOrderRate()
                                )
                            );
                        } elseif (is_array($tmpPdfTotalAmounts)) {
                            foreach ($tmpPdfTotalAmounts as $tmpPdfTotalAmount) {
                                $totals[$sortOrder][] = array(
                                    'label'      => $tmpPdfTotalAmount['label'],
                                    'amount'     => $pdfTotal->getAmount(),
                                    'baseAmount' => Mage::app()->getStore()->roundPrice(
                                        $pdfTotal->getAmount() * $order->getBaseToOrderRate()
                                    )
                                );
                            }
                        }
                    }
                    break;
            }
        }

        //support Mico Rushprocessing
        if ((float)$this->getOrder($salesObject)->getMicoRushprocessingprice() > 0) {
            $totals[$sortOrder][] = array(
                'label'=>'Product &amp; Packaging:',
                'amount'=>(float)$this->getOrder($salesObject)->getMicoRushprocessingprice(),
                'baseAmount'=>(float)$this->getOrder($salesObject)->getMicoRushprocessingprice()
            );
        }
        
        //support payment fee by XIB
        //use same settings as shipping (total does not provide separate settings)       
        if ((float)$salesObject->getXibpaymentsFee()) {
            $totals[550][] = Mage::helper('xibpayments/pdfcustomiser')->appendTotals(
                $totals[$sortOrder], $salesObject, $order, $helper->getStoreId()
            );
        }

        //Prepare Cash on Delivery
        //use same settings as shipping (total does not provide separate settings)
        if ((float)$salesObject->getCodFee()) {
            if (Mage::getStoreConfig('tax/sales_display/shipping', $helper->getStoreId())
                == Mage_Tax_Model_Config::DISPLAY_TYPE_INCLUDING_TAX
            ) {
                $totals[550][] = array(
                    'label'     => str_replace(
                        ' &amp; ', ' & ', Mage::getStoreConfig('payment/cashondelivery/title', $helper->getStoreId())
                    ) . ':',
                    'amount'    => $salesObject->getCodFee() + $order->getCodTaxAmount(),
                    'baseAmount'=> $salesObject->getBaseCodFee() + $order->getBaseCodTaxAmount()
                );
            } elseif (Mage::getStoreConfig('tax/sales_display/shipping', $helper->getStoreId())
                == Mage_Tax_Model_Config::DISPLAY_TYPE_BOTH
            ) {
                $totals[550][] = array(
                    'label'     => str_replace(
                        ' &amp; ', ' & ', Mage::getStoreConfig('payment/cashondelivery/title', $helper->getStoreId())
                    ) . ' ' . Mage::helper('tax')->__('Incl. Tax') . ':',
                    'amount'    => $salesObject->getCodFee() + $order->getCodTaxAmount(),
                    'baseAmount'=> $salesObject->getBaseCodFee() + $order->getBaseCodTaxAmount()
                );
                $totals[550][] = array(
                    'label'     => str_replace(
                        ' &amp; ', ' & ', Mage::getStoreConfig('payment/cashondelivery/title', $helper->getStoreId())
                    ) . ' ' . Mage::helper('tax')->__('Excl. Tax') . ':',
                    'amount'    => $salesObject->getCodFee(),
                    'baseAmount'=> $salesObject->getBaseCodFee()
                );
            } else {
                $totals[550][] = array(
                    'label'     =>
                    str_replace(
                        ' &amp; ', ' & ', Mage::getStoreConfig('payment/cashondelivery/title', $helper->getStoreId())
                    ) . ':',
                    'amount'    => $salesObject->getCodFee(),
                    'baseAmount'=> $salesObject->getBaseCodFee()
                );
            }
        }

        /*
        //Prepare Klarna-Faktura Invoice fee(separate extension by trollweb_kreditor)
        //use same settings as shipping (total does not provide separate settings)
        $paymentInfo = $order->getPayment()->getMethodInstance()->getInfoInstance();
        $invoiceFee = $paymentInfo->getAdditionalInformation('invoice_fee');
        if ((float)$invoiceFee){
            $sortOrder = Mage::getStoreConfig('sales/totals_sort/tax',$helper->getStoreId());
            $totals[550][] = array(
                    'label'=>str_replace(' &amp; ',' & ',Mage::helper('kreditor')->__('Klarna Invoice fee')).':',
                    'amount'=>$invoiceFee+$paymentInfo->getAdditionalInformation('invoice_tax_amount'),
                    'baseAmount'=>$paymentInfo->getAdditionalInformation('base_invoice_fee')
                                +$paymentInfo->getAdditionalInformation('base_invoice_tax_amount')
            );
        }
        */

        //Grand Total
        $grandTotals = array();
        
        if (Mage::getStoreConfigFlag('sales_pdf/all/allonly1grandtotal', $helper->getStoreId())) {
            $grandTotals[] = array(
                'label'      => Mage::helper('sales')->__('Grand Total') . ':',
                'amount'     => $salesObject->getGrandTotal(),
                'baseAmount' => $salesObject->getBaseGrandTotal(),
                'bold'       => true
            );            
        } elseif (Mage::getStoreConfig('tax/sales_display/grandtotal', $helper->getStoreId())) {
            $grandTotals[] = array(
                'label'      => Mage::helper('sales')->__('Grand Total')
                    . ' (' . Mage::helper('tax')->__('Excl. Tax') . '):',
                'amount'     => $salesObject->getGrandTotal() - $salesObject->getTaxAmount(),
                'baseAmount' => $salesObject->getBaseGrandTotal() - $salesObject->getBaseTaxAmount(),
                'bold'       => true
            );
            if ((float)$salesObject->getTaxAmount() > 0) {
                //Magento looses information of tax rates if an order is split into multiple invoices
                //so only display summary if both tax amounts equal
                if (Mage::getStoreConfig('tax/sales_display/full_summary', $helper->getStoreId())
                    && $order->getTaxAmount() == $salesObject->getTaxAmount()
                ) {
                    $filteredTaxrates = array();
                    //need to filter out doubled up taxrates on edited/reordered items -> Magento bug
                    foreach ($order->getFullTaxInfo() as $taxrate) {
                        foreach ($taxrate['rates'] as $rate) {
                            $taxId = $rate['code'];
                            if (!isset($rate['title'])) {
                                $rate['title'] = $taxId;
                            }
                            $filteredTaxrates[$taxId] = array('id'        => $rate['code'],
                                                              'percent'   => $rate['percent'],
                                                              'amount'    => $taxrate['amount'],
                                                              'baseAmount'=> $taxrate['base_amount'],
                                                              'title'     => $rate['title']
                            );
                        }
                    }
                    foreach ($filteredTaxrates as $filteredTaxrate) {
                        $grandTotals[] = array(
                            'label'      => $filteredTaxrate['title'] . ':',
                            'amount'     => (float)$filteredTaxrate['amount'],
                            'baseAmount' => (float)$filteredTaxrate['baseAmount'],
                            'bold'       => false
                        );
                    }
                } else {
                    $grandTotals[] = array(
                        'label'     => $helper->getTranslatedString('Tax', 'sales') . ":",
                        'amount'    => (float)$salesObject->getTaxAmount(),
                        'baseAmount'=> (float)$salesObject->getBaseTaxAmount(),
                        'bold'      => false
                    );
                }
            } elseif (Mage::getStoreConfig('sales/totals_sort/zero_tax', $helper->getStoreId())) {
                    $grandTotals[] = array(
                        'label'     => $helper->getTranslatedString('Tax', 'sales') . ":",
                        'amount'    => 0,
                        'baseAmount'=> 0,
                        'bold'      => false
                    );
            }
            $grandTotals[] = array(
                    'label'=> Mage::helper('sales')->__('Grand Total'). ' ('.Mage::helper('tax')->__('Incl. Tax').'):',
                    'amount'    => $salesObject->getGrandTotal(),
                    'baseAmount'=> $salesObject->getBaseGrandTotal(),
                    'bold'      => true
            );
        } else {
            $grandTotals[] = array(
                'label'     => Mage::helper('sales')->__('Grand Total') . ':',
                'amount'    => $salesObject->getGrandTotal() - $salesObject->getTaxAmount(),
                'baseAmount'=> $salesObject->getBaseGrandTotal() - $salesObject->getBaseTaxAmount(),
                'bold'      => true
            );            
        }

        //Enterprise output refunded to store credit
        if ((float)$salesObject->getCustomerBalanceTotalRefunded()) {
            $grandTotals[] = array(
                'label'     => Mage::helper('enterprise_giftcardaccount')->__('Refunded to Store Credit') . ':',
                'amount'    => $salesObject->getCustomerBalanceTotalRefunded(),
                'baseAmount'=> $salesObject->getCustomerBalanceTotalRefunded(),
                'bold'      => true
            );
        }
        
        ksort($totals);
        $totalsSorted = array();
        foreach ($totals as $sortOrder) {
            foreach ($sortOrder as $total) {
                $formattedTotal = $total;
                $formattedTotal['amount_default'] = $this->formatPrice($helper, $order, $total['amount']);
                $formattedTotal['amount'] = $this->formatPrice($helper, $order, $total['amount']);
                $formattedTotal['base_amount'] = $this->formatPrice($helper, $order, $total['baseAmount'], 'base');
                $totalsSorted['totals'][] = $formattedTotal;
            }           
        }        
        foreach ($grandTotals as $total) {
            $formattedTotal = $total;
            $formattedTotal['amount_default'] = $this->formatPrice($helper, $order, $total['amount']);
            $formattedTotal['amount'] = $this->formatPrice($helper, $order, $total['amount']);
            $formattedTotal['base_amount'] = $this->formatPrice($helper, $order, $total['baseAmount'], 'base');
            $totalsSorted['grand_totals'][] = $formattedTotal;
        }
        if (!isset($totalsSorted['totals'])) {
            $totalsSorted['totals'] = array();
        }
        return $totalsSorted;
    }

    /**
     * format the price according to locale settings
     *
     * @param      $helper
     * @param      $order
     * @param      $price
     * @param null $currency
     *
     * @return string
     */
    public function formatPrice($helper, $order, $price, $currency=null)
    {
        $price = sprintf("%F", $price);
        if ($helper->isRtl()) {
            if ($currency == 'base') {
                $price = Mage::app()->getLocale()->currency($order->getBaseCurrencyCode())
                    ->toCurrency($price, array('position' => Zend_Currency::LEFT));
            } else {
                $price = Mage::app()->getLocale()->currency($order->getOrderCurrencyCode())
                    ->toCurrency($price, array('position' => Zend_Currency::LEFT));
            }
        } else {
            if ($currency == 'base') {
                $price = Mage::app()->getLocale()->currency($order->getBaseCurrencyCode())
                    ->toCurrency($price, array());
            } else {
                $price = Mage::app()->getLocale()->currency($order->getOrderCurrencyCode())
                    ->toCurrency($price, array());
            }
        }
        return $price;
    }
    
}
