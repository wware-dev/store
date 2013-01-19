<?php
/**
 * ECGiken_Price
 *
 * Copyright (c) 2011, EC-GIKEN Inc.
 * All rights reserved.
 */

class ECGikenJp_L10N_Block_Adminhtml_Tag_Grid_Customers extends Mage_Adminhtml_Block_Tag_Grid_Customers {

    protected function _prepareColumns() {

        $this->addColumn('entity_id', array(
            'header'    =>Mage::helper('tag')->__('ID'),
            'width'     => '40px',
            'align'     =>'center',
            'sortable'  =>true,
            'index'     =>'entity_id'
        ));
        $this->addColumn('lastname', array(
            'header'    =>Mage::helper('tag')->__('Last Name'),
            'index'     =>'lastname'
        ));
        $this->addColumn('firstname', array(
            'header'    =>Mage::helper('tag')->__('First Name'),
            'index'     =>'firstname'
        ));
        $this->addColumn('tags', array(
            'header'    => Mage::helper('tag')->__('Tags'),
            'index'     => 'tags',
            'sortable'  => false,
            'filter'    => false,
            'renderer'  => 'adminhtml/tag_grid_column_renderer_tags'
        ));
        $this->addColumn('action', array(
            'header'    =>Mage::helper('tag')->__('Action'),
            'align'     =>'center',
            'width'     => '120px',
            'format'    =>'<a href="'.$this->getUrl('*/*/products/customer_id/$entity_id').'">'.Mage::helper('tag')->__('View Products').'</a>',
            'filter'    =>false,
            'sortable'  =>false,
            'is_system' =>true
        ));

        $this->setColumnFilter('entity_id')
            ->setColumnFilter('email')
            ->setColumnFilter('firstname')
            ->setColumnFilter('lastname');

        return parent::_prepareColumns();
    }
}
