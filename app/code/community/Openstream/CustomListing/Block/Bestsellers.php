<?php

class Openstream_CustomListing_Block_Bestsellers extends Openstream_CustomListing_Block_Abstract
{
    protected function _getProductCollection()
    {
        if (is_null($this->_productCollection)) {
            $this->_productCollection = Mage::getResourceModel('reports/product_collection');
            $this->_productCollection->addOrderedQty();
            $this->_productCollection->addAttributeToSelect('*')
                                     ->addStoreFilter()
                                     ->setOrder('ordered_qty', 'desc');
        }
        return $this->_productCollection;
    }
}