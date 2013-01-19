<?php
 /**
 * GoMage Advanced Navigation Extension
 *
 * @category     Extension
 * @copyright    Copyright (c) 2010-2012 GoMage (http://www.gomage.com)
 * @author       GoMage
 * @license      http://www.gomage.com/license-agreement/  Single domain license
 * @terms of use http://www.gomage.com/terms-of-use
 * @version      Release: 3.2
 * @since        Class available since Release 1.0
 */
	
class GoMage_Navigation_Helper_Data extends Mage_Core_Helper_Abstract{
    
    	
    public function getConfigData($node){
		return Mage::getStoreConfig('gomage_navigation/'.$node);
	}
	
	public function getAllStoreDomains(){
		
		$domains = array();
    	
    	foreach (Mage::app()->getWebsites() as $website) {
    		
    		$url = $website->getConfig('web/unsecure/base_url');
    		
    		if($domain = trim(preg_replace('/^.*?\\/\\/(.*)?\\//', '$1', $url))){
    		
    		$domains[] = $domain;
    		
    		}
    		
    		$url = $website->getConfig('web/secure/base_url');
    		
    		if($domain = trim(preg_replace('/^.*?\\/\\/(.*)?\\//', '$1', $url))){
    		
    		$domains[] = $domain;
    		
    		}
    		
    	}
    	
    	return array_unique($domains);
    	
		
	}
	
	public function getAvailabelWebsites(){
		return $this->_w();
	}
	
	public function getAvailavelWebsites(){
		return $this->_w();
	}
	
	protected function _w(){
    
        if(!Mage::getStoreConfig('gomage_activation/advancednavigation/installed') || 
           (intval(Mage::getStoreConfig('gomage_activation/advancednavigation/count')) > 10))
		{
			return array();
		}
		            		
		$time_to_update = 60*60*24*15;
		
		$r = Mage::getStoreConfig('gomage_activation/advancednavigation/ar');
		$t = Mage::getStoreConfig('gomage_activation/advancednavigation/time');
		$s = Mage::getStoreConfig('gomage_activation/advancednavigation/websites');
		
		$last_check = str_replace($r, '', Mage::helper('core')->decrypt($t));
		
		$allsites = explode(',', str_replace($r, '', Mage::helper('core')->decrypt($s)));
		$allsites = array_diff($allsites, array(""));
			
		if(($last_check+$time_to_update) < time()){
			$this->a(Mage::getStoreConfig('gomage_activation/advancednavigation/key'), 
			         intval(Mage::getStoreConfig('gomage_activation/advancednavigation/count')),
			         implode(',', $allsites));
		}
		
		return $allsites;
		
	}
	
	public function a($k, $c = 0, $s = ''){
		
		$ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, sprintf('https://www.gomage.com/index.php/gomage_downloadable/key/check'));
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, 'key='.urlencode($k).'&sku=advanced-navigation&domains='.urlencode(implode(',', $this->getAllStoreDomains())));
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        
        $content = curl_exec($ch);
        
        $r	= Zend_Json::decode($content);
        $e = Mage::helper('core');
        if(empty($r)){
        	
        	$value1 = Mage::getStoreConfig('gomage_activation/advancednavigation/ar');
        	
	        $groups = array(
	        	'advancednavigation'=>array(
	        		'fields'=>array(
	        			'ar'=>array(
	        				'value'=>$value1
	        			),
	        			'websites'=>array(
	        				'value'=>(string)Mage::getStoreConfig('gomage_activation/advancednavigation/websites')
	        			),
	        			'time'=>array(
	        				'value'=>(string)$e->encrypt($value1.(time()-(60*60*24*15-1800)).$value1)
	        			),
	        			'count'=>array(
	        				'value'=>$c+1)
	        		)
	        	)
        	);
        	
	        Mage::getModel('adminhtml/config_data')
	                ->setSection('gomage_activation')
	                ->setGroups($groups)
	                ->save();
        	
	        Mage::getConfig()->reinit();
            Mage::app()->reinitStores();        
	                
        	return;
        }
        
        $value1 = '';
        $value2 = '';
        
        
        
        if(isset($r['d']) && isset($r['c'])){
    		$value1 = $e->encrypt(base64_encode(Zend_Json::encode($r)));
        
        
        if (!$s) $s = Mage::getStoreConfig('gomage_activation/advancednavigation/websites');
        
        $s = array_slice(explode(',', $s), 0, $r['c']);
        
        $value2 = $e->encrypt($value1.implode(',', $s).$value1);
        
        }
        $groups = array(
        	'advancednavigation'=>array(
        		'fields'=>array(
        			'ar'=>array(
        				'value'=>$value1
        			),
        			'websites'=>array(
        				'value'=>(string)$value2
        			),
        			'time'=>array(
        				'value'=>(string)$e->encrypt($value1.time().$value1)
        			),
        			'installed'=>array(
        				'value'=>1
        			),
        			'count'=>array(
        				'value'=>0)
        			
        		)
        	)
        );
        
        Mage::getModel('adminhtml/config_data')
                ->setSection('gomage_activation')
                ->setGroups($groups)
                ->save();
                
        Mage::getConfig()->reinit();
        Mage::app()->reinitStores();        
        
	}
	
	public function ga(){
		return Zend_Json::decode(base64_decode(Mage::helper('core')->decrypt(Mage::getStoreConfig('gomage_activation/advancednavigation/ar'))));
	}
	
	public function isGomageNavigation(){
		 if ($this->isMobileDevice() && Mage::getStoreConfigFlag('gomage_navigation/general/disable_mobile')){
		 	return false;
		 }
	     return in_array(Mage::app()->getStore()->getWebsiteId(), $this->getAvailavelWebsites()) &&
	            Mage::getStoreConfigFlag('gomage_navigation/general/mode'); 	         	     
	}
	
	public function isGomageNavigationAjax(){
		
	     return $this->isGomageNavigation() &&                 
	    		Mage::getStoreConfigFlag('gomage_navigation/general/pager')
	    		&& 
                (Mage::registry('current_category') ||
                (Mage::app()->getFrontController()->getRequest()->getRouteName() == 'catalogsearch' &&
                 Mage::app()->getFrontController()->getRequest()->getControllerName() != 'advanced'));
	}
	
	public function isGomageNavigationClearAjax(){
		
	     return $this->isGomageNavigation()&& 
                (Mage::registry('current_category') ||
                (Mage::app()->getFrontController()->getRequest()->getRouteName() == 'catalogsearch' &&
                 Mage::app()->getFrontController()->getRequest()->getControllerName() != 'advanced'));
	}
	
	public function formatColor($value){
	    if ($value = preg_replace('/[^a-zA-Z0-9\s]/', '', $value)){
	       $value = '#' . $value; 	        
	    }
	    return $value;
	}
	
	public function isFrendlyUrl(){
		return $this->isGomageNavigation() && Mage::getStoreConfigFlag('gomage_navigation/general/frendlyurl');
	}
	
	public function getFilterUrl($route = '', $params = array()){
		
		if (!$this->isFrendlyUrl()){
            return Mage::getUrl($route, $params);
        }

        $model = Mage::getModel('core/url');
        $request_query = $model->getRequest()->getQuery();
        $attr = Mage::registry('gan_filter_attributes');

        foreach($model->getRequest()->getQuery() as $param => $value){
        	if ($param == 'cat'){
            	$values = explode(',', $value);
            	$prepare_values = array();
            	foreach($values as $_value){
            		$category = Mage::getModel('catalog/category')->load($_value);
            		if ($category && $category->getId()){
            			$prepare_values[] = $category->getData('url_key');
            		}
            	}
            	$model->getRequest()->setQuery($param, implode(',', $prepare_values));
            }elseif (isset($attr[$param]) && !in_array($attr[$param]['type'], array('price', 'decimal'))){	
            	$values = explode(',', $value);
            	$prepare_values = array();
            	foreach($values as $_value){                		
            		foreach($attr[$param]['options'] as $_k => $_v){
            			if ($_v == $_value){
            				$prepare_values[] = $_k;
            				break;
            			}
            		}
            	}            		
                $model->getRequest()->setQuery($param, implode(',', $prepare_values));                
            }
        }
                
        foreach ($params['_query'] as $param => $value){
        	if ($value){
	        	if ($param == 'cat'){
	            	$values = explode(',', $value);
	            	$prepare_values = array();
	            	foreach($values as $_value){
	            		$category = Mage::getModel('catalog/category')->load($_value);
	            		if ($category && $category->getId()){
	            			$prepare_values[] = $category->getData('url_key');
	            		}
	            	}
	            	$params['_query'][$param] = implode(',', $prepare_values);
	            }elseif (isset($attr[$param]) && !in_array($attr[$param]['type'], array('price', 'decimal'))){	
	            	$values = explode(',', $value);
	            	$prepare_values = array();
	            	foreach($values as $_value){            			
		            	foreach($attr[$param]['options'] as $_k => $_v){
	            			if ($_v == $_value){
	            				$prepare_values[] = $_k;
	            				break;
	            			}
	            		}            		
	            	}            		
	                $params['_query'][$param] = implode(',', $prepare_values);                
	            }
        	}
        }
        
        $url = $model->getUrl($route, $params);

        foreach($request_query as $param => $value){
        	$model->getRequest()->setQuery($param, $value);
        }
        
        return $url; 
        
	}
	
 	public function formatUrlValue($value){    	
        $value = preg_replace('#[^0-9a-z]+#i', '_', Mage::helper('catalog/product_url')->format($value));
        $value = strtolower($value);
        $value = trim($value, '-');

        return $value;
    }
    
    public function isMobileDevice(){		
    	$user_agent = strtolower($_SERVER['HTTP_USER_AGENT']);
    	if (!$user_agent || strpos($user_agent, 'ipad')) return false;
    	
		$regex_match="/(nokia|iphone|android|motorola|^mot-|softbank|foma|docomo|kddi|up.browser|up.link|";
		$regex_match.="htc|dopod|blazer|netfront|helio|hosin|huawei|novarra|CoolPad|webos|techfaith|palmsource|";
		$regex_match.="blackberry|alcatel|amoi|ktouch|nexian|samsung|^sam-|s[cg]h|^lge|ericsson|philips|sagem|wellcom|bunjalloo|maui|";	
		$regex_match.="symbian|smartphone|midp|wap|phone|windows ce|iemobile|^spice|^bird|^zte-|longcos|pantech|gionee|^sie-|portalmmm|";
		$regex_match.="jigs browser|hiptop|^ucweb|^benq|haier|^lct|operas*mobi|opera*mini|320x320|240x320|176x220";
		$regex_match.=")/i";		
		return preg_match($regex_match, strtolower($user_agent));
		
    }
    
	public function IsGooglebot(){
		// check if user agent contains googlebt
		if(preg_match("/Google/",$_SERVER['HTTP_USER_AGENT']) || preg_match("/bot/",$_SERVER['HTTP_USER_AGENT'])){
			$ip = $_SERVER['REMOTE_ADDR'];
			//server name e.g. crawl-66-249-66-1.googlebot.com
			$name = gethostbyaddr($ip);
			//check if name ciontains googlebot
			if(preg_match("/Googlebot/",$name) || preg_match("/bot/",$name)){
				//list of IP's
				$hosts = gethostbynamel($name);
				foreach($hosts as $host){
					if ($host == $ip){
						return true;
					}
				}
				return false; // Pretender, take some action if needed
			}else{
			return false; // Pretender, take some action if needed
			}
		}else{
			return true;
		}
		return false;
	}	

	public function getFilterItemCount($filter)
	{
		$count = 0;
		if ( $filter && $filter->getItems() )
		{
			foreach( $filter->getItems() as $item )
			{
				$count += $item->getCount();
			}
		}
		
		if ( $count == 0 && $filter->getName() == 'Stock' )
		{
			return 1;
		}
		
		return $count;
	}
}

