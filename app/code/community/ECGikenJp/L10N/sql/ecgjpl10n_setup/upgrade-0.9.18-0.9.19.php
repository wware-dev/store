<?php
/**
 * ECGiken_Price
 *
 * Copyright (c) 2011, EC-GIKEN Inc.
 * All rights reserved.
 */

$installer = $this;
$installer->startSetup();

$src = realpath(dirname(__FILE__) . "/../../../../../../../js/ecgiken/ja/validation.js.src");
$dst = preg_replace('/\.src$/', '', $src);

$install_directory = substr(Mage::getBaseDir(), strlen($_SERVER['DOCUMENT_ROOT'])) . "/";
file_put_contents($dst, preg_replace('/%INSTALL_DIR%/', $install_directory, file_get_contents($src)));

$installer->endSetup();
