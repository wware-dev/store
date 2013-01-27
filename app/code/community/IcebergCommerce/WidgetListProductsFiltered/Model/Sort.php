<?php
/**
 * Iceberg Commerce
 * @author     IcebergCommerce
 * @package    IcebergCommerce_WidgetListProductsFiltered
 * @copyright  Copyright (c) 2010 Iceberg Commerce
 */
class IcebergCommerce_WidgetListProductsFiltered_Model_Sort
{
	public function toOptionArray()
	{
		$ret = Mage::getSingleton('catalog/category')
            ->getAvailableSortByOptions();
            
        if (!$ret)
        {
        	return array();
        }
        	
        return $ret;
	}
}