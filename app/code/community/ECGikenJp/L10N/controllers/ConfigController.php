<?php
/**
 * ECGiken_L10N
 *
 * Copyright (c) 2011, EC-GIKEN Inc.
 * All rights reserved.
 */
class ECGikenJp_L10N_ConfigController extends Mage_Core_Controller_Front_Action {

    public function strictAction() {
        $strict = Mage::getStoreConfig('customer/email_validation/use_strict');
        header('Content-Type: text/html; charset=UTF-8');
        echo $strict;
    }
}
