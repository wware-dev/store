<?php
/**
 * ECGiken_Price
 *
 * Copyright (c) 2011, EC-GIKEN Inc.
 * All rights reserved.
 */

class ECGikenJp_I18n_Model_Customer_Address extends Mage_Customer_Model_Address {

    public function getName() {
        $formatType = Mage::getSingleton('customer/address_config')->getFormatByCode('name_template');
        return $formatType->getRenderer()->render($this);
    }
}
