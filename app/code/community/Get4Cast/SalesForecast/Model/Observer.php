<?php
class Get4Cast_SalesForecast_Model_Observer {
	
	public function get4CastAddCustomHandle(Varien_Event_Observer $observer){
		$controller_action = $observer->getEvent()->getAction();
		$layout = $observer->getEvent()->getLayout();
		$controller_module = $controller_action->getRequest()->getControllerModule();
		
		if ($controller_action 
			&& $layout 
			&& $controller_module == 'Get4Cast_SalesForecast_Adminhtml') 
		{
			$layout->getUpdate()->addHandle('get4cast_salesforecast');
		}
		return $this;
	}
	
    public function get4CastPreDispatch($observer) {
		try {
			$api_client = Mage::getModel('get4cast_salesforecast/apiclient');
			$_response = $api_client->register();
			$_response_array = Mage::helper('core')->jsonDecode($_response);
			if(!isset($_response_array['app_key'])){
				Throw new Exception('App not registered');
			}
			
			$api_client = Mage::getModel('get4cast_salesforecast/apiclient');
			$account_info = $api_client->getAccountInfo();

			$this->processForecastHistory($account_info['forecast_history']);

			Mage::getSingleton('core/session')
				->setGet4CastAccountInfo($account_info);
			
		} catch (Exception $e) {
			Mage::log('G4C-MAG-YXiY: '.$e->getMessage());
			return false;
		}
    }
    
    public function processForecastHistory($forecast_history){
		try{
			$report_keys = array();
			if($forecast_history){
				foreach($forecast_history as $data){
					
					if($data['action'] != null && $data['action'] != 'nop'){

						$forecast = Mage::getModel('get4cast_salesforecast/forecast');
						$forecast = $forecast->getCollection()
										->addFieldToFilter('report_key', $data['report_key']);
						$_forecast = $forecast->getFirstItem();
						$report_keys[] = $data['report_key'];
						
						if(!$_forecast->getId()){
							$_forecast = Mage::getModel('get4cast_salesforecast/forecast');
						}

						if($data['action'] == 'delete'){
							$_forecast->delete();
							continue;
						}

						$_forecast->setReportKey($data['report_key']);
						
						$date = Mage::getModel('core/date')->gmtDate($data['created_at']);
						$_forecast->setCreatedAt($date);
						
						$date = Mage::getModel('core/date')->gmtDate($data['updated_at']);
						$_forecast->setUpdatedAt($date);
						
						$_forecast->setStoreGroupId($data['store_group_id']);
						$_forecast->setStoreGroupName($data['store_group_name']);
						$_forecast->setEmail($data['email']);
						$_forecast->setPeriodStart($data['period_start']);
						$_forecast->setPeriodEnd($data['period_end']);
						$_forecast->setForecastDateStart($data['forecast_date_start']);
						$_forecast->setForecastDateEnd($data['forecast_date_end']);
						$_forecast->setPrice($data['price']);
						$_forecast->setPaymentStatus($data['payment_status']);
						
						$_forecast->setReportKey($data['report_key']);
						$_forecast->setUrl($data['url']);
						$_forecast->setStatus($data['status']);

						$_forecast->save();
					}
				}
			}
			
			$forecast = Mage::getModel('get4cast_salesforecast/forecast');
			$forecasts = $forecast->getCollection();
			if($forecasts){
				foreach($forecasts as $forecast){
					if(array_search($forecast->getReportKey(), $report_keys) === false){
						$forecast->delete();
					}
				}
			}
			
		} catch (Exception $e) {
			Mage::log('G4C-MAG-b2uu: '.$e->getMessage());
			return false;
		}
	}
}
