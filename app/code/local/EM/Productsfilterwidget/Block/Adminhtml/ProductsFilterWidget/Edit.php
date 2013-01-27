<?php

class EM_ProductsFilterWidget_Block_Adminhtml_ProductsFilterWidget_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
                 
        $this->_objectId = 'id';
        $this->_blockGroup = 'productsfilterwidget';
        $this->_controller = 'adminhtml_productsfilterwidget';
        
        $this->_updateButton('save', 'label', Mage::helper('productsfilterwidget')->__('Save Item'));
        $this->_updateButton('delete', 'label', Mage::helper('productsfilterwidget')->__('Delete Item'));
		
        $this->_addButton('saveandcontinue', array(
            'label'     => Mage::helper('adminhtml')->__('Save And Continue Edit'),
            'onclick'   => 'saveAndContinueEdit()',
            'class'     => 'save',
        ), -100);

        $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('productsfilterwidget_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'productsfilterwidget_content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'productsfilterwidget_content');
                }
            }

            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
    }

    public function getHeaderText()
    {
        if( Mage::registry('productsfilterwidget_data') && Mage::registry('productsfilterwidget_data')->getId() ) {
            return Mage::helper('productsfilterwidget')->__("Edit Item '%s'", $this->htmlEscape(Mage::registry('productsfilterwidget_data')->getTitle()));
        } else {
            return Mage::helper('productsfilterwidget')->__('Add Item');
        }
    }
}