<?php

/**
 * @author     Kristof Ringleff
 * @package    Fooman_PdfCustomiser
 * @copyright  Copyright (c) 2009 Fooman Limited (http://www.fooman.co.nz)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Fooman_PdfCustomiser_Helper_Data extends Fooman_EmailAttachments_Helper_Data
{
    const LOG_FILE_NAME='fooman_pdfcustomiser.log';

    /**
     * convert pdf object to string and attach to mail object
     *
     * @param        $pdf
     * @param        $mailObj
     * @param string $name
     *
     * @return mixed
     */
    public function addAttachment($pdf, $mailObj, $name = "order.pdf")
    {
        try {
            $this->debug('ADDING ATTACHMENT: '.$name);
            if ($this->writePdfsToDisk()) {
                $dir = Mage::getBaseDir().DS.'media'.DS.'pdfs';
                if (file_exists($dir)) {
                    $pdfFileName = $dir . DS . $name . '.pdf';
                    if (file_exists($pdfFileName)) {
                        //uncomment here to delete existing files and keep the last sent pdf
                        //unlink($pdfFileName);
                    }
                    if (!file_exists($pdfFileName)) {
                        $pdf->render(null, null, $pdfFileName);
                    }
                }
            }
            $file = $pdf->render();
            $mailObj->getMail()->createAttachment(
                $file,
                'application/pdf',
                Zend_Mime::DISPOSITION_ATTACHMENT,
                Zend_Mime::ENCODING_BASE64,
                $name . '.pdf'
            );
            $this->debug('FINISHED ADDING ATTACHMENT: '.$name);
        } catch (Exception $e) {
            Mage::logException($e);
        }
        return $mailObj;
    }

    /**
     * log debug message if in debug mode
     *
     * @param $msg
     */
    public function debug($msg)
    {
        if ($this->isDebugMode()) {
            Mage::log($msg, null, self::LOG_FILE_NAME);
        }
    }

    /**
     * are we in debug mode?
     *
     * @return bool
     */
    public function isDebugMode()
    {
        return false;
    }

    /**
     * should we write pdf email attachments to disk?
     *
     * @return bool
     */
    public function writePdfsToDisk()
    {
        return false;
    }
}