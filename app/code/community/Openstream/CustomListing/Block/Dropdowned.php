<?php

class Openstream_CustomListing_Block_Dropdowned extends Openstream_CustomListing_Block_Abstract
{
// $attribute_value
    protected function _getProductCollection()
    {
        if (($attribute_code = $this->getAttribute()) && ($attribute_value = $this->getValue())) {
            if (is_null($this->_productCollection)) {
            	error_log($attribute_code);
                $this->_productCollection = Mage::getResourceModel('reports/product_collection');
                $this->_productCollection->addAttributeToFilter($attribute_code, array('neq' => ''))
                						 ->addAttributeToSort($attribute_code, "desc")
                                         ->addAttributeToSelect('*')
                                         ->addStoreFilter();
            }
        }
        return $this->_productCollection;
    }
}