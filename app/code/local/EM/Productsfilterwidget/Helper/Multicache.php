<?php

class EM_Productsfilterwidget_Helper_Multicache extends Mage_Core_Helper_Abstract
{
	public function __construct()
	{	
		$this->cache_dir	=	'var/cache/productsfilterwidget/';	

			if(!$this->cache_dir)
			{
				die('Cannot load config cache filearray');exit;
			}
			@mkdir($this->cache_dir,0777,true);
			$lib_cache	=	Mage::helper('productsfilterwidget/cache');
	}
	
	public function set($key=false,$value=false,$timeout=60)
	{
		$timeout = intval($timeout);
		if(!$key) return false;
		if($timeout<1) return false;
		
			$array['datacreated']	=	time();
			$array['timeout']		=	$timeout;
			$array['datavalue']		=	$value;
			$lib_cache	=	Mage::helper('productsfilterwidget/cache');
			return $lib_cache->make($array,$this->cache_dir.$key.'.php');
		
	}
	
	public function get($key=false)
	{	
		if(!$key) return false;
			$lib_cache	=	Mage::helper('productsfilterwidget/cache');
			$data	=	$lib_cache->load($this->cache_dir.$key.'.php');
			
			if(!$data) return false;
			if(intval($data['datacreated'])	+ intval($data['timeout']) < time())
			{
				@unlink($this->cache_dir.$key.'.php');
				return false;
			}
			return $data['datavalue'];
		
	}
	
	public function delete($key)
	{
		if(!$key) return false;
		
			@unlink($this->cache_dir.$key.'.php');
			return true;
		
	}
	
	public function clear()
	{
		
			$this->recursiveDelete($this->cache_dir);
			return true;
		
	}
	
    private function recursiveDelete($str){
        if(is_file($str)){
            return @unlink($str);
        }
        elseif(is_dir($str)){
            $scan = glob(rtrim($str,'/').'/*');
            foreach($scan as $index=>$path){
                $this->recursiveDelete($path);
            }
            return true;
        }
    }
	
}