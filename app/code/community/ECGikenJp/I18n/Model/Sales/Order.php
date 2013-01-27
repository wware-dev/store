<?php
/**
 * ECGiken_Price
 *
 * Copyright (c) 2011, EC-GIKEN Inc.
 * All rights reserved.
 */

class ECGikenJp_I18n_Model_Sales_Order extends Mage_Sales_Model_Order {

    public function getCustomerName() {
        if ($this->getCustomerFirstname()) {
            $formatType = Mage::getSingleton('customer/address_config')->getFormatByCode('name_template');
            $address = Mage::getModel('customer/address');
            foreach ($this->getData() as $key => $val) {
                if (preg_match('/^customer_/', $key)) {
                    $address->setData(preg_replace('/^customer_/', '', $key), $val);
                }
            }
            return $formatType->getRenderer()->render($address);
        } else {
            $customerName = Mage::helper('sales')->__('Guest');
        }
        return $customerName;
    }
}
