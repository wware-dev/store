<?php
/**
 * ECGiken_Price
 *
 * Copyright (c) 2011, EC-GIKEN Inc.
 * All rights reserved.
 */
// encode convert
function private_encode_convert($src, $dst) {
    $fpr = fopen($src, "r");
    if ($fpr) {
        $fpw = fopen($dst, "w");
        if ($fpw) {
            while ($str = fgets($fpr)) {
                fputs($fpw, iconv('cp932', 'UTF-8', $str));
            }
            fclose($fpw);
        }
        fclose($fpr);
    }
}

$installer = $this;
$installer->startSetup();

$site = 'www.ec-giken.com';
$directory = 'files/Postcode';
$files = array(
    'a7dc791691b2b038546441bbbbd902cf' => 'jigyosyo.csv',
    '60da43118bd74fe8470dba8fcfe0b3ad' => 'ken_all.csv'
);

foreach ($files as $md5 => $file) {
    $source = "http://" . $site . "/" . $directory . "/" . $file;
    $target = dirname(__FILE__) . "/" . $file;
    file_put_contents($target, file_get_contents($source));
}

$KEN_ALL_ORG = dirname(__FILE__) . DIRECTORY_SEPARATOR . "ken_all.csv";
$JIGYOSYO_ORG = dirname(__FILE__) . DIRECTORY_SEPARATOR . "jigyosyo.csv";
$KEN_ALL = Mage::getBaseDir('var') . DIRECTORY_SEPARATOR . "ken_all.csv";
$JIGYOSYO = Mage::getBaseDir('var') . DIRECTORY_SEPARATOR . "jigyosyo.csv";

private_encode_convert($KEN_ALL_ORG, $KEN_ALL);
private_encode_convert($JIGYOSYO_ORG, $JIGYOSYO);

if (!file_exists($KEN_ALL)) {
    Mage::throwException('Can not install ECGikenJp Post Code Module(ken_all.csv)');
}
if (!file_exists($JIGYOSYO)) {
    Mage::throwException('Can not install ECGikenJp Post Code Module(jigyosho.csv)');
}

$installer->run("
    DROP TABLE IF EXISTS `{$installer->getTable('ecgjppostcode/code7')}`;
    CREATE TABLE `{$installer->getTable('ecgjppostcode/code7')}` (
      `postcode_id` int(11) NOT NULL auto_increment,
      `x040x` char(5) DEFAULT NULL,
      `code5` char(5) DEFAULT NULL,
      `code7` char(7) DEFAULT NULL,
      `region_kana` varchar(64) DEFAULT NULL,
      `city_kana` varchar(128) DEFAULT NULL,
      `street_kana` varchar(128) DEFAULT NULL,
      `region_kanji` varchar(64) DEFAULT NULL,
      `city_kanji` varchar(128) DEFAULT NULL,
      `street_kanji` varchar(128) DEFAULT NULL,
      `region_id` mediumint(8) DEFAULT NULL,
      `flag1` int(1) DEFAULT '0',
      `flag2` int(1) DEFAULT '0',
      `flag3` int(1) DEFAULT '0',
      `flag4` int(1) DEFAULT '0',
      `flag5` int(1) DEFAULT '0',
      `flag6` int(1) DEFAULT '0',
      `office_kana` varchar(128) DEFAULT NULL,
      `office_kanji` varchar(128) DEFAULT NULL,
      `street2_kanji` varchar(128) DEFAULT NULL,
      `postoffice` varchar(128) DEFAULT NULL,
      `flag11` int(1) DEFAULT '0',
      `flag12` int(1) DEFAULT '0',
      `flag13` int(1) DEFAULT '0',
      PRIMARY KEY  (`postcode_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");

$installer->run("SET autocommit=0;");
$installer->run("START TRANSACTION;");
$installer->run("LOCK TABLES `{$installer->getTable('ecgjppostcode/code7')}` WRITE;");
$installer->run("ALTER TABLE `{$installer->getTable('ecgjppostcode/code7')}` DISABLE KEYS;");

$lines = 0;
$amount = 4000;
$sql = "INSERT INTO `{$installer->getTable('ecgjppostcode/code7')}` (x040x, code5, code7, region_kana, city_kana, street_kana, region_kanji, city_kanji, street_kanji, flag1, flag2, flag3, flag4, flag5, flag6) VALUES";
$fp = fopen($KEN_ALL, "r");
if ($fp) {
    while ($val = fgetcsv($fp)) {
        if (is_array($val) && count($val) == 15) {
            if ($lines > $amount) {
                $installer->run($sql);
                $lines = 0;
                $sql = "INSERT INTO `{$installer->getTable('ecgjppostcode/code7')}` (x040x, code5, code7, region_kana, city_kana, street_kana, region_kanji, city_kanji, street_kanji, flag1, flag2, flag3, flag4, flag5, flag6) VALUES";
            }
            if ($lines != 0) {
                $sql .= ",";
            }
            $sql .= "('{$val[0]}', '{$val[1]}', '{$val[2]}', '" . mb_convert_kana($val[3], "KV", "UTF-8") . "', '" . mb_convert_kana($val[4], "KV", "UTF-8") . "', '" . mb_convert_kana($val[5], "KV", "UTF-8") . "', '{$val[6]}', '{$val[7]}', '{$val[8]}', '{$val[9]}', '{$val[10]}', '{$val[11]}', '{$val[12]}', '{$val[13]}', '{$val[14]}')";
            $lines++;
        }
    }
    if ($lines > 0) {
        $installer->run($sql);
    }
    fclose($fp);
}

$lines = 0;
$sql = "INSERT INTO `{$installer->getTable('ecgjppostcode/code7')}` (x040x, code5, code7, region_kanji, city_kanji, street_kanji, street2_kanji, office_kana, office_kanji, postoffice, flag11, flag12, flag13) VALUES";
$fp = fopen($JIGYOSYO, "r");
if ($fp) {
    while ($val = fgetcsv($fp)) {
        if (is_array($val) && count($val) == 13) {
            if ($lines > $amount) {
                $installer->run($sql);
                $lines = 0;
                $sql = "INSERT INTO `{$installer->getTable('ecgjppostcode/code7')}` (x040x, code5, code7, region_kanji, city_kanji, street_kanji, street2_kanji, office_kana, office_kanji, postoffice, flag11, flag12, flag13) VALUES";
            }
            if ($lines != 0) {
                $sql .= ",";
            }
            $sql .= "('{$val[0]}', '{$val[8]}', '{$val[7]}', '{$val[3]}', '{$val[4]}', '{$val[5]}', '{$val[6]}', '" . mb_convert_kana($val[1], "KV", "UTF-8") . "', '{$val[2]}', '{$val[9]}', '{$val[10]}', '{$val[11]}', '{$val[12]}')";
            $lines++;
        }
    }
    if ($lines > 0) {
        $installer->run($sql);
    }
    fclose($fp);
}

$installer->run("ALTER TABLE `{$installer->getTable('ecgjppostcode/code7')}` ENABLE KEYS;");
$installer->run("UNLOCK TABLES;");
$installer->run("COMMIT;");
$installer->run("SET autocommit=1;");

$installer->run("
    CREATE INDEX IDX_REGION_DEFAULT_NAME on `{$installer->getTable('directory/country_region')}`(default_name);
    CREATE INDEX IDX_REGION_KANJI on `{$installer->getTable('ecgjppostcode/code7')}`(region_kanji);
    UPDATE `{$installer->getTable('ecgjppostcode/code7')}`
        SET region_id=(SELECT region_id FROM `{$installer->getTable('directory/country_region')}` WHERE default_name=`{$installer->getTable('ecgjppostcode/code7')}`.region_kanji);
    CREATE INDEX IDX_CODE7 on `{$installer->getTable('ecgjppostcode/code7')}`(code7);
    DROP INDEX IDX_REGION_DEFAULT_NAME ON `{$installer->getTable('directory/country_region')}`;
    DROP INDEX IDX_REGION_KANJI ON `{$installer->getTable('ecgjppostcode/code7')}`;
");

$installer->endSetup();
