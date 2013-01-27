<?php
/**
 * ECGiken_Price
 *
 * Copyright (c) 2011, EC-GIKEN Inc.
 * All rights reserved.
 */

class ECGikenJp_L10N_Block_Adminhtml_Customer_Online_Grid extends Mage_Adminhtml_Block_Customer_Online_Grid {

    protected function _prepareColumns()
    {
        $this->addColumn('customer_id', array(
            'header'    => Mage::helper('customer')->__('ID'),
            'width'     => '40px',
            'align'     => 'right',
            'type'      => 'number',
            'default'   => Mage::helper('customer')->__('n/a'),
            'index'     => 'customer_id'
        ));

        $this->addColumn('lastname', array(
            'header'    => Mage::helper('customer')->__('Last Name'),
            'default'   => Mage::helper('customer')->__('n/a'),
            'index'     => 'customer_lastname'
        ));

        $this->addColumn('firstname', array(
            'header'    => Mage::helper('customer')->__('First Name'),
            'default'   => Mage::helper('customer')->__('Guest'),
            'index'     => 'customer_firstname'
        ));

        $this->addColumn('email', array(
            'header'    => Mage::helper('customer')->__('Email'),
            'default'   => Mage::helper('customer')->__('n/a'),
            'index'     => 'customer_email'
        ));

        $this->addColumn('ip_address', array(
            'header'    => Mage::helper('customer')->__('IP Address'),
            'default'   => Mage::helper('customer')->__('n/a'),
            'index'     => 'remote_addr',
            'renderer'  => 'adminhtml/customer_online_grid_renderer_ip',
            'filter'    => false,
            'sort'      => false
        ));

        $this->addColumn('session_start_time', array(
            'header'    => Mage::helper('customer')->__('Session Start Time'),
            'align'     => 'left',
            'width'     => '200px',
            'type'      => 'datetime',
            'default'   => Mage::helper('customer')->__('n/a'),
            'index'     =>'first_visit_at'
        ));

        $this->addColumn('last_activity', array(
            'header'    => Mage::helper('customer')->__('Last Activity'),
            'align'     => 'left',
            'width'     => '200px',
            'type'      => 'datetime',
            'default'   => Mage::helper('customer')->__('n/a'),
            'index'     => 'last_visit_at'
        ));

        $typeOptions = array(
            Mage_Log_Model_Visitor::VISITOR_TYPE_CUSTOMER => Mage::helper('customer')->__('Customer'),
            Mage_Log_Model_Visitor::VISITOR_TYPE_VISITOR  => Mage::helper('customer')->__('Visitor'),
        );

        $this->addColumn('type', array(
            'header'    => Mage::helper('customer')->__('Type'),
            'index'     => 'type',
            'type'      => 'options',
            'options'   => $typeOptions,
//            'renderer'  => 'adminhtml/customer_online_grid_renderer_type',
            'index'     => 'visitor_type'
        ));

        $this->addColumn('last_url', array(
            'header'    => Mage::helper('customer')->__('Last URL'),
            'type'      => 'wrapline',
            'lineLength' => '60',
            'default'   => Mage::helper('customer')->__('n/a'),
            'renderer'  => 'adminhtml/customer_online_grid_renderer_url',
            'index'     => 'last_url'
        ));

        return parent::_prepareColumns();
    }
}
