<?php
/**
 * ECGiken_Price
 *
 * Copyright (c) 2011, EC-GIKEN Inc.
 * All rights reserved.
 */
class ECGikenJp_Postcode_Model_Code7 extends Mage_Core_Model_Abstract {

    protected function _construct() {
        $this->_init('ecgjppostcode/code7');
    }

    public function loadByCode7($code7) {
        $collection = $this
            ->getCollection()
            ->addFieldToFilter(
                'code7',
                array(
                    'eq' => $code7
                )
            );
        
        if ($collection) {
            $obj = $collection->getFirstItem();
            if ($obj && $obj->getId() > 0) {
                return $obj;
            }
        }
        return false;
    }
}
