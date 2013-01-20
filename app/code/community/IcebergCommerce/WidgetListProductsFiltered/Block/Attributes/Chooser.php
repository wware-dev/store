<?php
/**
 * Attributes chooser for Product List widget
 * Allows you to filter a list by product attributes
 * 
 * Iceberg Commerce
 * @author     IcebergCommerce
 * @package    IcebergCommerce_WidgetListProductsFiltered
 * @copyright  Copyright (c) 2010 Iceberg Commerce
 */
class IcebergCommerce_WidgetListProductsFiltered_Block_Attributes_Chooser extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * Prepare chooser element HTML
     *
     * @param Varien_Data_Form_Element_Abstract $element Form Element
     * @return Varien_Data_Form_Element_Abstract
     */
    public function prepareElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
    	// Get All Attributes
		$attributes = Mage::helper('widgetlistproductsfiltered/data')->getAttributes();
		
		// Add Attributes to Form
        $form = new Varien_Data_Form();//array('id' => 'edit_form', 'action' => $this->getData('action'), 'method' => 'post'));
        $fieldset = $form->addFieldset('chooser_fieldset', array('legend'=>Mage::helper('catalog')->__('Optional Product Filters')));

        foreach ($attributes as $key=>$attribute)
        {
        	// If Attribute name begins with "option", it will conflict withe widget. skip it.
			if (substr($attribute->getAttributeCode(),0,6)=='option')
			{
				$attributes->removeItemByKey($key);
				continue;
			}
			
			if (stristr($attribute->getFrontendInputRenderer(),'_config'))
			{
				$attributes->removeItemByKey($key);
				continue;
			}
				
        }
        
        $this->_setFieldset($attributes, $fieldset);
        
        // Pass widget values to this form
        $form->setValues($this->getWidgetValues());
        
        // Assign form html to widget
        $this->setForm($form);
        $element->setData('after_element_html', $this->getFormHtml() ."<style>#chooser_fieldset .form-list td.value select{display:inline}</style>");
        
        return $element;
    }
    
    /**
     * Override parent definition of this method
     * We needed to have a different name for each html field
     */
    protected function _setFieldset($attributes, $fieldset, $exclude=array())
    {
    	$widgetValues = $this->getWidgetValues();
    	
        $this->_addElementTypes($fieldset);
        foreach ($attributes as $attribute) {
            /* @var $attribute Mage_Eav_Model_Entity_Attribute */
            if (!$attribute || ($attribute->hasIsVisible() && !$attribute->getIsVisible())) {
                continue;
            }
            if ( ($inputType = $attribute->getFrontend()->getInputType())
                 && !in_array($attribute->getAttributeCode(), $exclude)
                 && ('media_image' != $inputType)
                 ) {

                $fieldType      = $inputType;
                $rendererClass  = $attribute->getFrontend()->getInputRendererClass();
                if (!empty($rendererClass)) {
                    $fieldType  = $inputType . '_' . $attribute->getAttributeCode();
                    $fieldset->addType($fieldType, $rendererClass);
                }

                $element = $fieldset->addField($attribute->getAttributeCode(), $fieldType,
                    array(
                        'name'      => 'parameters['.$attribute->getAttributeCode().']',
                        'label'     => $attribute->getFrontend()->getLabel(),
                        'class'     => $attribute->getFrontend()->getClass(),
                        'required'  => $attribute->getIsRequired(),
                        'note'      => $attribute->getNote(),
                        'disabled'  => !isset($widgetValues[$attribute->getAttributeCode()]),
                    )
                )->setEntityAttribute($attribute);

                $extraHtml = '&nbsp;&nbsp; <input '.(isset($widgetValues[$attribute->getAttributeCode()]) ? 'checked="checked"' : '').' type="checkbox" onclick="$(\''.$attribute->getAttributeCode().'\').disabled = !this.checked;" /> Use ';
                $element->setAfterElementHtml($this->_getAdditionalElementHtml($element).$extraHtml);

                if ($inputType == 'select' || $inputType == 'multiselect') {
                    $element->setValues($attribute->getSource()->getAllOptions(true, true));
                } elseif ($inputType == 'date') {
                    $element->setImage($this->getSkinUrl('images/grid-cal.gif'));
                    $element->setFormat(Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT));
                }
            }
        }
    }
    
    private function getWidgetValues()
    {
    	// Widget values come from the request object
        $widgetValues = array();
		
		if (Mage::registry('current_widget_instance'))
        {
        	$widgetValues = Mage::registry('current_widget_instance')->getWidgetParameters();
        }
        else 
        {
        	$widgetData = Mage::helper('core')->jsonDecode($this->getRequest()->getParam('widget'));
			if (is_array($widgetData)) 
			{
				$widgetValues = isset($widgetData['values']) ? $widgetData['values'] : array();
			}
        }
        
		return $widgetValues;
    }
    
	/**
	 * Add renderer for boolean attrbutes
	 */
	protected function _getAdditionalElementTypes()
	{
		$result = array(
			'boolean'  => Mage::getConfig()->getBlockClassName('adminhtml/catalog_product_helper_form_boolean'),
		);
		
		$response = new Varien_Object();
		$response->setTypes(array());
		Mage::dispatchEvent('adminhtml_catalog_product_edit_element_types', array('response'=>$response));
		
		foreach ($response->getTypes() as $typeName=>$typeClass) 
		{
			$result[$typeName] = $typeClass;
		}
		
		return $result;
	}

}
