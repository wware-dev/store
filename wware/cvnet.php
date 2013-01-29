<?php
setlocale( LC_ALL, 'ja_JP' );
ini_set('auto_detect_line_endings', 1);

$brand["10"]["_root_category"] = "ricori";
$brand["20"]["_root_category"] = "Danny&Anne";
$brand["10"]["_product_website"] = "ricori";
$brand["20"]["_product_website"] = "dannyanne";

/*
echo '
<?xml version="1.0" encoding="SJIS" ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ja"><head></head><body>
';
*/

if (is_uploaded_file($_FILES["upfile"]["tmp_name"])) {
	$f = file_get_contents($_FILES["upfile"]["tmp_name"]);
	$f = mb_convert_encoding($f, "UTF8", "SJIS");
	$line = explode("\n", $f);

	//アップされたCVnetのCSVを配列に読み込み。	
	$GLOBAL['cvnet'] = array();
	foreach($line as $k => $v){
		if($k == 0){
			$title = str_getcsv($v);
		}else{
			$data = str_getcsv($v);
			$a = array();
			foreach($data as $kk => $vv){
				$a[$title[$kk]] = $vv;
			}
			array_push($GLOBAL['cvnet'], $a);
		}
	}

	//カラー・サイズ展開の点数を調べるためのテーブルを作る。
	foreach($GLOBAL['cvnet'] as $k => $v){
		$GLOBAL["skuoptions"][$v["商品CD"]]["color"][$v["色"]] = 0;
		$GLOBAL["skuoptions"][$v["商品CD"]]["size"][$v["サイズ"]] = 0;
	}
	
	$cvnet = $GLOBAL['cvnet'];
	$mage = array();
	$conf = array();
	$super_attr_size = array();
	$super_attr_colr = array();
	foreach($cvnet as $k => $v){
		$mageSimpleSKU['sku']						= $v["商品CD"] . substr($v["色"], 0, 2) . substr($v["サイズ"], 0, 1);
		$mageSimpleSKU['sku']						= $mageSimpleSKU['sku'] . calcJanCodeDigit($mageSimpleSKU['sku']);
		$mageSimpleSKU['_attribute_set']			= "属性セット（標準）";
		$mageSimpleSKU['_product_websites']			= $brand[$v["ブランドCD"]]["_product_website"];
		$mageSimpleSKU['_store']					= "";
		$mageSimpleSKU['_type']						= "simple";
		$mageSimpleSKU['_root_category']			= $brand[$v["ブランドCD"]]["_root_category"];
		$mageSimpleSKU['_category']					= str_replace("/", "／", $v["アイテム名"]);
		$mageSimpleSKU['name']						= $v["商品名"] . "/" . substr($v["サイズ"], 2) . "/" . substr($v["色"], 3);
		$mageSimpleSKU['short_description']			= '（未使用）';
		$mageSimpleSKU['description']				= '（未使用）';
		$mageSimpleSKU['price']						= round($v["上代"] * 1.05, -1);
		$mageSimpleSKU['size']						= substr($v["サイズ"], 2);
		$mageSimpleSKU['color']						= substr($v["色"], 3);
		$mageSimpleSKU['url_key']					= $mageSimpleSKU['sku'];
		$mageSimpleSKU['has_options']				= "0";
		$mageSimpleSKU['required_options']			= "0";
		$mageSimpleSKU['status']					= "1";
		$mageSimpleSKU['tax_class_id']				= "4";
		$mageSimpleSKU['visibility']				= "1";
		$mageSimpleSKU['qty']						= "1";
		$mageSimpleSKU['is_in_stock']				= "1";
		$mageSimpleSKU['manage_stock']				= "1";
		$mageSimpleSKU['_super_products_sku']		= NULL;
		$mageSimpleSKU['_super_attribute_code']		= NULL;
		$mageSimpleSKU['_super_attribute_option']	= NULL;

		// _super_attribute_codeをサイズとカラーに分ける処理
		$super_attr_size[$v["商品CD"]][$mageSimpleSKU['size']] = 1;
		$super_attr_colr[$v["商品CD"]][$mageSimpleSKU['color']] = 1;

		$mageConfigSKU1['sku']						= $v["商品CD"];
		$mageConfigSKU1['_attribute_set']			= "属性セット（標準）";
		$mageConfigSKU1['_product_websites']		= $brand[$v["ブランドCD"]]["_product_website"];
		$mageConfigSKU1['_store']					= "";
		$mageConfigSKU1['_type']					= "configurable";
		$mageConfigSKU1['_root_category']			= $brand[$v["ブランドCD"]]["_root_category"];
		$mageConfigSKU1['_category']				= str_replace("/", "／", $v["アイテム名"]);
		$mageConfigSKU1['name']						= $v["商品名"];
		$mageConfigSKU1['short_description']		= $v["商品名"] . "概要";
		$mageConfigSKU1['description']				= $v["商品名"] . "詳細説明文";
		$mageConfigSKU1['price']					= round($v["上代"] * 1.05, -1);
		$mageConfigSKU1['size']						= NULL;
		$mageConfigSKU1['color']					= NULL;
		$mageConfigSKU1['url_key']					= $mageConfigSKU1['sku'];
		$mageConfigSKU1['has_options']				= "1";
		$mageConfigSKU1['required_options']			= "1";
		$mageConfigSKU1['status']					= "1";
		$mageConfigSKU1['tax_class_id']				= "4";
		$mageConfigSKU1['visibility']				= "4";
		$mageConfigSKU1['qty']						= "0";
		$mageConfigSKU1['is_in_stock']				= "1";
		$mageConfigSKU1['manage_stock']				= "1";
		$mageConfigSKU1['_super_products_sku']		= $mageSimpleSKU['sku'];
		$mageConfigSKU1['_super_attribute_code']	= "size";
		$mageConfigSKU1['_super_attribute_option']	= substr($v["サイズ"], 2);

		$mageConfigSKU2['sku']						= $v["商品CD"];
		$mageConfigSKU2['_attribute_set']			= "属性セット（標準）";
		$mageConfigSKU2['_product_websites']		= $brand[$v["ブランドCD"]]["_product_website"];
		$mageConfigSKU2['_store']					= "";
		$mageConfigSKU2['_type']					= "configurable";
		$mageConfigSKU2['_root_category']			= $brand[$v["ブランドCD"]]["_root_category"];
		$mageConfigSKU2['_category']				= str_replace("/", "／", $v["アイテム名"]);
		$mageConfigSKU2['name']						= $v["商品名"];
		$mageConfigSKU2['short_description']		= $v["商品名"] . "概要";
		$mageConfigSKU2['description']				= $v["商品名"] . "詳細説明文";
		$mageConfigSKU2['price']					= round($v["上代"] * 1.05, -1);
		$mageConfigSKU2['size']						= NULL;
		$mageConfigSKU2['color']					= NULL;
		$mageConfigSKU2['url_key']					= $mageConfigSKU2['sku'];
		$mageConfigSKU2['has_options']				= "1";
		$mageConfigSKU2['required_options']			= "1";
		$mageConfigSKU2['status']					= "1";
		$mageConfigSKU2['tax_class_id']				= "4";
		$mageConfigSKU2['visibility']				= "4";
		$mageConfigSKU2['qty']						= "0";
		$mageConfigSKU2['is_in_stock']				= "1";
		$mageConfigSKU2['manage_stock']				= "1";
		$mageConfigSKU2['_super_products_sku']		= $mageSimpleSKU['sku'];
		$mageConfigSKU2['_super_attribute_code']	= "color";
		$mageConfigSKU2['_super_attribute_option']	= substr($v["色"], 3);
		
		if($mageSimpleSKU["name"] != "//"){
			array_push($mage, $mageSimpleSKU);
			
			if($GLOBAL["skuoptions"][$mageConfigSKU1["sku"]]["size"][$v["サイズ"]] == 0){
				array_push($conf, $mageConfigSKU1);
				$GLOBAL["skuoptions"][$mageConfigSKU1["sku"]]["size"][$v["サイズ"]] = 1;
			}
			if($GLOBAL["skuoptions"][$mageConfigSKU2["sku"]]["color"][$v["色"]] == 0){
				array_push($conf, $mageConfigSKU2);
				$GLOBAL["skuoptions"][$mageConfigSKU2["sku"]]["color"][$v["色"]] == 1;
			}
		}
	}
	

	usort($conf, "cmp");
	$lastsku = "";
	foreach($conf as $k => $v){

		if($lastsku != $v["sku"]){
			$lastsku = $v["sku"];
			continue;
		}
		$conf[$k]["sku"] = NULL;
		$conf[$k]['_attribute_set'] = NULL;
		$conf[$k]["_store"] = NULL;			
		$conf[$k]["_type"] = NULL;			
		$conf[$k]["_category"] = NULL;			
		$conf[$k]["_root_category"] = NULL;			
		$conf[$k]["_product_websites"] = NULL;			
		$conf[$k]["color"] = NULL;	
		$conf[$k]['url_key'] = NULL;
		$conf[$k]["description"] = NULL;			
		$conf[$k]["name"] = NULL;	
		$conf[$k]["price"] = NULL;			
		$conf[$k]["short_description"] = NULL;			
		$conf[$k]["has_options"] = NULL;			
		$conf[$k]["required_options"] = NULL;			
		$conf[$k]["size"] = NULL;			
		$conf[$k]["status"] = NULL;			
		$conf[$k]["tax_class_id"] = NULL;			
		$conf[$k]["visibility"] = NULL;			
		$conf[$k]["qty"] = NULL;			
		$conf[$k]["is_in_stock"] = NULL;			
		$conf[$k]["manage_stock"] = NULL;		
	}
	
	
	array_unshift($mage, array_keys($mageConfigSKU1));
	foreach($mage as $k => $v){
		$magecsv .= implode(",", $v) . "\n";
	}
	foreach($conf as $k => $v){
		$magecsv .= implode(",", $v) . "\n";
	}	
	header('Content-Disposition:attachment; filename="'.$_FILES["upfile"]["tmp_name"].'_ec.csv"');
    header('Content-Type:application/octet-stream');
    header('Content-Length:'.strlen($magecsv));
    echo $magecsv;
    exit;


} else {
	$html = '
<form enctype="multipart/form-data" action="cvnet.php" method="POST">
    このファイルをアップロード: <input name="upfile" type="file" />
    <input type="submit" value="ファイルを送信" />
</form>
';
	echo $html;

}


function cmp($a, $b)
{
    return strcmp($a["sku"], $b["sku"]);
}


function calcJanCodeDigit($num) {
	$arr = str_split($num);
	$odd = 0;
	$mod = 0;
	for($i=0;$i<count($arr);$i++){
		if(($i+1) % 2 == 0) {
		//偶数の総和
			$mod += intval($arr[$i]);
		} else {
		//奇数の総和
			$odd += intval($arr[$i]);               
		}
	}
	//偶数の和を3倍+奇数の総和を加算して、下1桁の数字を10から引く
	$cd = 10 - intval(substr((string)($mod * 3) + $odd,-1));
	//10なら1の位は0なので、0を返す。
	return $cd === 10 ? 0 : $cd;
}

/*
$mageSimpleSKU['sku']
$mageSimpleSKU['_store']
$mageSimpleSKU['_attribute_set']
$mageSimpleSKU['_type']
$mageSimpleSKU['_category']
$mageSimpleSKU['_root_category']
$mageSimpleSKU['_product_websites']
$mageSimpleSKU['color']
$mageSimpleSKU['description']
$mageSimpleSKU['image']
$mageSimpleSKU['name']
$mageSimpleSKU['price']
$mageSimpleSKU['short_description']
$mageSimpleSKU['has_options']
$mageSimpleSKU['required_options']
$mageSimpleSKU['size']
$mageSimpleSKU['status']
$mageSimpleSKU['tax_class_id']
$mageSimpleSKU['visibility']
$mageSimpleSKU['qty']
$mageSimpleSKU['use_config_backorders']
$mageSimpleSKU['is_in_stock']
$mageSimpleSKU['notify_stock_qty']
$mageSimpleSKU['use_config_notify_stock_qty']
$mageSimpleSKU['manage_stock']
$mageSimpleSKU['use_config_manage_stock']
$mageSimpleSKU['_super_products_sku']
$mageSimpleSKU['_super_attribute_code']
$mageSimpleSKU['_super_attribute_option']
$mageSimpleSKU['_super_attribute_price_corr']
*/

?>