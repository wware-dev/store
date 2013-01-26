<?php
class ECGiken_Gmo_Helper_Data extends Mage_Core_Helper_Abstract {

    public function getPriceText($amount=0) {
        $amount = preg_replace('/\.0+$/', '', $amount);
        $ecgPrice = Mage::getModel('directory/currency');
        if($ecgPrice instanceof ECGiken_Price_Model_Directory_Currency) {
            if(method_exists($ecgPrice, 'formatSimpleTxt')) {
                return $ecgPrice->formatSimpleTxt($amount);
            }
        }
        if(preg_match('/\./', $amount) === 1) {
            Mage::throwException($this->__('[ECGiken_Gmo Error] Amount format error.  Please refer to "app/code/community/ECGiken/Gmo/readme.txt" file.    金額フォーマットエラー。"app/code/community/ECGiken/Gmo/readme.txt" ファイルをご参照ください。'));
        }
        return $amount;
    }

    public function getCommonConfigData($field) {
        $path = 'ecggmo/ecggmo_common/'.$field;
        return Mage::getStoreConfig($path);
    }

}
