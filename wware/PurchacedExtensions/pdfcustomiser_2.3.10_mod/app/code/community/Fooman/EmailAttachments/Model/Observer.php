<?php

/**
 * @author     Kristof Ringleff
 * @package    Fooman_EmailAttachments
 * @copyright  Copyright (c) 2009 Fooman Limited (http://www.fooman.co.nz)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Fooman_EmailAttachments_Model_Observer
{

    /**
     * observe core_block_abstract_prepare_layout_after to add a Print Orders
     * massaction to the actions dropdown menu
     *
     * @param $observer
     */
    public function addbutton($observer)
    {
        $block = $observer->getEvent()->getBlock();
        if ($block instanceof Mage_Adminhtml_Block_Widget_Grid_Massaction
            || $block instanceof
                Enterprise_SalesArchive_Block_Adminhtml_Sales_Order_Grid_Massaction
        ) {
            if ($block->getRequest()->getControllerName() == 'sales_order' ||
                $block->getRequest()->getControllerName() == 'adminhtml_sales_order') {
                $block->addItem(
                    'pdforders_order', array(
                        'label'=> Mage::helper('emailattachments')->__('Print Orders'),
                        'url'  => Mage::helper('adminhtml')->getUrl(
                            'emailattachments/admin_order/pdforders',
                            Mage::app()->getStore()->isCurrentlySecure() ? array('_secure'=> 1) : array()
                        ),
                    )
                );
            }
        }
        if ($block instanceof Mage_Adminhtml_Block_Sales_Order_View) {
            $block->addButton(
                'print', array(
                    'label'     => Mage::helper('sales')->__('Print'),
                    'class'     => 'save',
                    'onclick'   => 'setLocation(\'' . $this->getPrintUrl($block) . '\')'
                )
            );
        }
    }

    /**
     * return url to print single order from order > view
     *
     * @param void
     * @access protected
     *
     * @return string
     */
    protected function getPrintUrl($block)
    {
        if ((string)Mage::getConfig()->getModuleConfig('Fooman_PdfCustomiser')->active == 'true') {
            return $block->getUrl(
                'pdfcustomiser/adminhtml_sales_order/print',
                array('order_id' => $block->getOrder()->getId())
            );
        } else {
            return $block->getUrl(
                'emailattachments/admin_order/print',
                array('order_id' => $block->getOrder()->getId())
            );
        }
    }

    /**
     * listen to order email send event to attach pdfs and agreements
     *
     * @param $observer
     */
    public function beforeSendOrder ($observer)
    {
        $update = $observer->getEvent()->getUpdate();
        $mailTemplate = $observer->getEvent()->getTemplate();
        $order = $observer->getEvent()->getObject();
        $configPath = $update ? 'order_comment' : 'order';

        if (Mage::getStoreConfig('sales_email/' . $configPath . '/attachpdf', $order->getStoreId())) {
            //Create Pdf and attach to email - play nicely with PdfCustomiser
            $pdf = Mage::getModel('emailattachments/order_pdf_order')->getPdf(array($order));
            $mailTemplate = Mage::helper('emailattachments')->addAttachment(
                $pdf, $mailTemplate, Mage::helper('sales')->__('Order') . "_" . $order->getIncrementId()
            );
        }

        if (Mage::getStoreConfig('sales_email/' . $configPath . '/attachagreement', $order->getStoreId())) {
            $mailTemplate = Mage::helper('emailattachments')->addAgreements($order->getStoreId(), $mailTemplate);
        }

        $fileAttachment = Mage::getStoreConfig('sales_email/' . $configPath . '/attachfile', $order->getStoreId());
        if ($fileAttachment) {
            $mailTemplate = Mage::helper('emailattachments')->addFileAttachment($fileAttachment, $mailTemplate);
        }
    }

    /**
     * listen to invoice email send event to attach pdfs and agreements
     *
     * @param $observer
     */
    public function beforeSendInvoice ($observer)
    {
        $update = $observer->getEvent()->getUpdate();
        $mailTemplate = $observer->getEvent()->getTemplate();
        $invoice = $observer->getEvent()->getObject();
        $configPath = $update ? 'invoice_comment' : 'invoice';

        if (Mage::getStoreConfig('sales_email/' . $configPath . '/attachpdf', $invoice->getStoreId())) {
            //Create Pdf and attach to email - play nicely with PdfCustomiser
            $pdf = Mage::getModel('sales/order_pdf_invoice')->getPdf(array($invoice));
            $mailTemplate = Mage::helper('emailattachments')->addAttachment(
                $pdf,
                $mailTemplate,
                Mage::helper('sales')->__('Invoice') . "_" . $invoice->getIncrementId()
            );
        }

        if (Mage::getStoreConfig('sales_email/' . $configPath . '/attachagreement', $invoice->getStoreId())) {
            $mailTemplate = Mage::helper('emailattachments')->addAgreements($invoice->getStoreId(), $mailTemplate);
        }

        $fileAttachment = Mage::getStoreConfig('sales_email/' . $configPath . '/attachfile', $invoice->getStoreId());
        if ($fileAttachment) {
            $mailTemplate = Mage::helper('emailattachments')->addFileAttachment($fileAttachment, $mailTemplate);
        }
    }

    /**
     * listen to shipment email send event to attach pdfs and agreements
     *
     * @param $observer
     */
    public function beforeSendShipment ($observer)
    {
        $update = $observer->getEvent()->getUpdate();
        $mailTemplate = $observer->getEvent()->getTemplate();
        $shipment = $observer->getEvent()->getObject();
        $configPath = $update ? 'shipment_comment' : 'shipment';

        if (Mage::getStoreConfig('sales_email/' . $configPath . '/attachpdf', $shipment->getStoreId())) {
            //Create Pdf and attach to email - play nicely with PdfCustomiser
            $pdf = Mage::getModel('sales/order_pdf_shipment')->getPdf(array($shipment));
            $mailTemplate = Mage::helper('emailattachments')->addAttachment(
                $pdf, $mailTemplate, Mage::helper('sales')->__('Shipment') . "_" . $shipment->getIncrementId()
            );
        }

        if (Mage::getStoreConfig('sales_email/' . $configPath . '/attachagreement', $shipment->getStoreId())) {
            $mailTemplate = Mage::helper('emailattachments')->addAgreements($shipment->getStoreId(), $mailTemplate);
        }

        $fileAttachment = Mage::getStoreConfig('sales_email/' . $configPath . '/attachfile', $shipment->getStoreId());
        if ($fileAttachment) {
            $mailTemplate = Mage::helper('emailattachments')->addFileAttachment($fileAttachment, $mailTemplate);
        }
    }

    /**
     * listen to creditmemo email send event to attach pdfs and agreements
     *
     * @param $observer
     */
    public function beforeSendCreditmemo ($observer)
    {
        $update = $observer->getEvent()->getUpdate();
        $mailTemplate = $observer->getEvent()->getTemplate();
        $creditmemo = $observer->getEvent()->getObject();
        $configPath = $update ? 'creditmemo_comment' : 'creditmemo';

        if (Mage::getStoreConfig('sales_email/' . $configPath . '/attachpdf', $creditmemo->getStoreId())) {
            //Create Pdf and attach to email - play nicely with PdfCustomiser
            $pdf = Mage::getModel('sales/order_pdf_creditmemo')->getPdf(array($creditmemo));
            $mailTemplate = Mage::helper('emailattachments')->addAttachment(
                $pdf, $mailTemplate, Mage::helper('sales')->__('Credit Memo') . "_" . $creditmemo->getIncrementId()
            );
        }

        if (Mage::getStoreConfig('sales_email/' . $configPath . '/attachagreement', $creditmemo->getStoreId())) {
            $mailTemplate = Mage::helper('emailattachments')->addAgreements($creditmemo->getStoreId(), $mailTemplate);
        }

        $fileAttachment = Mage::getStoreConfig('sales_email/' . $configPath . '/attachfile', $creditmemo->getStoreId());
        if ($fileAttachment) {
            $mailTemplate = Mage::helper('emailattachments')->addFileAttachment($fileAttachment, $mailTemplate);
        }
    }

}