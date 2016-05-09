<?php
class Get4Cast_SalesForecast_Model_Config
    extends Mage_Core_Model_Abstract
{
    protected function _construct()
    {
        $this->_init('get4cast_salesforecast/config');
    }
    
    public function getApiKey(){	
		$_config_app = $this->getCollection()
						->addFilter('config','app')
						->getFirstItem();
						
		$_config_app_value = $_config_app->getValue();
		$_config_app_json = Mage::helper('core')->jsonDecode($_config_app_value);
		
		if(isset($_config_app_json['app_key'])){
			return $_config_app_value;
		}
		
		return false;
	}
    
    protected function _beforeSave()
    {
        parent::_beforeSave();
        Mage::helper('get4cast_salesforecast/data')
					->updateTimestamps($this);
        return $this;
    }
}
