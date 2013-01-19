<?php


$mediadir = "/var/www/html/store/media/import";

$dh1 = opendir($mediadir);

while (($file1 = readdir($dh1)) !== false) {
	if($file1 == "."){continue;}
	if($file1 == ".."){continue;}
	if($file1 == "cache"){continue;}
	
	if(is_dir($mediadir . "/" . $file1)){
		$dh2 = opendir($mediadir . "/" . $file1);
		$imglist = array();
		while (($file2 = readdir($dh2)) !== false) {
			if($file2 == "."){continue;}
			if($file2 == ".."){continue;}
			if(is_dir($mediadir . "/" . $file1 . "/" . $file2)){continue;}
			$imglist[] = "/" . $file1 . "/" . $file2;
		}
		natsort($imglist);
		$skuimg[$file1][] = $imglist;
	}
}
closedir($dh1);
closedir($dh2);


foreach($skuimg as $k1 => $v1){
	$mediacount = 1;
	foreach($v1[0] as $k2 => $v2){
		$imglist = array();
		if(!strpos($v2, "_")){
			$simplesku = substr($v2, -16, 12);
			$simplesku = $simplesku . calcJanCodeDigit($simplesku);
			$imglist["sku"] = $simplesku;
//			$imglist["_attribute_set"] = "属性セット（標準）";
//			$imglist["_product_websites"] = "dannyanne";
			$imglist["_type"] = "simple";
//			$imglist["has_options"] = "1";
			$imglist["image"] = $v2;
//			$imglist["image_label"] = "";
//			$imglist["required_options"] = "1";
			$imglist["small_image"] = $v2;
//			$imglist["small_image_label"] = "";
			$imglist["thumbnail"] = $v2;
//			$imglist["thumbnail_label"] = "";
			$imglist["_media_attribute_id"] = "88";
			$imglist["_media_image"] = $v2;
			$imglist["_media_lable"] = "";
			$imglist["_media_position"] = "1";
			$imglist["_media_is_disabled"] = "0";

			$title = array_keys($imglist);
			$imglistcsvtxt = implode(",", $title) . "\n";
			
			$imglistcsv[] = $imglist;
		}
	}
}
				

foreach($skuimg as $k1 => $v1){
	$mediacount = 1;
	foreach($v1[0] as $k2 => $v2){
		$imglist = array();
		if(strpos($v2, "_")){
			if($mediacount == 1){
				$imglist["sku"] = $k1;
//				$imglist["_attribute_set"] = "属性セット（標準）";
//				$imglist["_product_websites"] = "dannyanne";
				$imglist["_type"] = "configurable";
//				$imglist["has_options"] = "1";
				$imglist["image"] = $v2;
//				$imglist["image_label"] = "";
//				$imglist["required_options"] = "1";
				$imglist["small_image"] = $v2;
//				$imglist["small_image_label"] = "";
				$imglist["thumbnail"] = $v2;
//				$imglist["thumbnail_label"] = "";
				$imglist["_media_attribute_id"] = "88";
				$imglist["_media_image"] = $v2;
				$imglist["_media_lable"] = "";
				$imglist["_media_position"] = $mediacount;
				$imglist["_media_is_disabled"] = "0";
				$mediacount++;

			}else{
				$imglist["sku"] = NULL;
//				$imglist["_attribute_set"] = NULL;
//				$imglist["_product_websites"] = NULL;
				$imglist["_type"] = NULL;
//				$imglist["has_options"] = NULL;
				$imglist["image"] = NULL;
//				$imglist["image_label"] = NULL;
//				$imglist["required_options"] = NULL;
				$imglist["small_image"] = NULL;
//				$imglist["small_image_label"] = NULL;
				$imglist["thumbnail"] = NULL;
//				$imglist["thumbnail_label"] = NULL;
				$imglist["_media_attribute_id"] = "88";
				$imglist["_media_image"] = $v2;
				$imglist["_media_lable"] = NULL;
				$imglist["_media_position"] = $mediacount;
				$imglist["_media_is_disabled"] = "0";
				$mediacount++;
	
			}
			$imglistcsv[] = $imglist;
		}
	}
}

//$title = array_keys($imglist);
//$imglistcsvtxt = implode(",", $title) . "\n";

foreach($imglistcsv as $k => $v){
	$imglistcsvtxt .= implode(",", $v) . "\n";
}
header('Content-Disposition:attachment; filename="imglist.csv"');
header('Content-Type:application/octet-stream');
header('Content-Length:'.strlen($imglistcsvtxt));
echo $imglistcsvtxt;
exit;




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

$imglist["sku"]
$imglist["has_options"]
$imglist["image"]
$imglist["image_label"]
$imglist["required_options"]
$imglist["small_image"]
$imglist["small_image_label"]
$imglist["thumbnail"]
$imglist["thumbnail_label"]
$imglist["_media_attribute_id"]
$imglist["_media_image"]
$imglist["_media_lable"]
$imglist["_media_position"]
$imglist["_media_is_disabled"]
*/
?>