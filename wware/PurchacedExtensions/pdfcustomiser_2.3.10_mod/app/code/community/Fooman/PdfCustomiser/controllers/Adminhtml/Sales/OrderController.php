<?php

/**
 * @author     Kristof Ringleff
 * @package    Fooman_PdfCustomiser
 * @copyright  Copyright (c) 2009 Fooman Limited (http://www.fooman.co.nz)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once BP.'/app/code/community/Fooman/EmailAttachments/controllers/Admin/OrderController.php';

class Fooman_PdfCustomiser_Adminhtml_Sales_OrderController extends Fooman_EmailAttachments_Admin_OrderController
{

    /**
     * print orders from order_ids
     * @deprecated uncomment config,xml to use
     *
     * @return Mage_Core_Controller_Varien_Action
     */
    public function pdfordersAction()
    {
        $orderIds = $this->getRequest()->getPost('order_ids');
        if (sizeof($orderIds)) {
            Mage::getModel('pdfcustomiser/order')->renderPdf(null, $orderIds);
        } else {
            $this->_getSession()->addError($this->__('There are no printable documents related to selected orders'));
            $this->_redirect('*/*/');
        }
        $this->_redirect('*/*/');
    }

    /**
     * print invoices from order_ids
     * @deprecated uncomment config,xml to use
     *
     * @return Mage_Core_Controller_Varien_Action
     */
    public function pdfinvoicesAction()
    {
        $orderIds = $this->getRequest()->getPost('order_ids');
        if (sizeof($orderIds)) {
            Mage::getModel('sales/order_pdf_invoice')->renderPdf(null, $orderIds);
        } else {
            $this->_getSession()->addError($this->__('There are no printable documents related to selected orders'));
            $this->_redirect('*/*/');
        }
        $this->_redirect('*/*/');
    }

    /**
     * print credimemos from order_ids
     * @deprecated uncomment config,xml to use
     *
     * @return Mage_Core_Controller_Varien_Action
     */
    public function pdfcreditmemosAction()
    {
        $orderIds = $this->getRequest()->getPost('order_ids');
        if (sizeof($orderIds)) {
            Mage::getModel('sales/order_pdf_creditmemo')->renderPdf(null, $orderIds);
        } else {
            $this->_getSession()->addError($this->__('There are no printable documents related to selected orders'));
            $this->_redirect('*/*/');
        }
        $this->_redirect('*/*/');
    }

    /**
     * override EmailAttachment behaviour to print based on order_ids
     * to allow printing without having first created shipments
     *
     * @return Mage_Core_Controller_Varien_Action
     */
    public function pdfshipmentsAction()
    {
        $orderIds = $this->getRequest()->getPost('order_ids');
        $print = false;
        if (sizeof($orderIds)) {
            if (!Fooman_PdfCustomiser_Model_Abstract::COMPAT_MODE) {
                Mage::getModel('sales/order_pdf_shipment')->renderPdf(null, $orderIds);
                exit;
            } else {
                $pdf = Mage::getModel('sales/order_pdf_shipment')->getPdf(null, $orderIds);
                $print = $pdf->render();
            }
        }
        if ($print) {
            return $this->_prepareDownloadResponse(
                'shipments_'.Mage::getSingleton('core/date')->date('Y-m-d_H-i-s').'.pdf',
                $print,
                'application/pdf'
            );
            exit;
        } else {
            $this->_getSession()->addError(
                $this->__('There are no printable documents related to selected orders')
            );
            $this->_redirect('*/*/');
        }
        $this->_redirect('*/*/');
    }

    /**
     * override EmailAttachment behaviour to print based on order_ids
     *
     * @return Mage_Core_Controller_Varien_Action
     */
    public function pdfdocsAction()
    {
        $orderIds = $this->getRequest()->getPost('order_ids');
        if (sizeof($orderIds)) {
            if (!Fooman_PdfCustomiser_Model_Abstract::COMPAT_MODE) {
                $pdf = Mage::getModel('sales/order_pdf_invoice')->renderPdf(null, $orderIds, null, true);
                $pdf = Mage::getModel('sales/order_pdf_shipment')->renderPdf(null, $orderIds, $pdf, true);
                $pdf = Mage::getModel('pdfcustomiser/order')->renderPdf(null, $orderIds, $pdf, true);
                $pdf = Mage::getModel('sales/order_pdf_creditmemo')->renderPdf(null, $orderIds, $pdf, false, 'orderDocs_');
                exit;
            }

            $pdf = Mage::getModel('sales/order_pdf_invoice')->getPdf(null, $orderIds);
            $pages = Mage::getModel('sales/order_pdf_shipment')->getPdf(null, $orderIds);
            $pdf->pages = array_merge($pdf->pages, $pages->pages);
            $pages = Mage::getModel('pdfcustomiser/order')->getPdf(null, $orderIds);
            $pdf->pages = array_merge($pdf->pages, $pages->pages);
            $pages = Mage::getModel('sales/order_pdf_creditmemo')->getPdf(null, $orderIds);
            $pdf->pages = array_merge($pdf->pages, $pages->pages);
            return $this->_prepareDownloadResponse(
                'orderDocs_' . Mage::getSingleton('core/date')->date('Y-m-d_H-i-s') . '.pdf',
                $pdf->render(),
                'application/pdf'
            );
            exit;
        } else {
            $this->_getSession()->addError(
                $this->__('There are no printable documents related to selected orders')
            );
            $this->_redirect('*/*/');
        }
        $this->_redirect('*/*/');
    }

}