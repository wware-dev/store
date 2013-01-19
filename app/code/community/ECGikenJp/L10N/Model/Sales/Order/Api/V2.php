<?php
/**
 * ECGiken_Price
 *
 * Copyright (c) 2011, EC-GIKEN Inc.
 * All rights reserved.
 */

class ECGikenJp_L10N_Model_Sales_Order_Api_V2 extends Mage_Sales_Model_Order_Api_V2 {

    public function items($filters = null) {

        //TODO: add full name logic
        $billingAliasName = 'billing_o_a';
        $shippingAliasName = 'shipping_o_a';

        $collection = Mage::getModel("sales/order")->getCollection()
            ->addAttributeToSelect('*')
            ->addAddressFields()
            ->addExpressionFieldToSelect(
                'billing_lastname', "{{billing_lastname}}", array('billing_lastname'=>"$billingAliasName.lastname")
            )
            ->addExpressionFieldToSelect(
                'billing_firstname', "{{billing_firstname}}", array('billing_firstname'=>"$billingAliasName.firstname")
            )
            ->addExpressionFieldToSelect(
                'shipping_lastname', "{{shipping_lastname}}", array('shipping_lastname'=>"$shippingAliasName.lastname")
            )
            ->addExpressionFieldToSelect(
                'shipping_firstname', "{{shipping_firstname}}", array('shipping_firstname'=>"$shippingAliasName.firstname")
            )
            ->addExpressionFieldToSelect(
                    'billing_name',
                    "CONCAT({{billing_lastname}}, ' ', {{billing_firstname}})",
                    array('billing_lastname'=>"$billingAliasName.lastname", 'billing_firstname'=>"$billingAliasName.firstname")
            )
            ->addExpressionFieldToSelect(
                    'shipping_name',
                    'CONCAT({{shipping_lastname}}, " ", {{shipping_firstname}})',
                    array('shipping_lastname'=>"$shippingAliasName.lastname", 'shipping_firstname'=>"$shippingAliasName.firstname")
            );

        $preparedFilters = array();
        if (isset($filters->filter)) {
            foreach ($filters->filter as $_filter) {
                $preparedFilters[][$_filter->key] = $_filter->value;
            }
        }
        if (isset($filters->complex_filter)) {
            foreach ($filters->complex_filter as $_filter) {
                $_value = $_filter->value;
                if(is_object($_value)) {
                    $preparedFilters[][$_filter->key] = array(
                        $_value->key => $_value->value
                    );
                } elseif(is_array($_value)) {
                    $preparedFilters[][$_filter->key] = array(
                        $_value['key'] => $_value['value']
                    );
                } else {
                    $preparedFilters[][$_filter->key] = $_value;
                }
            }
        }

        if (!empty($preparedFilters)) {
            try {
                foreach ($preparedFilters as $preparedFilter) {
                    foreach ($preparedFilter as $field => $value) {
                        if (isset($this->_attributesMap['order'][$field])) {
                            $field = $this->_attributesMap['order'][$field];
                        }

                        $collection->addFieldToFilter($field, $value);
                    }
                }
            } catch (Mage_Core_Exception $e) {
                $this->_fault('filters_invalid', $e->getMessage());
            }
        }

        $result = array();

        foreach ($collection as $order) {
            $result[] = $this->_getAttributes($order, 'order');
        }

        return $result;
    }
}
