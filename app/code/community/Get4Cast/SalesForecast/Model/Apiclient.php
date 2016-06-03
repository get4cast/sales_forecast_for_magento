<?php
class Get4Cast_SalesForecast_Model_Apiclient
    extends Mage_Core_Model_Abstract
{
	public $register_called = false;
	
    protected function _construct()
    {
		$this->api_url = Mage::getStoreConfig('get4cast/default/api_url');
    }
    
    public function httpRequest($path, $method = 'post', $data = null){
		try{
			if($data === null){
				$data = array();
			}
			
			$config_model = Mage::getModel('get4cast_salesforecast/config');
			
			$_config_app = $config_model->getCollection()
							->addFilter('config','app')
							->getFirstItem();
							
			$_config_app_value = $_config_app->getValue();
			$_config_app_array = Mage::helper('core')->jsonDecode($_config_app_value);
			
			if($this->register_called){
				$data['_app_key'] = null;
			} elseif(!isset($_config_app_array['app_key'])){
				Throw new Exception('Could not get app key');
			}
			
			$data['_app_key'] = $_config_app_array['app_key'];
			
			$helper = Mage::helper('get4cast_salesforecast/data');
			$data['_module_version'] = $helper->getModuleVersion();

			$url = $this->api_url.'/'.$path;

			$data_json = Mage::helper('core')->jsonEncode($data);
			$data_json = urlencode($data_json);
			$send_data = '';
			if($data_json){
				$send_data = '&p='.$data_json;
			}
			
			Mage::log('..........................');
			Mage::log($send_data);
			Mage::log('..........................');
			
			if(strtolower($method) == 'post'){
				return $this->httpPost($url, $send_data);
			} elseif(strtolower($method) == 'get'){
				return $this->httpGet($url, $send_data);
			}
		} catch (Exception $e) {
			Mage::log('G4C-MAG-21Pf: '.$e->getMessage());
			return false;
		}
	}
	
	public function httpPost($url, $data){
		try{
			$ch = curl_init($url);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);

			$response  = curl_exec($ch);

			$return_code = (int)curl_getinfo( $ch, CURLINFO_HTTP_CODE);
			if($return_code != 200){
				Throw new Exception('Could not get API response');
			}

			curl_close($ch);
			
			return $response;
			
		} catch (Exception $e) {
			Mage::log('G4C-MAG-nlZP: '.$e->getMessage());
			return false;
		}
	}
    
    public function register(){
		try{
			//Check if app is already registered
			$config_model = Mage::getModel('get4cast_salesforecast/config');
			
			$_config_app = $config_model->getCollection()
							->addFilter('config','app')
							->getFirstItem();
							
			$_config_app_value = $_config_app->getValue();
			$_config_app_array = Mage::helper('core')->jsonDecode($_config_app_value);
			
			if(isset($_config_app_array['app_key'])){
				return $_config_app_value;
			}
			
			//Register app...
			$this->register_called = true;
			
			//POST data to API server for security reasons
			$data = array();
			$data['server'] = $_SERVER;
			$data['store'] = Mage::helper('get4cast_salesforecast/data')
									->getAllStores();
			
			$data['magento_version'] = Mage::getVersion();
			
			//Request register
			$response = $this->httpRequest('Install/register', 'POST', $data);			
			$response = Mage::helper('core')->jsonDecode($response);
			
			if(isset($response['_error'])){
				Throw new Exception($response['_error']);
			}
			
			if(!$response || !isset($response['app_key'])){
				Throw new Exception('Request register returned wrong parameters');
			}

			//Save app register
			$_config_app->setConfig('app');
			$_config_app->setValue(Mage::helper('core')->jsonEncode($response));
			$saved = $_config_app->save();
			
			if(!$saved){
				Throw new Exception('Register key received but could not save it');
			}

			return $response;
			
		} catch (Exception $e) {
			Mage::log('G4C-MAG-4yrx: '.$e->getMessage());
			return false;
		}
	}
	
	public function getAccountInfo(){
		try{
			$response = $this->httpRequest('install/getInfo', 'POST');
			$response = Mage::helper('core')->jsonDecode($response);
			
			if(isset($response['_error'])){
				Throw new Exception($response['_error']);
			}
			
			return $response;
		} catch (Exception $e) {
			Mage::log('G4C-MAG-b2rq: '.$e->getMessage());
			return false;
		}
	}
	
	public function getForecastPrice($get_data){
		try{

			$response = $this->httpRequest('forecast/getPrice', 'POST', $get_data);
			
			$response = Mage::helper('core')->jsonDecode($response);

			if(isset($response['_error'])){
				Throw new Exception($response['_error']);
			}
			
			return $response;
			
		} catch (Exception $e) {
			Mage::log('G4C-MAG-ZcKc: '.$e->getMessage());
			return false;
		}
	}
	
	
	public function requestSend($post_data){
		try{
			
			$response = $this->httpRequest('Forecast/requestForecast', 'POST', $post_data);

			$response = Mage::helper('core')->jsonDecode($response);

			if(isset($response['_error'])){
				Throw new Exception($response['_error']);
			}

		} catch (Exception $e) {
			Mage::log('G4C-MAG-fcnW: '.$e->getMessage());
		}
		
		return $response;
	}
	
	
}
