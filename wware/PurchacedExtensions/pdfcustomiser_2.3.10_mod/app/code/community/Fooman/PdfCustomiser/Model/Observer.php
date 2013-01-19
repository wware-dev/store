<?php

/**
 * @author     Kristof Ringleff
 * @package    Fooman_PdfCustomiser
 * @copyright  Copyright (c) 2009 Fooman Limited (http://www.fooman.co.nz)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Fooman_PdfCustomiser_Model_Observer
{

    /**
     * test to combine Zend_Pdf content with tcpdf pdfs
     *
     * @param $observer
     */
    public function adjustPdf($observer)
    {

        $extractor = new Zend_Pdf_Resource_Extractor();

        $pdf = $observer->getEvent()->getPdf();
        $counter = false;
        foreach ($pdf->pages as $key => &$page) {
            if ($page instanceof Fooman_PdfCustomiser_Model_Abstract) {
                $counter = 1;
                $instance = $page;
                $firstKey = $key;
                unset ($pdf->pages[$key]);
            } elseif ($counter == 1) {
                $objectArray = $page;
                $counter++;
                unset ($pdf->pages[$key]);
            } elseif ($counter == 2) {
                $orderIds = $page;
                $tcpdf = Zend_Pdf::parse($instance->renderPdf($objectArray, $orderIds, null, true));
                foreach ($tcpdf->pages as $p) {
                    $pdf->pages[$firstKey] = $extractor->clonePage($p);
                }
                $counter = 0;
            }
        }
    }

}