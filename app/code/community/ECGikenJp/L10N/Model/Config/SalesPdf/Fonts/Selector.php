<?php
class ECGikenJp_L10N_Model_Config_SalesPdf_Fonts_Selector {

    protected $_options = null;

    private function enumerateTtfFiles($directory) {
        $dir = opendir($directory);
        if ($dir) {
            while (($file = readdir($dir)) !== false) {
                if (strlen($file) > 0 && substr($file, 0, 1) == ".") {
                    continue;
                }
                $full_path = $directory . "/" . $file;
                if (is_dir($full_path)) {
                    $this->enumerateTtfFiles($full_path);
                } else {
                    $ext = pathinfo($full_path, PATHINFO_EXTENSION);
                    if (preg_match('/^[Tt][Tt][Ff]$/', $ext)) {
                        $rel_path = substr($full_path, strlen(Mage::getBaseDir('lib')) + 1);
                        $this->_options[] = array(
                            'value' => $rel_path,
                            'label' => $rel_path
                        );
                    }
                }
            }
            closedir($dir);
        }
    }

    public function toOptionArray() {
        if (is_null($this->_options)) {
            $directory = Mage::getBaseDir('lib');
            $this->_options = array();
            $this->enumerateTtfFiles($directory);
        }

        return $this->_options;
    }
}
