<?php
/**
 * ECGiken_Price
 *
 * Copyright (c) 2011, EC-GIKEN Inc.
 * All rights reserved.
 */

$installer = $this;
$installer->startSetup();

$config = Mage::app()->getConfig();

$config->saveConfig('general/region/display_all', '1');
$config->saveConfig('general/region/state_required', 'JP');

$installer->endSetup();
