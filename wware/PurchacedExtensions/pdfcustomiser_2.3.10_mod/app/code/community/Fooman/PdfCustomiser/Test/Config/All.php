<?php

/**
 * Test class for config.xml.
 */
class Fooman_PdfCustomiser_Test_Config_All extends EcomDev_PHPUnit_Test_Case_Config
{
    public function testModule()
    {
        $this->assertModuleCodePool('community');
        $this->assertModuleVersionGreaterThan('2.3.3');
    }

    public function testModels()
    {
        $this->assertModelAlias(
            'sales/order_pdf_invoice',
            'Fooman_PdfCustomiser_Model_Invoice'
        );
        $this->assertModelAlias(
            'sales/order_pdf_shipment',
            'Fooman_PdfCustomiser_Model_Shipment'
        );
        $this->assertModelAlias(
            'sales/order_pdf_creditmemo',
            'Fooman_PdfCustomiser_Model_Creditmemo'
        );
        $this->assertModelAlias(
            'emailattachments/order_pdf_order',
            'Fooman_PdfCustomiser_Model_Order'
        );
    }

    public function testConfig()
    {
        $this->assertConfigNodeHasChild('global/helpers', 'pdfcustomiser');
        $this->assertConfigNodeValue('global/helpers/pdfcustomiser/class', 'Fooman_PdfCustomiser_Helper');
        $this->assertConfigNodeHasChild('global/models', 'pdfcustomiser');
        $this->assertConfigNodeValue('global/models/pdfcustomiser/class', 'Fooman_PdfCustomiser_Model');
        $this->assertConfigNodeHasChild('global/blocks', 'pdfcustomiser');
        $this->assertConfigNodeValue('global/blocks/pdfcustomiser/class', 'Fooman_PdfCustomiser_Block');
    }
}
