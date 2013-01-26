<?php
/**
 * ECGiken_Price
 *
 * Copyright (c) 2011, EC-GIKEN Inc.
 * All rights reserved.
 */

class ECGikenJp_L10N_Block_Adminhtml_Permissions_Grid_User extends Mage_Adminhtml_Block_Permissions_Grid_User {

    protected function _prepareColumns() {

        $this->addColumn('user_id', array(
            'header'    =>Mage::helper('adminhtml')->__('ID'),
            'width'     =>5,
            'align'     =>'right',
            'sortable'  =>true,
            'index'     =>'user_id'
        ));
        $this->addColumn('username', array(
            'header'    =>Mage::helper('adminhtml')->__('User Name'),
            'index'     =>'username'
        ));
        $this->addColumn('lastname', array(
            'header'    =>Mage::helper('adminhtml')->__('Last Name'),
            'index'     =>'lastname'
        ));
        $this->addColumn('firstname', array(
            'header'    =>Mage::helper('adminhtml')->__('First Name'),
            'index'     =>'firstname'
        ));
        $this->addColumn('email', array(
            'header'    =>Mage::helper('adminhtml')->__('Email'),
            'width'     =>40,
            'align'     =>'left',
            'index'     =>'email'
        ));

        return parent::_prepareColumns();
    }
}

