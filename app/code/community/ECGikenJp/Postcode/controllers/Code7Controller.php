<?php
/**
 * ECGiken_Price
 *
 * Copyright (c) 2011, EC-GIKEN Inc.
 * All rights reserved.
 */
class ECGikenJp_Postcode_Code7Controller extends Mage_Core_Controller_Front_Action {

    public function indexAction() {
        $obj = false;
        foreach (array('Shift_JIS', 'UTF-8', 'EUC-JP') as $encoding) {
            $code7 = mb_convert_encoding($this->getRequest()->getParam('code7'), 'UTF-8', $encoding);
            $id = $this->getRequest()->getParam('id');
            $code7 = preg_replace('/[^0-9]+/u', '', mb_convert_kana($code7, 'n', 'UTF-8'));
            if (strlen($code7) == 7) {
                $obj = Mage::getModel('ecgjppostcode/code7')->loadByCode7($code7);
                break;
            }
        }
        if (!$obj) {
            $obj = new Varien_Object(array('postcode_id' => 0));
        } else {
            $obj->setCode7(substr($obj->getCode7(), 0, 3) . "-" . substr($obj->getCode7(), 3, 4));
            $obj->setTagId($id);
            if (strlen($id) > 8 && substr($id, -8) == "postcode") {
                $obj->setPrefix(substr($id, 0, strlen($id) - 8));
            } else {
                $obj->setPrefix(false);
            }
        }
        $this->getResponse()->setHeader('Content-Type', 'application/json');
        echo Mage::helper('core')->jsonEncode($obj);
    }
}
