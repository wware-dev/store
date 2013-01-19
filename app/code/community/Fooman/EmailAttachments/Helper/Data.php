<?php

/**
 * @author     Kristof Ringleff
 * @package    Fooman_EmailAttachments
 * @copyright  Copyright (c) 2009 Fooman Limited (http://www.fooman.co.nz)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Fooman_EmailAttachments_Helper_Data extends Mage_Core_Helper_Abstract
{

    const LOG_FILE_NAME='fooman_pdfcustomiser.log';

    /**
     * render pdf and attach to email
     *
     * @param        $pdf
     * @param        $mailObj
     * @param string $name
     *
     * @return mixed
     */
    public function addAttachment($pdf, $mailObj, $name = "order")
    {
        try {
            $this->debug('ADDING ATTACHMENT: ' . $name);
            $file = $pdf->render();
            $mailObj->getMail()->createAttachment(
                $file, 'application/pdf', Zend_Mime::DISPOSITION_ATTACHMENT, Zend_Mime::ENCODING_BASE64, $name . '.pdf'
            );
            $this->debug('FINISHED ADDING ATTACHMENT: ' . $name);
        } catch (Exception $e) {
            Mage::log('Caught error while attaching pdf:' . $e->getMessage());
        }
        return $mailObj;
    }

    /**
     * attach file to email
     * supported types: pdf
     *
     * @param        $file
     * @param        $mailObj
     *
     * @return mixed
     */
    public function addFileAttachment($file, $mailObj)
    {
        try {
            $this->debug('ADDING ATTACHMENT: ' . $file);
            $filePath = Mage::getBaseDir('media') . DS . 'pdfs' . DS .$file;
            if(file_exists($filePath)){
                $mailObj->getMail()->createAttachment(
                    file_get_contents($filePath), 'application/pdf', Zend_Mime::DISPOSITION_ATTACHMENT,
                    Zend_Mime::ENCODING_BASE64, basename($filePath)
                );
            }
            $this->debug('FINISHED ADDING ATTACHMENT: ' . $file);

        } catch (Exception $e) {
            Mage::log('Caught error while attaching pdf:' . $e->getMessage());
        }
        return $mailObj;
    }

    /**
     * attach agreements for store and attach as
     * txt or html to email
     *
     * @param $storeId
     * @param $mailObj
     *
     * @return mixed
     */
    public function addAgreements($storeId, $mailObj)
    {
        $this->debug('ADDING AGREEMENTS');
        $agreements = Mage::getModel('checkout/agreement')->getCollection()
            ->addStoreFilter($storeId)
            ->addFieldToFilter('is_active', 1);
        if ($agreements) {
            foreach ($agreements as $agreement) {
                $agreement->load($agreement->getId());
                $this->debug($agreement->getName());
                $helper = Mage::helper('cms');
                $processor = $helper->getPageTemplateProcessor();
                $content = $processor->filter($agreement->getContent());
                if ((string)Mage::getConfig()->getModuleConfig('Fooman_PdfCustomiser')->active == 'true') {
                    $pdf = Mage::getModel('pdfcustomiser/agreement')->getPdf(array($storeId=> $agreement));
                    $this->addAttachment($pdf, $mailObj, urlencode($agreement->getName()));
                } else {
                    if ($agreement->getIsHtml()) {
                        $html = '<html><head><title>' . $agreement->getName() . '</title></head><body>'
                            . $agreement->getContent() . '</body></html>';
                        $mailObj->getMail()->createAttachment(
                            $html, 'text/html', Zend_Mime::DISPOSITION_ATTACHMENT, Zend_Mime::ENCODING_BASE64,
                            urlencode($agreement->getName()) . '.html'
                        );
                    } else {
                        $mailObj->getMail()->createAttachment(
                            Mage::helper('core')->escapeHtml($agreement->getContent()), 'text/plain',
                            Zend_Mime::DISPOSITION_ATTACHMENT, Zend_Mime::ENCODING_BASE64,
                            urlencode($agreement->getName()) . '.txt'
                        );
                    }
                }
                $this->debug('Done ' . $agreement->getName());
            }
        }
        $this->debug('FINISHED ADDING AGREEMENTS');
        return $mailObj;
    }

    /**
     * if in debug mode send message to logs
     *
     * @param $msg
     */
    public function debug($msg)
    {
        if ($this->isDebugMode()) {
            Mage::helper('foomancommon')->sendToFirebug($msg);
            Mage::log($msg, null, self::LOG_FILE_NAME);
        }
    }

    /**
     * are we debugging
     *
     * @return bool
     */
    public function isDebugMode()
    {
        return false;
    }
}