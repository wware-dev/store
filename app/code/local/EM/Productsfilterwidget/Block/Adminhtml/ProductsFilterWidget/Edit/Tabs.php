<?php

class EM_ProductsFilterWidget_Block_Adminhtml_ProductsFilterWidget_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

  public function __construct()
  {
      parent::__construct();
      $this->setId('productsfilterwidget_tabs');
      $this->setDestElementId('edit_form');
      $this->setTitle(Mage::helper('productsfilterwidget')->__('Item Information'));
  }

  protected function _beforeToHtml()
  {
      $this->addTab('form_section', array(
          'label'     => Mage::helper('productsfilterwidget')->__('Item Information'),
          'title'     => Mage::helper('productsfilterwidget')->__('Item Information'),
          'content'   => $this->getLayout()->createBlock('productsfilterwidget/adminhtml_productsfilterwidget_edit_tab_form')->toHtml(),
      ));
     
      return parent::_beforeToHtml();
  }
}