<?php
class ECGiken_Gmo_Model_System_Config_Backend_Gmomodule extends Mage_Adminhtml_Model_System_Config_Backend_File
{
    protected function extractZipFile($fileName, $extractPath) {
        if( file_exists("/usr/bin/unzip") ) {
            exec("/usr/bin/unzip ".$fileName." -d ".$extractPath);
        }else{
            throw new Exception("unzip command not found");
        }
        return true;
    }

    protected function tmpFileDelete($tmpDir) {
        if( file_exists($tmpDir) ) {
            exec("rm -Rf " . $tmpDir);
        }
    }

    protected function zipFileMaint() {
        $uploadDir = $this->_getUploadDir()."/";
        $value = $this->getValue();
        if (is_array($value)) {
            if(!empty($value['delete'])) {
                if( file_exists($uploadDir.$value['value']) ) {
                    exec("rm ".$uploadDir.$value['value']);
                    $this->setValue('');
                }
            }
            return true;
        }
        exec("rm ".$uploadDir."*");
        return false;
    }

    protected function editIndex() {
        $baseDir = Mage::getBaseDir('base')."/";
        if($fp = fopen($baseDir.'index.php', "r")) {
            $body = fread($fp, filesize($baseDir.'index.php'));
            fclose($fp);
            $date = Mage::getModel('core/date')->date("YmdHis");
            exec("cp -f ".$baseDir."index.php ".$baseDir."index_php.backup.".$date);
        }else{
            throw new Exception("index.php file open error");
        }
        $body = mb_ereg_replace("error_reporting\(.*?\)", "error_reporting((E_ALL ^ E_STRICT) && (E_ALL ^ E_DEPRECATED))", $body);
        if($fp = fopen($baseDir.'index.php', "w")) {
            fwrite($fp, $body);
            fclose($fp);
        }else{
            throw new Exception("index.php file open error");
        }
    }

    protected function _beforeSave()
    {
        if( $this->zipFileMaint() ) {
            return $this;
        }

        parent::_beforeSave();

        $baseDir = Mage::getBaseDir('base')."/";
        $uploadDir = $this->_getUploadDir()."/";
        $extractDir = "tmp/";
        $zipFileName = $this->getValue();
        Mage::log($baseDir.$uploadDir.$zipFileName);
        Mage::log($baseDir.$uploadDir.$extractDir);

        try {
//            if(file_exists($baseDir."lib/com/gmo_pg") && file_exists($baseDir."lib/conf/connector.properties")) {
//                throw new Exception("GMO module files already exists");
//            }
            if(!file_exists($baseDir.$uploadDir.$extractDir)) {
                if(!mkdir($baseDir.$uploadDir.$extractDir)) {
                    throw new Exception("Don't make directory ".$baseDir.$uploadDir.$extractDir);
                }
            }
            $this->extractZipFile($baseDir.$uploadDir.$zipFileName, $baseDir.$uploadDir.$extractDir);
            $srcFullPath = "";
            if(file_exists($baseDir.$uploadDir.$extractDir."PHP/gpay_client/src")) {
                Mage::log($baseDir.$uploadDir.$extractDir."PHP/gpay_client/src found");
                exec("cp -Rf ".$baseDir.$uploadDir.$extractDir."PHP/gpay_client/src/* ".$baseDir."lib/.");
                $this->tmpFileDelete($baseDir.$uploadDir.$extractDir);
                $this->editIndex();
            }else{
                throw new Exception("Directory not found '".$baseDir.$uploadDir.$extractDir."PHP/gpay_client/src'");
            }
        } catch( Exception $e) {
            $this->tmpFileDelete($baseDir.$uploadDir.$extractDir);
            Mage::throwException($e->getMessage());
        }

        return $this;
    }

    protected function _getAllowedExtensions() {
        return array("zip");
    }
}
