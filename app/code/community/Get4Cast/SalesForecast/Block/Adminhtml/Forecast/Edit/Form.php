<?php
class Get4Cast_SalesForecast_Block_Adminhtml_Forecast_Edit_Form
    extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {

		$helper = Mage::helper('get4cast_salesforecast/data');
		
        // Instantiate a new form
        $form = new Varien_Data_Form(array(
            'id' => 'edit_form',
            'action' => $this->getUrl(
                'get4cast_salesforecast_admin/forecast/edit', 
                array(
                    '_current' => true,
                    'continue' => 0,
                )
            ),
            'method' => 'post',
        ));
        $form->setUseContainer(true);
        $this->setForm($form);
        
        // Define a new fieldset
        $fieldset = $form->addFieldset(
            'general',
            array(
                'legend' => $this->__('Forecast details')
            )
        );
        
        $forecast = Mage::registry('current_forecast');
		$disabled = $forecast->getId() ? true : false;
        
        $account_info = Mage::getSingleton('core/session')
							->getGet4CastAccountInfo();
		if(!$account_info){
			$account_info = array();
		}
							
		$account_info_json = htmlspecialchars(Mage::helper('core')
								->jsonEncode($account_info));
		$account_info_json = str_replace('\'', '\\\'', $account_info_json);
		
		if(isset($account_info['account_notifications'])
			&& count($account_info['account_notifications'])
			&& !$disabled
		){
			// Field to show account information. This field uses a custom
			// renderer Get4Cast_SalesForecast_Block_Adminhtml_Account
			// file: app/code/community/Get4Cast/SalesForecast
			//          /Block/Adminhtml/Account.php
			$fieldset->addField('account', 'text', array(
				'label' => $this->__('Account info'),
				'tabindex' => 1
			));
			$form->getElement('account')->setRenderer( Mage::app()
				->getLayout()
				->createBlock('get4cast_salesforecast_adminhtml/account')
				->setData('account_info', $account_info)
			);
		}
		
		if($forecast->getEntityId()){
			$fieldset->addField('report_link', 'text', array(
				'label' => $this->__('Report link'),
				'tabindex' => 2
			));
			$form->getElement('report_link')->setRenderer( Mage::app()
				->getLayout()
				->createBlock('get4cast_salesforecast_adminhtml/link')
				->setData('report_link', $forecast)
			);
		}
		
		// Select box with store groups
		$store_groups = $helper->getAllStoresToSelect();
		reset($store_groups);
		$first_store = $forecast->getStoreGroupId() ? $forecast->getStoreGroupId() : current($store_groups);
		$fieldset->addField('store_group_id', 'select', array(
			'name' => 'store_group_id',
			'label' => $this->__('Store'),
			'class' => 'required-entry',
			'required' => true,
			'disabled' => false,
			'after_element_html' => '<small>'.$this->__('Forecast for the selected store').'</small>',
			'options' => $store_groups,
			'onchange' => "jQuery('#h_store_group_name').val(jQuery('#store_group_id option:selected').text());",
			'value' => $forecast->getStoreGroupId(),
			'disabled' => $disabled,
		));
		$fieldset->addField('h_store_group_name', 'hidden', array(
			'name'  => 'h_store_group_name',
			'value' => $first_store,
		));
		
		$notify_email_after = $this->__('You will be notified by email when the report is ready');
		$fieldset->addField('email', 'text', array(
			'name'     => 'email',
			'label'    => $this->__('Your email'),
			'class'    => 'required-entry',
			'required' => true,
			'disabled' => false,
			'image'    => $this->getSkinUrl('images/grid-cal.gif'),
			'after_element_html' => '<small>'.$notify_email_after.'</small>',
			'value' => $forecast->getEmail(),
			'disabled' => $disabled,
		));

		// Period start to analyse historical data
		$fieldset->addField('historical_date_start', 'date', array(
			'name'               => 'historical_date_start',
			'label'              => $this->__('Analyse from day'),
			'class'     => 'required-entry',
			'required'  => true,
			'image' => $this->getSkinUrl('images/grid-cal.gif'),
			'after_element_html' => '<small>'.$this->__('Start period of historical data to be analysed').'</small>',
			'format'             => Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT) ,
			'onchange' => 'disableRequestButton()',
			'value' => $forecast->getPeriodStart(),
			'disabled' => $disabled,
		));
		
		// Period end to analyse historical data
		$fieldset->addField('historical_date_end', 'date', array(
			'name'               => 'historical_date_end',
			'label'              => $this->__('Analyse to day'),
			'class'     => 'required-entry',
			'required'  => true,
			'disabled' => false,
			'image' => $this->getSkinUrl('images/grid-cal.gif'),
			'after_element_html' => '<small>'.$this->__('End period of historical data to be analysed').'</small>',
			'format'             => Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT) ,
			'onchange' => 'disableRequestButton()',
			'value' => $forecast->getPeriodEnd(),
			'disabled' => $disabled,
		));

		// Field to select the day to forecast
		$fieldset->addField('forecast_date_end', 'date', array(
			'name'     => 'forecast_date_end',
			'label'    => $this->__('Forecast until day'),
			'class'    => 'required-entry',
			'required' => true,
			'disabled' => false,
			'image'    => $this->getSkinUrl('images/grid-cal.gif'),
			'format'   => Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT) ,
			'onchange' => 'disableRequestButton()',
			'value' => $forecast->getForecastDateEnd(),
			'disabled' => $disabled,
        ))->setAfterElementHtml("
			<small>
				<span id='forecast_ahead_info'>"
					.$this->__('Forecast from today until the selected day').
				"</span>
			</small>
		");
		
		$after_html = "";	
		$after_html .= "<small>
							<span id='request_forecast_price_info' style='font-weight:bold'><br></span>
						</small>";
		if(!$disabled){
			$after_html .= "<script type=\"text/javascript\">
					</script>";
		}
		$fieldset->addField(
			'request_forecast_price', 'button', array(
				'name' => 'request_forecast_price',
				'label' => $this->__('Check forecast price'),
				'value' => $this->__('Step 1: Check forecast price'),
				'class' => 'form-button',
				'onclick' => 'callCheckReportDetails(
								\''.$account_info_json.'\'
								,\''.$this->getUrl('get4cast_salesforecast_admin/forecast/getForecastPrice').'\')',
			)
        )->setAfterElementHtml($after_html);
		
		// Button to call ajax functions to request forecast
		$request_urls = array();
		$request_urls['request_validate_url'] = $this->getUrl('get4cast_salesforecast_admin/forecast/requestValidate');
		$request_urls['request_analyse_data_base_size_url'] = $this->getUrl('get4cast_salesforecast_admin/forecast/requestAnalyseDataBaseSize');
		$request_urls['request_collect_url'] = $this->getUrl('get4cast_salesforecast_admin/forecast/requestCollect');
		$request_urls['request_split_url'] = $this->getUrl('get4cast_salesforecast_admin/forecast/requestSplit');
		$request_urls['request_send_url'] = $this->getUrl('get4cast_salesforecast_admin/forecast/requestSend');
		$request_urls['request_best_fit'] = $this->getUrl('get4cast_salesforecast_admin/forecast/requestBestFit');
		
		$request_urls = htmlspecialchars(Mage::helper('core')
								->jsonEncode($request_urls));
		
		$after_html = "";
		$after_html .= "<small>
							<span id='request_forecast_info' style=''></span>
						</small>";
		$after_html .= "<script type=\"text/javascript\">
							disableRequestButton();
						</script>";
		if($disabled){
			$after_html .= "<script type=\"text/javascript\">
							disableRequestButton(false, 'request_forecast_price');
						</script>";
		}
		$fieldset->addField(
			'request_forecast', 'button', array(
				'name' => 'request_forecast',
				'label' => $this->__('Request forecast'),
				'value' => $this->__('Step 2: Request forecast'),
				'class' => 'form-button',
				'onclick' => 'requestValidate(\''.$request_urls.'\')',
				'disabled' => true,
			)
        )->setAfterElementHtml($after_html);
        
        if($forecast->getStatus() == 'request result' || $forecast->getStatus() == 'result requested' || $forecast->getStatus() == 'complete'){

			$after_html = "";
			$after_html .= "<small>
							<span id='request_result_info' style=''></span>
						</small>";
			
			$disable_result = $forecast->getStatus() == 'request result' ? false : true;
			if($disable_result){
			$after_html .= "<script type=\"text/javascript\">
								disableRequestButton(false, 'request_result');
							</script>";
			}
			
					
			$fieldset->addField(
				'request_result', 'button', array(
					'name' => 'request_result',
					'label' => $this->__('Request result comparison'),
					'value' => $this->__('Step 3: Request result'),
					'class' => 'form-button',
					'onclick' => 'disableRequestButton(false, \'request_result\');requestValidate(\''.$request_urls.'\')',
					'disabled' => $disable_result,
				)
			)->setAfterElementHtml($after_html);
		}
		
		$fieldset->addField('h_report_key', 'hidden', array(
			'name'  => 'h_report_key',
			'value' => $forecast->getReportKey(),
		));
		
		$fieldset->addField('h_forecast_quote_key', 'hidden', array(
			'name'  => 'h_forecast_quote_key',
			'value' => '',
		));
		
		$h_data_type = $disabled ? 'result' : 'input';
		$fieldset->addField('h_data_type', 'hidden', array(
			'name'  => 'h_data_type',
			'value' => $h_data_type,
		));
		
		$h_forecast_date_start = $forecast->getId() ? substr($forecast->getForecastDateStart(), 0, 10) : date('Y-m-d');
		$h_forecast_date_end = $forecast->getId() ? substr($forecast->getForecastDateEnd(), 0, 10) : '';
		$fieldset->addField('h_forecast_date_start', 'hidden', array(
			'name'  => 'h_forecast_date_start',
			'value' => $h_forecast_date_start,
		));
		
		$after_html = "";
		if($disabled){
			if($h_forecast_date_start
				&& $h_forecast_date_end
				&& isset($account_info['core_config']['limit_data_split']))
			{
				$collect = Mage::getModel('get4cast_salesforecast/collect');
				$count_orders = $collect->getOrders(1 // store group ID
									, $h_forecast_date_start
									, $h_forecast_date_end
									, false // limit
									, false // page
									, false // filters
									, true); // get only count
						
				$data_size = $count_orders;
			
				$split_pages = round($data_size / $account_info['core_config']['limit_data_split']);
				if ($split_pages == 0) {
					$split_pages = 1;
				}
				
				$split_per_page = round($data_size / $split_pages);
				
				$after_html .= "<script type=\"text/javascript\">
									global_split_pages = ".$split_pages.";
									global_split_per_page = ".$split_per_page.";
									global_data_size = ".$data_size.";
								</script>";
			}
		}
		
		$enable_request_report = Mage::getStoreConfig('get4cast/default/enable_request_report');
		
		$after_html .= "<script type=\"text/javascript\">
							global_enable_request_report = ".$enable_request_report.";
						</script>";

		$fieldset->addField('h_forecast_date_end', 'hidden', array(
			'name'  => 'h_forecast_date_end',
			'value' => $h_forecast_date_end,
		))->setAfterElementHtml($after_html);
		

		$fieldset->addField('h_historical_date_start', 'hidden', array(
			'name'  => 'h_historical_date_start',
			'value' => '',
		));
		$fieldset->addField('h_historical_date_end', 'hidden', array(
			'name'  => 'h_historical_date_end',
			'value' => '',
		));
		$fieldset->addField('h_historical_diff', 'hidden', array(
			'name'  => 'h_historical_diff',
			'value' => '',
		));
		$fieldset->addField('h_forecast_diff', 'hidden', array(
			'name'  => 'h_forecast_diff',
			'value' => '',
		));

		$fieldset->addField('h_date_format', 'hidden', array(
			'name'  => 'h_date_format',
			'value' => $helper->getDateFormat(),
		));
		
		$limit_best_fit_historical_days = '';
		if(isset($account_info['core_config']['limit_best_fit_historical_days'])){
			$limit_best_fit_historical_days = $account_info['core_config']['limit_best_fit_historical_days'];
		}
		$fieldset->addField('h_limit_best_fit_historical_days', 'hidden', array(
			'name'  => 'h_limit_best_fit_historical_days',
			'value' => $limit_best_fit_historical_days,
		));
		
        
        if (Mage::getSingleton('adminhtml/session')->getFondationData()) {
			$form->setValues(Mage::getSingleton('adminhtml/session')->getFondationData());
			Mage::getSingleton('adminhtml/session')->setFondationData(null);
		} elseif (Mage::registry('fondation_data')) {
			$form->setValues(Mage::registry('fondation_data')->getData());
		}

        return $this;
    }
}
