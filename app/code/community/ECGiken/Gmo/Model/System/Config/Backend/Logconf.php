<?php
class ECGiken_Gmo_Model_System_Config_Backend_Logconf extends Mage_Adminhtml_Model_System_Config_Backend_File {
    protected function _beforeSave() {
        $log_header = "[ECGiken_Gmo_Model_System_Config_Backend_Logconf] ";
        parent::_beforeSave();
        $baseDir = Mage::getBaseDir('base')."/";
        $uploadDir = $this->_getUploadDir()."/";
        $uploadFileName = $this->getValue();
        $moduleConfDir = Mage::getStoreConfig('ecggmo/ecggmo_common/module_conf_dir')."/";
        Mage::log($log_header.$baseDir.$uploadDir.$uploadFileName);
        Mage::log($log_header.$baseDir.$moduleConfDir);
        if(!empty($uploadFileName) && !is_array($uploadFileName)) {
            exec("cp ".$baseDir.$uploadDir.$uploadFileName." ".$baseDir.$moduleConfDir."log.properties");
        }else{
            Mage::log($log_header."Processing was not performed.");
        }
        return $this;
    }
}
