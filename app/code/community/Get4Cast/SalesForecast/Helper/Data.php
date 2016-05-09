<?php
class Get4Cast_SalesForecast_Helper_Data
    extends Mage_Core_Helper_Abstract
{
	public function todayPlus($plus){
		$date_format = Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);
		$date_format = str_replace('yyyy', 'Y', $date_format);
		$result = Date($date_format, strtotime('+'.$plus.' days'));
		return $result; 
	}
	
	public function getAllStores($ids_from_group = null){
		$return = array();
		$return_group = array();
		
		$all_stores = Mage::app()->getStores();

		foreach ($all_stores as $store_id => $store){
			$_store = Mage::getModel('core/store')->load($store_id);
			$website = Mage::getModel('core/website')->load($_store->getWebsiteId());
			
			$store_info['group_id'] = $_store->getGroupId();
			
			$store_info['website_id'] = $website->getId();
			$store_info['website_name'] = $website->getName();
			
			$store_info['store_id'] = $store_id;
			$store_info['store_name'] = $store->getFrontendName();
			
			$store_info['view_id'] = $store_id;
			$store_info['view_unsecure_url'] = $store->getUrl();
			$store_info['view_secure_url'] = $store->getUrl('',array('_forced_secure'=>true));
			$store_info['view_code'] = Mage::app()->getStore($store_id)->getCode();
			$store_info['view_name'] = Mage::app()->getStore($store_id)->getName();
			
			if($ids_from_group == $store_info['group_id']){
				$return_group[] = $store_info['store_id'];
			}
			
			$return[] = $store_info; 
		}
		if($ids_from_group){
			return $return_group;
		}
		return $return;
	}
	
	public function getAllStoresToSelect(){
		$return = array();
		$all_stores = $this->getAllStores();
		
		foreach ($all_stores as $store_id => $store){
			if(!array_key_exists($store['store_id'], $return)){
				$return[$store['store_id']] = $store['website_name'].' ('.$store['view_code'].')';
			}
		}
		return $return;
	}
	
	public function updateTimestamps($obj){
		$timestamp = now();
        if ($obj->isObjectNew()) {
            $obj->setCreatedAt($timestamp);
        } else {
			$obj->setUpdatedAt($timestamp);
		}
        return $obj;
	}
	
	public function getModuleVersion(){
		return Mage::getConfig()
				->getModuleConfig('Get4Cast_SalesForecast')
				->version
				->__toString();
	}
	
	public function getDateFormat(){
		$date_format = Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);		
		$date_format = strtolower($date_format);
		$date_format = preg_replace('/(y)\\1+/', 'Y', $date_format);
		$date_format = preg_replace('/(m)\\1+/', 'm', $date_format);
		$date_format = preg_replace('/(d)\\1+/', 'd', $date_format);
		
		return $date_format;
	}
	
	public function diffDates($start = 'today', $end){
		$date_format = $this->getDateFormat();
		$date_format = explode('/', $date_format);
		
		if($start == 'today'){
			$start = Mage::getModel('core/date')->date($this->getDateFormat());
		}
		
		$day_index = array_search('d', $date_format);
		$month_index = array_search('m', $date_format);
		$year_index = array_search('Y', $date_format);

		$start = explode('/', $start);
		$end = explode('/', $end);
		
		$start = $start[$year_index].'-'
						.$start[$month_index].'-'
						.$start[$day_index];
		$end = $end[$year_index].'-'
						.$end[$month_index].'-'
						.$end[$day_index];
		
		$start = strtotime($start);
		$end = strtotime($end);
		$difference = $end - $start;
		$diff_days = floor($difference/(60*60*24));
		return $diff_days;
	}
}
