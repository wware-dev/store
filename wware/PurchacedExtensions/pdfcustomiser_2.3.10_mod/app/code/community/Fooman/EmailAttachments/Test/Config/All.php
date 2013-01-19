<?php

/**
 * Test class for config.xml.
 */
class Fooman_EmailAttachments_Test_Config_All extends EcomDev_PHPUnit_Test_Case_Config
{
    public function testModule()
    {
        $this->assertModuleCodePool('community');
        $this->assertModuleVersion('0.9.0');
    }

    public function testModels()
    {
        $this->assertModelAlias(
            'core/email_template_mailer',
            'Fooman_EmailAttachments_Model_Core_Email_Template_Mailer'
        );
    }

    public function testEvents()
    {

        $this->assertEventObserverDefined(
            'adminhtml',
            'core_block_abstract_prepare_layout_after',
            'emailattachments/observer',
            'addbutton'
        );
        $this->assertEventObserverDefined(
            'global',
            'fooman_emailattachments_before_send_order',
            'emailattachments/observer',
            'beforeSendOrder'
        );
        $this->assertEventObserverDefined(
            'global',
            'fooman_emailattachments_before_send_invoice',
            'emailattachments/observer',
            'beforeSendInvoice'
        );
        $this->assertEventObserverDefined(
            'global',
            'fooman_emailattachments_before_send_shipment',
            'emailattachments/observer',
            'beforeSendShipment'
        );
        $this->assertEventObserverDefined(
            'global',
            'fooman_emailattachments_before_send_creditmemo',
            'emailattachments/observer',
            'beforeSendCreditmemo'
        );
    }

    public function testConfig()
    {
        $this->assertConfigNodeHasChild('global/helpers', 'emailattachments');
        $this->assertConfigNodeValue('global/helpers/emailattachments/class', 'Fooman_EmailAttachments_Helper');
        $this->assertConfigNodeHasChild('global/models', 'emailattachments');
        $this->assertConfigNodeValue('global/models/emailattachments/class', 'Fooman_EmailAttachments_Model');
    }
}
