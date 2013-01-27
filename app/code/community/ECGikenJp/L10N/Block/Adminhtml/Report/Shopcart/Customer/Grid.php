<?php
/**
 * ECGiken_Price
 *
 * Copyright (c) 2011, EC-GIKEN Inc.
 * All rights reserved.
 */

class ECGikenJp_L10N_Block_Adminhtml_Report_Shopcart_Customer_Grid extends Mage_Adminhtml_Block_Report_Shopcart_Customer_Grid {

    protected function _prepareColumns() {

        $this->addColumn('entity_id', array(
            'header'    =>Mage::helper('reports')->__('ID'),
            'width'     =>'50px',
            'align'     =>'right',
            'index'     =>'entity_id'
        ));

        $this->addColumn('lastname', array(
            'header'    =>Mage::helper('reports')->__('Last Name'),
            'index'     =>'lastname'
        ));

        $this->addColumn('firstname', array(
            'header'    =>Mage::helper('reports')->__('First Name'),
            'index'     =>'firstname'
        ));

        $this->addColumn('items', array(
            'header'    =>Mage::helper('reports')->__('Items in Cart'),
            'width'     =>'70px',
            'sortable'  =>false,
            'align'     =>'right',
            'index'     =>'items'
        ));

        $this->addColumn('total', array(
            'header'    =>Mage::helper('reports')->__('Total'),
            'width'     =>'70px',
            'sortable'  =>false,
            'type'      =>'currency',
            'align'     =>'right',
            'currency_code' => $this->getCurrentCurrencyCode(),
            'index'     =>'total',
            'renderer'  =>'adminhtml/report_grid_column_renderer_currency'
        ));

        $this->setFilterVisibility(false);

        $this->addExportType('*/*/exportCustomerCsv', Mage::helper('reports')->__('CSV'));
        $this->addExportType('*/*/exportCustomerExcel', Mage::helper('reports')->__('Excel XML'));

        return parent::_prepareColumns();
    }
}

