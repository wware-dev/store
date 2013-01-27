<?php

class EM_ProductsFilterWidget_Block_Adminhtml_ProductsFilterWidget_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
  protected function _prepareForm()
  {
      $form = new Varien_Data_Form();
      $this->setForm($form);
      $fieldset = $form->addFieldset('productsfilterwidget_form', array('legend'=>Mage::helper('productsfilterwidget')->__('Item information')));
     
      $fieldset->addField('title', 'text', array(
          'label'     => Mage::helper('productsfilterwidget')->__('Title'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'title',
      ));

      $fieldset->addField('filename', 'file', array(
          'label'     => Mage::helper('productsfilterwidget')->__('File'),
          'required'  => false,
          'name'      => 'filename',
	  ));
		
      $fieldset->addField('status', 'select', array(
          'label'     => Mage::helper('productsfilterwidget')->__('Status'),
          'name'      => 'status',
          'values'    => array(
              array(
                  'value'     => 1,
                  'label'     => Mage::helper('productsfilterwidget')->__('Enabled'),
              ),

              array(
                  'value'     => 2,
                  'label'     => Mage::helper('productsfilterwidget')->__('Disabled'),
              ),
          ),
      ));
     
      $fieldset->addField('content', 'editor', array(
          'name'      => 'content',
          'label'     => Mage::helper('productsfilterwidget')->__('Content'),
          'title'     => Mage::helper('productsfilterwidget')->__('Content'),
          'style'     => 'width:700px; height:500px;',
          'wysiwyg'   => false,
          'required'  => true,
      ));
     
      if ( Mage::getSingleton('adminhtml/session')->getProductsFilterWidgetData() )
      {
          $form->setValues(Mage::getSingleton('adminhtml/session')->getProductsFilterWidgetData());
          Mage::getSingleton('adminhtml/session')->setProductsFilterWidgetData(null);
      } elseif ( Mage::registry('productsfilterwidget_data') ) {
          $form->setValues(Mage::registry('productsfilterwidget_data')->getData());
      }
      return parent::_prepareForm();
  }
}