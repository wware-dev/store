<?php
/**
 * ECGiken_Price
 *
 * Copyright (c) 2011, EC-GIKEN Inc.
 * All rights reserved.
 */

class ECGikenJp_L10N_Block_Adminhtml_Catalog_Product_Edit_Tab_Tag_Customer extends Mage_Adminhtml_Block_Catalog_Product_Edit_Tab_Tag_Customer {

    protected function _prepareColumns()
    {
        $this->addColumn('lastname', array(
            'header'        => Mage::helper('catalog')->__('Last Name'),
            'index'         => 'lastname',
        ));

        $this->addColumn('firstname', array(
            'header'    => Mage::helper('catalog')->__('First Name'),
            'index'     => 'firstname',
        ));

        $this->addColumn('email', array(
            'header'        => Mage::helper('catalog')->__('Email'),
            'index'         => 'email',
        ));

        $this->addColumn('name', array(
            'header'        => Mage::helper('catalog')->__('Tag Name'),
            'index'         => 'name',
        ));

        return parent::_prepareColumns();
    }
}
