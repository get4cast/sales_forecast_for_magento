<?php
class Get4Cast_SalesForecast_Model_Collect
    extends Mage_Core_Model_Abstract
{
    protected function _construct(){
        $this->_init('get4cast_salesforecast/forecast');
    }
    
    public function getForecastPrice($get_data){
		try{
			$api_reponse = '';
			if(!$get_data){
				return Mage::helper('core')->jsonEncode($api_reponse);
			}
			
			$get_data['data_size'] = $this->getOrders($get_data['store_group_id']
										, $get_data['h_historical_date_start']
										, $get_data['h_historical_date_end']
										, false // limit
										, false // page
										, false // filter
										, true); // get only count
			
			//DEMO
			//$get_data['data_size'] = $get_data['h_historical_diff']*13;
			
			if(!$get_data['data_size']){
				$api_reponse['error'] = 'There is no data in the selected period';
				return Mage::helper('core')->jsonEncode($api_reponse);
			}

			$orders = $this->getOrders($get_data['store_group_id']
						, $get_data['h_historical_date_start']
						, $get_data['h_historical_date_end']
						, false // limit
						, false // page
						, array('state'=>'complete'));

			$ticket = 0;
			foreach($orders as $order){
				$ticket += $order->getGrandTotal();
			}
			$average_ticket = $ticket/count($orders);
			$get_data['average_ticket'] = number_format($average_ticket, 2, '.', '');
			
			//DEMO
			//$get_data['average_ticket'] = '220.20';

			
			$get_data['store_group_url'] = Mage::app()->getStore($get_data['store_group_id'])->getBaseUrl();

			$api_client = Mage::getModel('get4cast_salesforecast/apiclient');
			$api_reponse = $api_client->getForecastPrice($get_data);
			
			return Mage::helper('core')->jsonEncode($api_reponse);
			
		} catch (Exception $e) {
			Mage::log('G4C-MAG-6CAo: '.$e->getMessage());
			return false;
		}
	}
	
	public function getOrders($store_group_id, $from_date, $to_date, $limit = false, $page = false, $field_filter = false, $only_get_count = false){
		
		$helper = Mage::helper('get4cast_salesforecast/data');

		$stores = $store_group_id;
		
		$magento_core_date = Mage::getSingleton('core/date');
		$date_format = 'Y-m-d H:i:s';
		
		$from_date = str_replace('/', '-', $from_date);
		$from_date .= ' 00:00:00';
		$from_date = $magento_core_date->gmtDate($date_format, $from_date);
		
		$to_date = str_replace('/', '-', $to_date);
		$to_date .= ' 23:59:59';
		$to_date = $magento_core_date->gmtDate($date_format, $to_date);
		
		$orders = Mage::getSingleton('sales/order')
				->getCollection()
				->addAttributeToFilter('store_id', $stores)
				->addAttributeToFilter('created_at', array('from'=>$from_date, 'to'=>$to_date));
				
		if($limit){
			$orders->setPageSize($limit);
		}
		
		if($page){
			$orders->setCurPage($page);
		}
		
		if(is_array($field_filter)){
			foreach($field_filter as $key=>$value){
				$orders->addFieldToFilter($key, $value);
			}
		}
		
		if($only_get_count){
			return $orders->getSize();
		}
		
		return $orders;
	}
	
	public function requestCollect($get_data){
		try{
			
			$enable_request_report = Mage::getStoreConfig('get4cast/default/enable_request_report');
			if(!$enable_request_report){
				throw new Exception('Request forecast is disabled in this platform');
			}
						
			$api_reponse = '';
			if(!$get_data){
				return Mage::helper('core')->jsonEncode($api_reponse);
			}

			$from_date = $get_data['h_historical_date_start'];
			$to_date = $get_data['h_historical_date_end'];
			if($get_data['h_data_type'] == 'result'){
				$from_date = $get_data['h_forecast_date_start'];
				$to_date = $get_data['h_forecast_date_end'];
			}
			
			$limit = $get_data['limit'];
			$page = $get_data['page'];
			
			$orders = $this->getOrders($get_data['store_group_id']
						, $from_date
						, $to_date
						, $limit
						, $page
						);
				
			$report_data = array();
			$report_data['metadata'] = array();
			$report_data['metadata']['limit'] = $limit;
			$report_data['metadata']['page'] = $page;
			$report_data['metadata']['pages'] = $get_data['pages'];
			$report_data['metadata']['currency'] = Mage::app()->getStore()->getCurrentCurrencyCode();
			$report_data['metadata']['type'] = $get_data['h_data_type'];
			$report_data['metadata']['report_key'] = $get_data['h_report_key'];
			$report_data['metadata']['forecast_quote_key'] = $get_data['h_forecast_quote_key'];
			
			$store_timezone = Mage::getStoreConfig('general/locale/timezone');
			$report_data['metadata']['timezone'] = $store_timezone;
			
			$time_zone = new DateTimeZone($store_timezone);
			$stock_qty = array();

			foreach($orders as $order){
				$order_info = array();
				$get_data_order = $order->getData();
				$order_info['id'] = $order->getId();
				
				$date = new DateTime($order->getCreatedAt().' GMT');
				$date->setTimezone($time_zone);
				$order_info['created_at_gmt'] = $order->getCreatedAt();
				$order_info['created_at_locale'] = $date->format('Y-m-d H:i:s');
				
				$date = new DateTime($order->getUpdatedAt().' GMT');
				$date->setTimezone($time_zone);
				$order_info['updated_at_gmt'] = $order->getUpdatedAt();
				$order_info['updated_at_locale'] = $date->format('Y-m-d H:i:s');
				
				$order_info['cupom_code'] = $order->getCupomCode();
				$order_info['state'] = $order->getState();
				$order_info['store_id'] = $order->getStoreId();
				$order_info['base_grand_total'] = number_format($get_data_order['base_grand_total'], 2, '.', '');
				$order_info['payment_discount'] = number_format($order->getPaymentDiscount(), 2, '.', '');
				$order_info['shipping_amount'] = $order->getShippingAmount();
				$order_info['increment_id'] = $order->getIncrementId();
				$order_info['base_currency_code'] = $order->getBaseCurrencyCode();
				$order_info['total_item_count'] = $order->getTotalItemCount();
				
				$payment_data = $order->getPayment()->getData();
				$payment_info = array();
				$payment_info['additional_data'] = $payment_data['additional_data'];
				$payment_info['method'] = $payment_data['method'];
				$order_info['payment_data'] = $payment_info;
				
				$status_history = array();
				$order_status_history = $order->getStatusHistoryCollection()->setOrder('created_at', 'DESC');

				if($order->getState() == 'canceled' && $order_status_history){
					foreach($order_status_history as $history){
						$index = $history->getEntityId();
						$status_history_aux[$index] = array();
						$status_history_aux[$index]['status'] = $history->getStatus();
						$status_history_aux[$index]['created_at'] = $history->getCreatedAt();
						$status_history_aux[$index]['entity_id'] = $history->getEntityName();
					}
					ksort($status_history_aux);
					reset($status_history_aux);
					$status_history[0] = current($status_history_aux);
					$status_history[1] = end($status_history_aux);
				}
				$order_info['status_history'] = $status_history;
				
				$order_info['items'] = array();
				$items = $order->getItemsCollection();
				foreach($items as $item){
					$item_info = array();
					$get_data = $item->getData();
					
					if(!isset($stock_qty[$item->getSku()])){
						$_product = Mage::getModel('catalog/product')->loadByAttribute('sku', $item->getSku());					
						$_stock_item = Mage::getModel('cataloginventory/stock_item')->loadByProduct($_product);
						$stock_qty[$item->getSku()] = (int)$_stock_item->getQty();
					}

					$item_info['id'] = $item->getId();
					$item_info['name'] = $item->getName();
					$item_info['sku'] = $item->getSku();
					$item_info['qty_ordered'] = $get_data['qty_ordered'];
					$item_info['price_incl_tax'] = number_format($item->getPriceInclTax(), 2, '.', '');
					$item_info['weight'] = $item->getWeight();
					$item_info['stock_qty'] = $stock_qty[$item->getSku()];
					$order_info['items'][] = $item_info;
				}

				$customer_info = array();
				
				if($order->getCustomerId()){
					$_customer = Mage::getModel('customer/customer')->load($order->getCustomerId());
					$customer_info['email'] = $order->getCustomerEmail();
					$customer_info['id'] = $order->getCustomerId();
					$customer_info['gender'] = $_customer->getResource()->getAttribute('gender')->getSource()->getOptionText($_customer->getData('gender'));
					$customer_info['is_guest'] = 0;
					$customer_info['day_of_birth'] = substr($_customer->getDob(), 0, 10);
				}
				else{
					$customer_info['email'] = $order->getBillingAddress()->getEmail();
					$customer_info['id'] = null;
					$customer_info['gender'] = null;
					$customer_info['is_guest'] = 1;
					$customer_info['day_of_birth'] = null;
				}
				
				$customer_info['shipping_address'] = array();
				$customer_info['shipping_address']['zipcode'] = $order->getShippingAddress()->getPostcode();
				$customer_info['shipping_address']['country'] = $order->getShippingAddress()->getCountry();
				$customer_info['shipping_address']['region'] = $order->getShippingAddress()->getRegion();
				$customer_info['shipping_address']['city'] = $order->getShippingAddress()->getCity();

				$customer_info['billing_address'] = array();
				$customer_info['billing_address']['zipcode'] = $order->getBillingAddress()->getPostcode();
				$customer_info['billing_address']['country'] = $order->getBillingAddress()->getCountry();
				$customer_info['billing_address']['region'] = $order->getBillingAddress()->getRegion();
				$customer_info['billing_address']['city'] = $order->getBillingAddress()->getCity();
				
				$order_info['customer'] = $customer_info;

				$report_data['data']['orders'][] = $order_info;
			}

			$api_client = Mage::getModel('get4cast_salesforecast/apiclient');
			$api_reponse = $api_client->requestSend($report_data);
	
			return Mage::helper('core')->jsonEncode($api_reponse);
			
		} catch (Exception $e) {
			Mage::log('G4C-MAG-X0j3: '.$e->getMessage());
			return false;
		}
	}
}

