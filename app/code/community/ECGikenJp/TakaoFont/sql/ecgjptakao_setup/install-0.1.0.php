<?php
/**
 * ECGiken_TakaoFont
 *
 * Copyright (c) 2011, EC-GIKEN Inc.
 * All rights reserved.
 */

$installer = $this;
$installer->startSetup();

$site = 'www.ec-giken.com';
$directory = 'files/TakaoFont/003.02.01';
$files = array(
    '91e2cab9dec62a9a5ff3cb84095370dc' => 'ChangeLog',
    'cf52fce188a46df5fac4e46bccd35190' => 'IPA_Font_License_Agreement_v1.0.txt',
    'ef1f9991e37eae58948b15f1f204f30e' => 'README',
    '9a98a7f92786760eccecd2a7fc8e5f30' => 'README.ja',
    '3cb10c8b4d365a84129a2624ee57da74' => 'TakaoExGothic.ttf',
    '9e0e72d856ad4d3df9e11fca169b2901' => 'TakaoExMincho.ttf',
    'c16fa7583bc2de38b6fb4e5b204e5fe6' => 'TakaoGothic.ttf',
    '9587d46265adf7bf998c2dd4c4b94769' => 'TakaoMincho.ttf',
    '321d784f6c0a279b594d0124145344ff' => 'TakaoPGothic.ttf',
    '3c221fb15708dfad833502626e2c6d28' => 'TakaoPMincho.ttf'
);

$lib_dir = Mage::getBaseDir('lib');
$takao_dir = $lib_dir . "/TakaoFont";
@mkdir($takao_dir, 0777, true);
foreach ($files as $md5 => $file) {
    $source = "http://" . $site . "/" . $directory . "/" . $file;
    $target = $takao_dir . "/" . $file;
    file_put_contents($target, file_get_contents($source));
}

$installer->endSetup();
