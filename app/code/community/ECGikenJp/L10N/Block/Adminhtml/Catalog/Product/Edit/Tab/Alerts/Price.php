<?php
/**
 * ECGiken_Price
 *
 * Copyright (c) 2011, EC-GIKEN Inc.
 * All rights reserved.
 */

class ECGikenJp_L10N_Block_Adminhtml_Catalog_Product_Edit_Tab_Alerts_Price extends Mage_Adminhtml_Block_Catalog_Product_Edit_Tab_Alerts_Price {

    protected function _prepareColumns() {

        $this->addColumn('lastname', array(
            'header'    => Mage::helper('catalog')->__('Last Name'),
            'index'     => 'lastname',
        ));

        $this->addColumn('firstname', array(
            'header'    => Mage::helper('catalog')->__('First Name'),
            'index'     => 'firstname',
        ));

        $this->addColumn('email', array(
            'header'    => Mage::helper('catalog')->__('Email'),
            'index'     => 'email',
        ));

        $this->addColumn('price', array(
            'header'    => Mage::helper('catalog')->__('Price'),
            'index'     => 'price',
            'type'      => 'currency',
            'currency_code'
                        => Mage::getStoreConfig(Mage_Directory_Model_Currency::XML_PATH_CURRENCY_BASE)
        ));

        $this->addColumn('add_date', array(
            'header'    => Mage::helper('catalog')->__('Date Subscribed'),
            'index'     => 'add_date',
            'type'      => 'date'
        ));

        $this->addColumn('last_send_date', array(
            'header'    => Mage::helper('catalog')->__('Last Notification'),
            'index'     => 'last_send_date',
            'type'      => 'date'
        ));

        $this->addColumn('send_count', array(
            'header'    => Mage::helper('catalog')->__('Send Count'),
            'index'     => 'send_count',
        ));

        return parent::_prepareColumns();
    }
}
