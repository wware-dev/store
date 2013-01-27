<?php
/*
 * @loadSharedFixture OrderController.yaml
 */
class Fooman_EmailAttachments_Test_Controller_Admin_OrderController
    extends Fooman_EmailAttachments_Test_Controller_Admin_Abstract
{

    private $_helper = '';
    private $_section = 'emailattachments';
    private $_group = 'settings';
    private $_field = 'activated';

    public function setUp()
    {
        $this->_helper = Mage::helper('foomantesting');
        $this->_helper->setSection($this->_section);
        $this->_helper->setGroup($this->_group);
        $this->_helper->setField($this->_field);
        parent::setUp();
    }

    /**
     * Test loading and rendering of index action
     *
     * @return null
     */
    public function testPrintAction()
    {
        $order = $this->_helper->placeOrder(1);
        Mage::register('isSecureArea', true);
        $this->getRequest()->setMethod('POST')
            ->setPost('order_id', $order->getId());

        $expectedRoute = 'emailattachments/admin_order/print';
        $this->dispatch($expectedRoute);
    }

    public function testSalesOrderViewHasPrintButton()
    {
        $order = $this->_helper->placeOrder(1);
        $expectedRoute = 'adminhtml/sales_order/view/order_id/'.$order->getId();
        $this->dispatch($expectedRoute);
        $this->assertResponseBodyContains('Print', true);
        if ((string)Mage::getConfig()->getModuleConfig('Fooman_PdfCustomiser')->active == 'true') {
            $this->assertResponseBodyContains('pdfcustomiser/adminhtml_sales_order/print');
        } else {
            $this->assertResponseBodyContains('emailattachments/admin_order/print');
        }
    }

    public function tearDown()
    {
        ///$this->_helper->deleteOrdersAndQuotes();
        parent::tearDown();
    }
}