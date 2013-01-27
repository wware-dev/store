<?php
/*
 * @loadSharedFixture Observer.yaml
 */
class Fooman_EmailAttachments_Test_Model_Observer extends EcomDev_PHPUnit_Test_Case
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

    public function testOrderAttachEventDispatched()
    {
        $this->_helper->placeOrder(1);
        $this->assertEventDispatched('fooman_emailattachments_before_send_order');
    }

    public function testInvoiceAttachEventDispatched()
    {
        $order = $this->_helper->placeOrder(1);
        $this->_helper->processInvoice($order);
        $this->assertEventDispatched('fooman_emailattachments_before_send_invoice');
    }

    public function testShipmentAttachEventDispatched()
    {
        $order = $this->_helper->placeOrder(1);
        $this->_helper->processShipment($order);
        $this->assertEventDispatched('fooman_emailattachments_before_send_shipment');
    }

    public function testCredimemoAttachEventDispatched()
    {
        $order = $this->_helper->placeOrder(1);
        $this->_helper->processInvoice($order);
        $this->_helper->processCreditmemo($order);
        $this->assertEventDispatched('fooman_emailattachments_before_send_creditmemo');
    }
}