<?php
class Get4Cast_SalesForecast_Model_Forecast
    extends Mage_Core_Model_Abstract
{
    const VISIBILITY_HIDDEN = '0';
    const VISIBILITY_DIRECTORY = '1';
    
    protected function _construct()
    {
        $this->_init('get4cast_salesforecast/forecast');
    }

    public function getForecastDaysAhead()
    {		
		$session_server_config = Mage::getSingleton('core/session')
					->getGet4CastServerConfig();
		
		$days_available = $session_server_config['forecast_days_available'];
		$price_per_day = (float)$session_server_config['price_per_day'];

		$account_balance = Mage::getSingleton('core/session')
							->getGet4CastAccountBalance($account_balance);
		if($account_balance){
			// Flash session
			Mage::getSingleton('core/session')
				->setGet4CastAccountBalance(null);
		} else {
			$api_client = Mage::getSingleton('get4cast_salesforecast/apiclient');
			$account_balance = $api_client->getAccountBalance();
		}

		$limit = $account_balance['account_balance']['days_ahead'];
		$helper = Mage::helper('get4cast_salesforecast/data');
		$return = array();
		$return['-1'] = $helper->__('Select...');
		foreach($days_available as $day){	
			$price = '';
			if($day > $limit ){
				$sum = '';
				$days_until = $helper->__('(insufficient balance)');
			} else {
				$sum = $helper->todayPlus($day);
				$days_until = $helper->__('days, until');
				$price = ' ( $'.number_format($day * $price_per_day, 2).' ) ';
			}
			
			$return[$day] = $day.' '.$days_until.' '.$sum.$price;
		}
		
        return $return;
    }
	
	public function processForecastHistory($forecast_history){
		$session_forecast_history = Mage::getSingleton('core/session')
									->getGet4CastForecastHistory();
		if($session_forecast_history && $forecast_history){
			foreach($forecast_history as $data){
				if($data->action != null && $data->action != 'nop'){
					try{
						$_forecast = Mage::getModel('get4cast_salesforecast/forecast');
						$_forecast = $_forecast->load($data->entity_id);
						
						if($data->action == 'delete'){
							$_forecast->delete();
							continue;
						}
						
						$_forecast->setCreatedAt($data->created_at);
						$_forecast->setUpdatedAt($data->updated_at);
						$_forecast->setPeriodStart($data->period_start);
						$_forecast->setPeriodEnd($data->period_end);
						$_forecast->setForecastAhead($data->forecast_ahead);
						$_forecast->setUrl($data->url);
						$_forecast->setStatus($data->status);
						$_forecast->save();
						
					} catch (Exception $e) {
						Mage::log($e->getMessage());
					}
				}
			}
			Mage::getSingleton('core/session')->setGet4CastForecastHistory($forecast_history);
		}
	}
    
    protected function _beforeSave()
    {
        parent::_beforeSave();
    }
    
    protected function _updateTimestamps()
    {
		if(!$this->getUpdatedAt()){
			$timestamp = now();
			$this->setUpdatedAt($timestamp);
		}
		
        if ($this->isObjectNew() && !$this->getCreatedAt()) {
            $this->setCreatedAt($timestamp);
        }
        
        return $this;
    }
    
    protected function _prepareUrlKey()
    {
        return $this;
    }
}
