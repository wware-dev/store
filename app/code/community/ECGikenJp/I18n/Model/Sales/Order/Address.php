<?php
/**
 * ECGiken_Price
 *
 * Copyright (c) 2011, EC-GIKEN Inc.
 * All rights reserved.
 */

class ECGikenJp_I18n_Model_Sales_Order_Address extends Mage_Sales_Model_Order_Address {

    public function getName() {
        $formatType = Mage::getSingleton('customer/address_config')->getFormatByCode('name_template');
        $address = Mage::getModel('customer/address');
        foreach ($this->getData() as $key => $val) {
            $address->setData($key, $val);
        }
        return $formatType->getRenderer()->render($address);
    }
}
