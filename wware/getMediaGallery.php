<?php

$imgs = explode(" ", $_REQUEST["imgs"]);
$html = "";
foreach($imgs as $img){
	$html  = '<img src="' . $img . '" ';
	$html .= 'width="36px" ';
	$html .= 'onmouseover="qv_changeimg(' . "'" . $img . "'" . ', ' . $_REQUEST["id"] . ');" />';
	echo $html;
}



?>