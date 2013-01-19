<?php
$installer = $this;
$installer->startSetup();

$KEN_ALL = Mage::getBaseDir('var') . DIRECTORY_SEPARATOR . "ken_all.csv";
$JIGYOSYO = Mage::getBaseDir('var') . DIRECTORY_SEPARATOR . "jigyosyo.csv";

@unlink($KEN_ALL);
@unlink($JIGYOSYO);

$installer->endSetup();
