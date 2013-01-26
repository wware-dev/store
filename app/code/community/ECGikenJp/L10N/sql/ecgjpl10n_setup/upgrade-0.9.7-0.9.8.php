<?php
/**
 * ECGiken_Price
 *
 * Copyright (c) 2011, EC-GIKEN Inc.
 * All rights reserved.
 */

$installer = $this;
$installer->startSetup();

$aov = $installer->getTable('eav/attribute_option_value');
$installer->run("
    UPDATE $aov SET value='男性' WHERE store_id=0 AND value='Male';
    UPDATE $aov SET value='女性' WHERE store_id=0 AND value='Female';
");

$installer->endSetup();
