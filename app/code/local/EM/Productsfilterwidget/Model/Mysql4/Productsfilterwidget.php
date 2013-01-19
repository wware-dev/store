<?php

class EM_Productsfilterwidget_Model_Mysql4_Productsfilterwidget extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        // Note that the productsfilterwidget_id refers to the key field in your database table.
        $this->_init('productsfilterwidget/productsfilterwidget', 'productsfilterwidget_id');
    }
}