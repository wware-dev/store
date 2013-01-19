<?php
class EM_ProductsFilterWidget_Block_Adminhtml_ProductsFilterWidget extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
    $this->_controller = 'adminhtml_productsfilterwidget';
    $this->_blockGroup = 'productsfilterwidget';
    $this->_headerText = Mage::helper('productsfilterwidget')->__('Item Manager');
    $this->_addButtonLabel = Mage::helper('productsfilterwidget')->__('Add Item');
    parent::__construct();
  }
}