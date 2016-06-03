<?php
class Get4Cast_SalesForecast_Adminhtml_ForecastController
    extends Mage_Adminhtml_Controller_Action
{
    public function indexAction(){
        $forecastBlock = $this->getLayout()
            ->createBlock('get4cast_salesforecast_adminhtml/forecast');

        $this->loadLayout()
            ->_addContent($forecastBlock)
            ->renderLayout();
    }
    
    public function editAction(){
		try{
			
			$forecast = Mage::getModel('get4cast_salesforecast/forecast');
			if ($forecast_id = $this->getRequest()->getParam('id', false)) {
				$forecast->load($forecast_id);

				if ($forecast->getId() < 1) {
					$this->_getSession()->addError(
						$this->__('This forecast no longer exists.')
					);
					return $this->_redirect(
						'get4cast_salesforecast_admin/forecast/index'
					);
				}
			}

			Mage::register('current_forecast', $forecast);

			$forecastEditBlock = $this->getLayout()->createBlock(
				'get4cast_salesforecast_adminhtml/forecast_edit'
			);
			
			$this->loadLayout()
				->_addContent($forecastEditBlock)
				->renderLayout();
		} catch (Exception $e) {
			Mage::log('G4C-MAG-4yrx: '.$e->getMessage());
			return false;
		}
    }

	public function requestValidateAction(){
		try{
			$get_data = $this->getRequest()->getParam('data');
			$get_data = Mage::helper('core')->jsonDecode($get_data);
			if($get_data){
				$errors = array();
				$helper = Mage::helper('get4cast_salesforecast/data');
				$forecast = Mage::getModel('get4cast_salesforecast/forecast');
				
				/*
				 * START: basic validation - required fields
				 * */
				if(!$get_data['store_group_id']){
					$errors[] = $this->__('\'Store\' is required');
				}
				
				if(!$get_data['email']){
					$errors[] = $this->__('\'Your email\' is required');
				}
				
				if(!$get_data['historical_date_start']){
					$errors[] = $this->__('\'Analyse from day\' is required');
				}
				
				if(!$get_data['historical_date_end']){
					$errors[] = $this->__('\'Analyse to day\' is required');
				}
				
				if(!$get_data['forecast_date_end']){
					$errors[] = $this->__('\'Forecast until day\' is required');
				}
				/*
				 * END: basic validation - required fields
				 * */

				// Check if 'Analyse from day' is before 'Analyse to day'
				$diff_days = $helper->diffDates($get_data['historical_date_start'], $get_data['historical_date_end']); 
				if($diff_days < 1){
					$errors[] = $this->__('\'Analyse to day\' must be after \'Analyse from day\'');
				}
				
				// Check if 'Forecast until day' is a future date
				if($diff_days < 1){
					$errors[] = $this->__('\'Forecast until day\' must be in the future');
				}
				
				$forecast = Mage::getModel('get4cast_salesforecast/forecast');
				
				$return['type'] = count($errors) ? 0 : 1;
				$return['data'] = $errors;
				echo Mage::helper('core')->jsonEncode($return);
				return true;
			}
			echo Mage::helper('core')->jsonEncode($api_reponse);
			return true;
		} catch (Exception $e) {
			Mage::log('G4C-MAG-eBGA: '.$e->getMessage());
			return false;
		}
	}
	
	public function getForecastPriceAction(){
		try{
			$get_data = $this->getRequest()->getParam('data');
			$get_data = Mage::helper('core')->jsonDecode($get_data);
			$collect = Mage::getModel('get4cast_salesforecast/collect');
			$return_data = $collect->getForecastPrice($get_data);
			echo $return_data;
			return true;
		} catch (Exception $e) {
			Mage::log('G4C-MAG-kNRW: '.$e->getMessage());
			return false;
		}
	}
	
	public function requestCollectAction(){
		try{

			$get_data = $this->getRequest()->getParam('data');
			$get_data = Mage::helper('core')->jsonDecode($get_data);
			$get_data['limit'] = $this->getRequest()->getParam('limit');
			$get_data['page'] = $this->getRequest()->getParam('page');
			$get_data['data_size'] = $this->getRequest()->getParam('data_size');
			$get_data['pages'] = $this->getRequest()->getParam('pages');
			$collect = Mage::getModel('get4cast_salesforecast/collect');
			$return_data = $collect->requestCollect($get_data);
			echo $return_data;
			return true;
			
		} catch (Exception $e) {
			Mage::log('G4C-MAG-P3vP: '.$e->getMessage());
			return false;
		}
	}

	public function requestSendAction(){
		$get_data = $this->getRequest()->getParam('data');
		$get_data = Mage::helper('core')->jsonDecode($get_data);
		$api_reponse = '';
		if($get_data){
			$api_client = Mage::getModel('get4cast_salesforecast/apiclient');
			$api_reponse = $api_client->getForecastPrice($get_data);
		}
		echo Mage::helper('core')->jsonEncode($api_reponse);
		return true;
	}

    protected function _isAllowed()
    {
        $action_name = $this->getRequest()->getActionName();
        $admin_session = Mage::getSingleton('admin/session');
        $is_allowed = false;
        switch ($action_name) {
            case 'index':
				$is_allowed = $admin_session->isAllowed('admin/report/get4cast_salesforecast/sales_forecast/forecast_history');
            break;
            case 'edit':
            case 'requestValidate':
            case 'getForecastPrice':
            case 'requestCollect':
            case 'requestSend':
				$is_allowed = $admin_session->isAllowed('admin/report/get4cast_salesforecast/sales_forecast/new_forecast');
            break;
        }
        
        return $is_allowed;
    }
}
