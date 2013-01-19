<?php
/**
 * ECGiken_Price
 *
 * Copyright (c) 2011, EC-GIKEN Inc.
 * All rights reserved.
 */
class ECGikenJp_I18n_Model_Design_Package extends Mage_Core_Model_Design_Package {

    public function getFilename($file, array $params) {
        $this->updateParamDefaults($params);
        $themes = array();
        $themes[] = array();
        if ($this->getFallbackTheme()) {
            $themes[] = array(
                '_theme' => $this->getFallbackTheme() . "/" . Mage::app()->getLocale()->getLocaleCode()
            );
            $themes[] = array(
                '_theme' => $this->getFallbackTheme()
            );
        }
        $themes[] = array(
            '_theme' => self::DEFAULT_THEME . "/" . Mage::app()->getLocale()->getLocaleCode()
        );
        $themes[] = array(
            '_theme' => self::DEFAULT_THEME
        );
        $themes[] = array(
            '_package' => self::BASE_PACKAGE,
            '_theme' => self::DEFAULT_THEME . "/" . Mage::app()->getLocale()->getLocaleCode()
        );
        $themes[] = array(
            '_package' => self::BASE_PACKAGE,
            '_theme' => self::DEFAULT_THEME
        );
        $result = $this->_fallback(
            $file,
            $params,
            $themes
        );
        return $result;
    }
}
