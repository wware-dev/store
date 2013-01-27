<?php

function getClassMethod($o, $echo=false){
	$className = get_class($o);
	$methods = get_class_methods($className);
	$vars = get_class_vars($className);
	
	$out = "[" . $className . "]\nMethods::\n" . implode("\n", $methods) . "\nVars::\n" . implode("\n", $vars);
	
	error_log($out);
	if($echo){echo $out;}
}

?>
