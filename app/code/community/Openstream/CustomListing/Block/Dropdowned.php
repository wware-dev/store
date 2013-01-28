<?php

class Openstream_CustomListing_Block_Dropdowned extends Openstream_CustomListing_Block_Abstract
{
// $attribute_value
    protected function _getProductCollection()
    {
	    

        if (($attribute_code = $this->getAttribute()) && ($attribute_value = $this->getValue())) {
            if (is_null($this->_productCollection)) {
                $this->_productCollection = Mage::getResourceModel('reports/product_collection');
                $this->_productCollection->addAttributeToFilter($attribute_code, array('neq' => ''))
                						 ->addAttributeToSelect('*')
                                         ->addStoreFilter();

            }
        }
//        kint::dump($this->_productCollection);exit;
		$this->_customlistingToolbarDisable = true;
        return $this->_productCollection;
    }
}