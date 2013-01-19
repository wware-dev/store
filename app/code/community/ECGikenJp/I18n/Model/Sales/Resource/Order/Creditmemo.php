<?php
/**
 * ECGiken_Price
 *
 * Copyright (c) 2011, EC-GIKEN Inc.
 * All rights reserved.
 */

class ECGikenJp_I18n_Model_Sales_Resource_Order_Creditmemo extends Mage_Sales_Model_Resource_Order_Creditmemo {

    protected function _initVirtualGridColumns() {
        parent::_initVirtualGridColumns();

        $fields = array();
        $customerAccount = Mage::getConfig()->getFieldset('customer_account');
        foreach ($customerAccount as $code => $node) {
            if ($node->is('name')) {
                $fields[$code] = $code;
            }
        }

        $source_fields = array();
        foreach (preg_split('/, */u', Mage::getStoreConfig('customer/address_templates/name_sql')) as $source_field) {
            if (array_key_exists($source_field, $fields)) {
                if (!in_array($source_field, $source_fields)) {
                    $source_fields[] = $source_field;
                }
            }
        }

        $adapter = $this->getReadConnection();
        $items = array();
        foreach ($source_fields as $source_field) {
            $items[] = $adapter->getIfNullSql('{{table}}.' . $source_field, $adapter->quote(''));
        }
        $concatAddress = $adapter->getConcatSql($items);

        $this->addVirtualGridColumn(
            'billing_name',
            'sales/order_address',
            array('billing_address_id' => 'entity_id'),
            $concatAddress
        )
        ->addVirtualGridColumn(
            'order_increment_id',
            'sales/order',
            array('order_id' => 'entity_id'),
            'increment_id'
        )
        ->addVirtualGridColumn(
            'order_created_at',
            'sales/order',
            array('order_id' => 'entity_id'),
            'created_at'
        );

        return $this;
    }
}
