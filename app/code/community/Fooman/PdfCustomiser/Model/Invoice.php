<?php

/**
 * @author     Kristof Ringleff
 * @package    Fooman_PdfCustomiser
 * @copyright  Copyright (c) 2009 Fooman Limited (http://www.fooman.co.nz)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Fooman_PdfCustomiser_Model_Invoice extends Fooman_PdfCustomiser_Model_Abstract
{
    const PDFCUSTOMISER_PDF_TYPE='invoice';

    /**
     * return type of pdf being rendered
     *
     * @return string
     */
    public function getPdfType()
    {
        return self::PDFCUSTOMISER_PDF_TYPE;
    }

    /**
     * Creates invoice pdf using the tcpdf library
     * pdf is either returned as object or sent to
     * the browser
     *
     * @param array $invoicesGiven
     * @param array $orderIds
     * @param null  $pdf
     * @param bool  $suppressOutput
     * @access public
     *
     * @return bool|Fooman_PdfCustomiser_Model_Mypdf
     */
    public function renderPdf($invoicesGiven = array(),$orderIds = array(), $pdf = null, $suppressOutput = false)
    {

        if (empty($pdf) && empty($invoicesGiven) && empty($orderIds)) {
            Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('adminhtml')->__('There are no printable documents related to selected orders')
            );
            return false;
        }

        //we will be working through an array of orderIds later - fill it up if only invoices are given
        if (!empty($invoicesGiven)) {
            foreach ($invoicesGiven as $invoiceGiven) {
                $currentOrderId = $invoiceGiven->getOrder()->getId();
                $orderIds[] = $currentOrderId;
                $invoiceIds[$currentOrderId] = $invoiceGiven->getId();
            }
        }

        $this->_beforeGetPdf();

        //need to get the store id from the first order to intialise pdf
        $storeId = Mage::getModel('sales/order')->load(current($orderIds))->getStoreId();

        //work with a new pdf or add to existing one
        if (empty($pdf)) {
            $pdf = Mage::getModel(
                'pdfcustomiser/mypdf', array(
                    'P', 'mm', Mage::getStoreConfig('sales_pdf/all/allpagesize', $storeId), true, 'UTF-8', false, false
                )
            );
        }

        foreach ($orderIds as $orderId) {
            //load data

            $order = Mage::getModel('sales/order')->load($orderId);
            if (!empty($invoicesGiven)) {
                $invoices = Mage::getResourceModel('sales/order_invoice_collection')
                    ->addAttributeToSelect('*')
                    ->setOrderFilter($orderId)
                    ->addAttributeToFilter('entity_id', $invoiceIds[$orderId])
                    ->load();
            } else {
                $invoices = Mage::getResourceModel('sales/order_invoice_collection')
                    ->addAttributeToSelect('*')
                    ->setOrderFilter($orderId)
                    ->load();
            }

            //loop over invoices
            if ($invoices->getSize() > 0) {
                foreach ($invoices as $invoice) {
                    $i = $pdf->getNumPages();
                    if ($i > 0) {
                        $pdf->endPage();
                    }

                    // create new invoice helper
                    /* var $invoiceHelper Fooman_PdfCustomiser_Helper_Pdf_Invoice */
                    $invoiceHelper = Mage::helper('pdfcustomiser/pdf_invoice');
                    $invoice->load($invoice->getId());
                    $storeId = $invoice->getStoreId();
                    if ($storeId) {
                        $appEmulation = Mage::getSingleton('core/app_emulation');
                        $initial = $appEmulation->startEnvironmentEmulation(
                            $storeId, Mage_Core_Model_App_Area::AREA_FRONTEND, true
                        );
                    }

                    $invoiceHelper->setStoreId($storeId);
                    $invoiceHelper->setSalesObject($invoice);                    
                    $pdf->setStoreId($storeId);
                    $pdf->setPdfHelper($invoiceHelper);
                    // set standard pdf info
                    $pdf->SetStandard($invoiceHelper);
                    if ($invoiceHelper->getPdfIntegratedLabels()) {
                        $pdf->SetAutoPageBreak(true, 85);
                    }
                    
                    // add a new page
                    $pdf->setIncrementId($invoice->getIncrementId());
                    if ($i == 0) {
                        $pdf->AddPage();
                    } else {
                        $pdf->startPage();
                    }

                    // Print the logo
                    $pdf->printHeader($invoiceHelper, $invoiceHelper->getPdfTitle());

                    // Prepare Line Items
                    $pdf->prepareLineItems($invoiceHelper, $invoice, $order);

                    // Prepare Top
                    $top = Mage::app()->getLayout()->createBlock('pdfcustomiser/pdf_block')
                        ->setPdfHelper($invoiceHelper)
                        ->setTemplate('fooman/pdfcustomiser/invoice/top.phtml')
                        ->toHtml();

                    $processor = Mage::helper('cms')->getBlockTemplateProcessor();
                    $processor->setVariables(
                        array(
                            'order' => $order,
                            'sales_object' => $invoice,
                            'billing_address'=> $pdf->PrepareCustomerAddress($invoiceHelper, $order, 'billing'),
                            'shipping_address'=> $pdf->PrepareCustomerAddress($invoiceHelper, $order, 'shipping'),
                            'payment'=> $pdf->PreparePayment($invoiceHelper, $order, $invoice),
                            'shipping'=> nl2br($pdf->PrepareShipping($invoiceHelper, $order, $invoice))
                        )
                    );
                    $top = $processor->filter($top);

                    //Prepare Totals
                    $totals = $this->PrepareTotals($invoiceHelper, $invoice);

                    //Prepare Bottom
                    $bottom = Mage::app()->getLayout()->createBlock('pdfcustomiser/pdf_block')
                        ->setPdf($pdf)
                        ->setPdfHelper($invoiceHelper)
                        ->setTotals($totals)
                        ->setTemplate('fooman/pdfcustomiser/invoice/bottom.phtml')
                        ->toHtml();
                    $processor->setVariables(
                        array(
                            'order'        => $order,
                            'sales_object' => $invoice
                        )
                    );
                    $bottom = $processor->filter($bottom);

                    //Prepare Items
                    $items = Mage::app()->getLayout()->createBlock('pdfcustomiser/pdf_items')
                        ->setPdf($pdf)
                        ->setPdfHelper($invoiceHelper)
                        ->setTemplate('fooman/pdfcustomiser/items.phtml')
                        ->toHtml();

                    //Put it all together
                    $pdf->writeHTML($top);
                    $pdf->SetFont($invoiceHelper->getPdfFont(), '', $invoiceHelper->getPdfFontsize('small'));
                    $pdf->writeHTML($items, true, false, false, false, '');
                    $pdf->SetFont($invoiceHelper->getPdfFont(), '', $invoiceHelper->getPdfFontsize());
                    //reset Margins in case there was a page break
                    $pdf->setMargins($invoiceHelper->getPdfMargins('sides'), $invoiceHelper->getPdfMargins('top'));
                    $pdf->writeHTML($bottom);
                    
                    /*
                    //Uncomment this block: delete /* and * /
                    //to add legal text for German invoices. EuVat Extension erforderlich
                    switch($order->getCustomerGroupId()){
                        case 2:
                            $pdf->Cell(0, 0, 'steuerfrei nach § 4 Nr. 1 b UStG', 0, 2, 'L',null,null,1);
                            break;
                        case 1:
                            $pdf->Cell(0, 0, 'umsatzsteuerfreie Ausfuhrlieferung', 0, 2, 'L',null,null,1);
                            break;
                    }
                     */

                    //print extra addresses for peel off labels
                    if ($invoiceHelper->getPdfIntegratedLabels()) {
                        $pdf->OutputCustomerAddresses($invoiceHelper, $order, $invoiceHelper->getPdfIntegratedLabels());
                    }

                    if ($invoice->getStoreId()) {
                        $appEmulation->stopEnvironmentEmulation($initial);
                    }
                    $pdf->setPdfAnyOutput(true);
                }
            }
        }

        //output PDF document
        if (!$suppressOutput) {
            if ($pdf->getPdfAnyOutput()) {
                // reset pointer to the last page
                $pdf->lastPage();
                $pdf->Output(
                    preg_replace("/[^a-zA-Z]/", "", $invoiceHelper->getPdfTitle()) . '_' . Mage::getSingleton(
                        'core/date'
                    )->date('Y-m-d_H-i-s') . '.pdf',
                    $invoiceHelper->getNewWindow()
                );
                exit;
            } else {
                Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('adminhtml')->__('There are no printable documents related to selected orders')
                );
            }
        }

        $this->_afterGetPdf();

        return $pdf;
    }

}