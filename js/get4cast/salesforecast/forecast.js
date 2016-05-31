jQuery.noConflict();
var first_validation = true; 
var request_sent = false;
var count_dates_error = 0;

var global_account_info_json = null
var global_ajax_url = null
var global_request_urls = null;
var global_split_pages = null;
var global_split_per_page = null;
var global_request_collect_error_msg = 0;
var global_tmp_final_balance = null;

function callCheckReportDetails(account_info_json, ajax_url){
	global_account_info_json = account_info_json;
	
	account_info_json = JSON.parse(account_info_json);
	if(global_tmp_final_balance != null){
		account_info_json.account_balance = global_tmp_final_balance;
	}
	
	global_ajax_url = ajax_url;
	
	disableRequestButton();
	checked = checkReportDetails(account_info_json);
	if(checked.type == 'ok'){
		if(request_sent){
			return true;
		}
		request_sent = true;
		jQuery.ajax(ajax_url, {
			type: 'GET',
			data: {'data': formToJson()},
			beforeSend: function() {
				jQuery('#loading-mask').show();
				updateRequestForecastInfo('info', Translator.translate('Loading...'), 'request_forecast_price_info');
			},
			success: function(data) {
				jQuery('#loading-mask').hide();
				request_sent = false;
				data = JSON.parse(data);
				if(typeof(data) != 'object' || !data.original_price || data.error){
					msg = 'Unable to get forecast price';
					if(data.error){
						msg = data.error;
					}
					msg = Translator.translate(msg);
					updateRequestForecastInfo('error', msg, 'request_forecast_price_info');
					return false;
				}
				if(data.original_price){
					
					payment_ling_msg_info = Translator.translate('The payment link will be available');
					payment_ling_msg_info += '<br>';
					payment_ling_msg_info += Translator.translate('when you open your report and in your email.');
					
					msg = '<div style=\'color:black;font-weight:normal;font-size:12px\'>';
							if(parseFloat(account_info_json.account_balance) > 0){
								msg += '<table>';
								msg += '<tr>';
									msg += '<td>'+Translator.translate('You have a credit of:')+'</td>';
									msg += '<td>+ $'+account_info_json.account_balance+'</td>';
								msg += '</tr>';						
								msg += '<tr>';
									msg += '<td style=\'border-bottom: solid 1px silver\'>'+Translator.translate('Forecast original price:')+'</td>';
									msg += '<td style=\'border-bottom: solid 1px silver\'>- $'+data.original_price+'</td>';
								msg += '</tr>';
								msg += '<tr>';
									msg += '<td style=\'font-weight:bold\'>'+Translator.translate('Forecast final price')+':</td>';
									if(data.final_balance > 0 || data.final_price <= 0){
										msg += '<td>';
											msg += '<span style=\'font-weight:bold\'>FREE</span>';
											if(data.final_balance > 0){
												msg += '<br>';
												msg += Translator.translate('You will still have<br>a credit of')+' $'+data.final_balance;
											}
										msg += '</td>';
									} else {
										msg += '<td>';
											msg += '<span style=\'font-weight:bold\'>$'+data.final_price+'</span>';
											msg += '<br>';
											msg += payment_ling_msg_info;
										msg += '</td>';
									}
								msg += '</tr>';
								msg += '</table>';
							} else {
								msg += '<span style=\'font-weight:bold\'>'
									msg += Translator.translate('Forecast price')+': $'+data.original_price;
								msg += '</span>'
								msg += '<br>';
								msg += payment_ling_msg_info;									
							}
						
					msg += '</div>';

					jQuery('#h_forecast_quote_key').val(data.forecast_quote_key);
					
					global_split_pages = data.split_pages;
					global_split_per_page = data.split_per_page;
					global_data_size = data.data_size;
						
					updateRequestForecastInfo('ok', msg, 'request_forecast_price_info');
					
					return true;
				}
			},
			error: function() {
				jQuery('#loading-mask').hide();
				request_sent = false;
				msg = Translator.translate('Unable to get forecast price');
				msg += ' ';
				msg += Translator.translate('Try again or');
				msg += ' ';
				msg += '<a target=\'_blank\' href=\''+account_info_json.core_config.url_contact_us_error+'\'>';
					msg += Translator.translate('contact us');
				msg += '</a>.';
				updateRequestForecastInfo('error', msg, 'request_forecast_price_info');
				return false;
			}
		});
	} else {
		updateRequestForecastInfo(checked.type, checked.msg, 'request_forecast_price_info')
	}
}

function checkReportDetails(account_info_json){

	first_validation = false;
	historical_date_start = jQuery('#historical_date_start').val();
	historical_date_end = jQuery('#historical_date_end').val();
	forecast_date_end = jQuery('#forecast_date_end').val();
	email = jQuery('#email').val();

	checked = {}
	checked.type = '';
	checked.msg = '';

	if( typeof(account_info_json.account_balance) == 'undefined'
		|| typeof(account_info_json.core_config) == 'undefined'
	){
		checked.type = 'error';
		checked.msg = Translator.translate('Could not retrieve account information');
		return checked;
	}

	if(!email){
		jQuery('#email').focus();
		checked.type = 'error';
		checked.msg = Translator.translate('Your email is required');
		return checked;
	}
	
	pattern = /^([a-z\d!#$%&'*+\-\/=?^_`{|}~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]+(\.[a-z\d!#$%&'*+\-\/=?^_`{|}~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]+)*|"((([ \t]*\r\n)?[ \t]+)?([\x01-\x08\x0b\x0c\x0e-\x1f\x7f\x21\x23-\x5b\x5d-\x7e\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]|\\[\x01-\x09\x0b\x0c\x0d-\x7f\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))*(([ \t]*\r\n)?[ \t]+)?")@(([a-z\d\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]|[a-z\d\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF][a-z\d\-._~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]*[a-z\d\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])\.)+([a-z\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]|[a-z\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF][a-z\d\-._~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]*[a-z\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])\.?$/i;
    validate_email = pattern.test(email);
    if(!validate_email){
		checked.type = 'error';
		checked.msg = Translator.translate('Your email is invalid');
		return checked;
	}
	
	if(!historical_date_start){
		jQuery('#historical_date_start').focus();
		checked.type = 'error';
		checked.msg = Translator.translate('Analyse from day is required');
		return checked;
	}

	if(!historical_date_end){
		jQuery('#historical_date_end').focus();
		checked.type = 'error';
		if(!first_validation){
			checked.type = 'error';
			checked.msg = Translator.translate('Analyse to day is required');
		}
		return checked;
	}
	
	if(!forecast_date_end){
		jQuery('#forecast_date_end').focus();
		checked.type = 'error';
		checked.msg = Translator.translate('Forecast until day is required');
		return checked;
	}
	
	date_format = jQuery('#h_date_format').val();
	date_format = date_format.split('/');
	if(date_format.length != 3){
		checked = {}
		checked.type = 'error';
		checked.msg = Translator.translate('Invalid date format');
		return checked;
	}
	
	index_day = null;
	index_month = null;
	index_year = null;
	for(i=0;i<=2;i++){
		if(date_format[i].toLowerCase() == 'd' || date_format[i].toLowerCase() == 'dd'){
			index_day = i;
		} else if(date_format[i].toLowerCase() == 'm' || date_format[i].toLowerCase() == 'mm'){
			index_month = i;
		} else if(date_format[i].toLowerCase() == 'y'){
			index_year = i;
		}
	}
	required_info = (index_day != null 
					&& index_month !=null
					&& index_year != null);

	if(!required_info){
		checked = {}
		checked.type = 'error';
		checked.msg = Translator.translate('Invalid date format...');
		return checked;
	}

	historical_date_start = historical_date_start.split('/');
	
	aux_two_digit_year = parseInt(historical_date_start[index_year]);
	if(aux_two_digit_year <= 99){
		historical_date_start[index_year] = twoDigitYearToFour(aux_two_digit_year);
	}
	
	aux_historical_date_start = parseInt(historical_date_start[index_year])
								+'/'+parseInt(historical_date_start[index_month])
								+'/'+parseInt(historical_date_start[index_day])
	jQuery('#h_historical_date_start').val(aux_historical_date_start);
								
	historical_date_end = historical_date_end.split('/');
	
	aux_two_digit_year = parseInt(historical_date_end[index_year]);
	if(aux_two_digit_year <= 99){
		historical_date_end[index_year] = twoDigitYearToFour(aux_two_digit_year);
	}
	
	aux_historical_date_end = parseInt(historical_date_end[index_year]);
	aux_historical_date_end += '/';
	aux_historical_date_end += parseInt(historical_date_end[index_month]);
	aux_historical_date_end += '/';
	aux_historical_date_end += parseInt(historical_date_end[index_day]);
	
	jQuery('#h_historical_date_end').val(aux_historical_date_end);
	var aux_historical_date_start = new Date(aux_historical_date_start);
    var aux_historical_date_end = new Date(aux_historical_date_end);
    
    if(parseInt(historical_date_start[index_day]) > 31
		|| parseInt(historical_date_start[index_month]) > 12)
	{
		checked = {}
		checked.type = 'error';
		checked.msg = Translator.translate('Analyse from day invalid date');
		return checked;
	}
	
	if(parseInt(historical_date_end[index_day]) > 31
		|| parseInt(historical_date_end[index_month]) > 12)
	{
		checked = {}
		checked.type = 'error';
		checked.msg = Translator.translate('Analyse to day invalid date');
		return checked;
	}
    
	hist_days_diff = subtractDates(aux_historical_date_start, aux_historical_date_end);
	jQuery('#h_historical_diff').val(hist_days_diff);

	if(hist_days_diff <= 0){
		count_dates_error++;
		checked.type = 'error';
		checked.msg = Translator.translate('Analyse from day must be before Analyse to day');
		return checked;
	}
	
	today_date = new Date();
	today = [];
	today[index_day] = parseInt(today_date.getDate());
	today[index_month] = parseInt(today_date.getMonth()+1);
	today[index_year] = parseInt(today_date.getFullYear());
	aux_today = parseInt(today[index_year])
								+'/'+parseInt(today[index_month])
								+'/'+parseInt(today[index_day])
	var aux_today = new Date(aux_today);
	
	forecast_date_end = forecast_date_end.split('/');
	
	aux_two_digit_year = parseInt(forecast_date_end[index_year]);
	if(aux_two_digit_year <= 99){
		forecast_date_end[index_year] = twoDigitYearToFour(aux_two_digit_year);
	}
	
	aux_forecast_date_end = parseInt(forecast_date_end[index_year])
								+'/'+parseInt(forecast_date_end[index_month])
								+'/'+parseInt(forecast_date_end[index_day])
	jQuery('#h_forecast_date_end').val(aux_forecast_date_end);
	var aux_forecast_date_end = new Date(aux_forecast_date_end);

	days_diff = subtractDates(aux_historical_date_end, aux_today);
	if(days_diff <= 0){
		count_dates_error++;
		checked.type = 'error';
		checked.msg = Translator.translate('Analyse to day must be in the past');
		return checked;
	}
	
	forecast_days_diff = subtractDates(aux_today, aux_forecast_date_end);
	jQuery('#h_forecast_diff').val(forecast_days_diff);
	if(forecast_days_diff <= 0){
		count_dates_error++;
		checked.type = 'error';
		checked.msg = Translator.translate('Forecast until day must be in the future');
		return checked;
	}
	
	if(account_info_json.core_config.limit_min_historic_days != 0
		&& hist_days_diff < account_info_json.core_config.limit_min_historic_days)
	{
		count_dates_error++;
		checked.type = 'error';
		
		msg = '';
		msg += Translator.translate('To have a minimum acceptable precision you must:');
		msg += '<br>';
		msg += '- '+Translator.translate('Select at least')+' '+account_info_json.core_config.limit_min_historic_days;
		msg += ' ';
		msg += Translator.translate('days of historical data to be analysed.');
		msg += '<br>';
		msg += Translator.translate('See');
		msg += ' ';
		msg += '<a target=\'_blank\' href=\''+account_info_json.core_config.url_help_tips_about_precision+'\'>';
			msg += Translator.translate('how to increase your forecast accuracy');
		msg += '</a>.';
		checked.msg = msg;
		
		return checked;
	}
	
	if(account_info_json.core_config.limit_historic_days != 0
		&& hist_days_diff > account_info_json.core_config.limit_historic_days)
	{
		count_dates_error++;
		checked.type = 'error';
		msg = Translator.translate('There is a limit of');
		msg += ' '+account_info_json.core_config.limit_historic_days+' ';
		msg += Translator.translate('days of historical data that can be analysed.');
		msg += '<br>';
		msg += Translator.translate('Please, select a smaller period.');
		msg += '<br>';
		msg += Translator.translate('If you need to analyze more data');
		msg += ' ';
		msg += '<a target=\'_blank\' href=\''+account_info_json.core_config.url_contact_us_custom+'\'>';
			msg += Translator.translate('contact us');
		msg += '</a>';
		msg += ' ';
		msg += Translator.translate('for a custom service.');
		checked.msg = msg;
		return checked;
	}
	
	
	if(account_info_json.core_config.limit_future_days != 0
		&& forecast_days_diff > account_info_json.core_config.limit_future_days)
	{
		count_dates_error++;
		checked.type = 'error';
		msg = Translator.translate('There is a limit of');
		msg += ' '+account_info_json.core_config.limit_future_days+' ';
		msg += Translator.translate('days for forecast.');
		msg += '<br>';
		msg += Translator.translate('Please, select a smaller period.');
		msg += '<br>';
		msg += Translator.translate('If you need to process more data');
		msg += ' ';
		msg += '<a target=\'_blank\' href=\''+account_info_json.core_config.url_contact_us_custom+'\'>';
			msg += Translator.translate('contact us');
		msg += '</a>';
		msg += ' ';
		msg += Translator.translate('for a custom service.');
		checked.msg = msg;
		return checked;
	}
	
	percent_historic_forecast = hist_days_diff/forecast_days_diff;
	percent_historic_forecast = percent_historic_forecast * 100;
	if(percent_historic_forecast < account_info_json.core_config.limit_percent_historic_forecast){
		count_dates_error++;
		checked.type = 'error';
		msg = '';
		msg += Translator.translate('You are willing to analyse');
		msg += ' ';
		msg += '<strong>';
			msg += hist_days_diff;
			msg += ' ';
			msg += Translator.translate('days');
		msg += '</strong>';	
		msg += ' ';
		msg += Translator.translate('of historical data and to forecast');
		msg += ' ';
		msg += '<strong>';
			msg += forecast_days_diff;
			msg += ' ';
			msg += Translator.translate('days');
		msg += '</strong>.';
		msg += ' ';
		msg += '<br>';
		msg += Translator.translate('To have a minimum acceptable precision you can:');
		msg += '<br>';
		msg += '- '+Translator.translate('Increase the period of historical data to be analysed');
		msg += '<br>';
		msg += '- '+Translator.translate('Decrease the number of forecast days');
		msg += '<br>';
		msg += '<br>';
		msg += Translator.translate('See');
		msg += ' ';
		msg += '<a target=\'_blank\' href=\''+account_info_json.core_config.url_help_tips_about_precision+'\'>';
			msg += Translator.translate('how to increase your forecast accuracy');
		msg += '</a>.';
		checked.msg = msg;
		return checked;
	}

	if(historical_date_start
		&& historical_date_end
		&& forecast_date_end)
	{
		checked.type = 'ok';
	}

	return checked;
}

function bestFit(){
	count_dates_error = 0;

	if(request_sent){
		return true;
	}
	request_sent = false;
	
	request_urls = JSON.parse(global_request_urls);
	ajax_url = request_urls.request_best_fit;

	jQuery.ajax(ajax_url, {
		type: 'GET',
		async: false,
		data: {'data': formToJson()},
		beforeSend: function() {
			jQuery('#loading-mask').show();
		},
		success: function(data) {
			jQuery('#loading-mask').hide();
			request_sent = false;
			api_result = JSON.parse(data)
			global_request_collect_error_msg = 0;

			if(api_result["_error"] != undefined){
				global_request_collect_error_msg = api_result["_error"];
			}
			
		},
		error: function(data) {
			jQuery('#loading-mask').hide();
			request_sent = false;
			global_request_collect_error_msg = data.result;
			info_id = jQuery('#h_data_type').val() == 'input' ? 'request_forecast_info' : 'request_result_info';
			updateRequestForecastInfo('error', Translator.translate('Request error'), info_id);
		}
	});
	
	jQuery('#historical_date_start').val(formatDate(best['historic_start']));
	jQuery('#historical_date_end').val(formatDate(best['historic_end']));
	jQuery('#forecast_date_end').val(formatDate(best['forecast_date_end']));
	callCheckReportDetails(global_account_info_json, global_ajax_url)
}
	

function requestValidate(request_urls){
	global_request_urls = request_urls;

	if(request_sent){
		return true;
	}
	request_sent = true;
	request_urls = JSON.parse(global_request_urls);
	ajax_url = request_urls.request_validate_url;
	jQuery.ajax(ajax_url, {
		type: 'GET',
		data: {'data': formToJson()},
		beforeSend: function() {
			disableRequestButton();
			jQuery('#loading-mask').show();
			info_id = jQuery('#h_data_type').val() == 'input' ? 'request_forecast_info' : 'request_result_info';
			updateRequestForecastInfo('info', Translator.translate('Validating data...'), info_id);
		},
		success: function(data) {
			data = JSON.parse(data);
			info_id = jQuery('#h_data_type').val() == 'input' ? 'request_forecast_info' : 'request_result_info';
			if(data.type == 0 || !global_split_pages){
				disableRequestButton();
				jQuery('#loading-mask').hide();
				updateRequestForecastInfo('error', Translator.translate('Validation error'), info_id);
			} else {
				request_sent = false;
				msg = Translator.translate('Collecting, splitting and sending data...');

				for(i=1;i<=global_split_pages;i++){				
					
					msg_count = ' [ '+i+' / '+global_split_pages+' ]';
					
					updateRequestForecastInfo('info', msg+msg_count, info_id);
					
					requestCollect(global_split_per_page, i);
					
					if(global_request_collect_error_msg != 0){
						jQuery('#loading-mask').hide();
						updateRequestForecastInfo('error', Translator.translate('Unable to request'), info_id);
						return false;
					}
				}
				
				if(global_tmp_final_balance >= 0){
					jQuery('#you_have_a_credit_of').html(global_tmp_final_balance);
				} else {
					jQuery('#you_have_a_credit_of').html('0.00');
				}
				
				msg = Translator.translate('Your request was successfully received.');
				msg += '<br>';
				msg += Translator.translate('We will process your data and notify you when everything is done.');
				msg += '<br>';
				msg += Translator.translate('You can check your report status in Forecast history page.');
				
				updateRequestForecastInfo('ok', msg, info_id, true);
			}
			
		},
		error: function(data) {
			jQuery('#loading-mask').hide();
			request_sent = false;
			info_id = jQuery('#h_data_type').val() == 'input' ? 'request_forecast_info' : 'request_result_info';
			updateRequestForecastInfo('error', Translator.translate('Request error'), info_id);
		}
	});
}

function requestCollect(limit, page){
	if(request_sent){
		return true;
	}
	request_sent = false;
	
	request_urls = JSON.parse(global_request_urls);
	ajax_url = request_urls.request_collect_url;

	jQuery.ajax(ajax_url, {
		type: 'GET',
		async: false,
		data: {'data': formToJson(), 'limit':limit, 'page':page, 'data_size':global_data_size, 'pages':global_split_pages},
		beforeSend: function() {
			jQuery('#loading-mask').show();
		},
		success: function(data) {
			jQuery('#loading-mask').hide();
			request_sent = false;
			api_result = JSON.parse(data)
			global_request_collect_error_msg = 0;

			if(api_result["_error"] != undefined){
				global_request_collect_error_msg = api_result["_error"];
			} else {
				global_tmp_final_balance = api_result.final_balance;
			}
			
		},
		error: function(data) {
			jQuery('#loading-mask').hide();
			request_sent = false;
			global_request_collect_error_msg = data.result;
			info_id = jQuery('#h_data_type').val() == 'input' ? 'request_forecast_info' : 'request_result_info';
			updateRequestForecastInfo('error', Translator.translate('Request error'), info_id);
		}
	});
}

function formatDate(original_date){
	date_format = jQuery('#h_date_format').val();
	date_format = date_format.split('/');
	if(date_format.length != 3){
		return false;
	}
	
	original_date = original_date.split('/');
	formated_date = '';
	for(i=0;i<=2;i++){
		if(date_format[i].toLowerCase() == 'd' || date_format[i].toLowerCase() == 'dd'){
			formated_date += original_date[2]+'/';
		} else if(date_format[i].toLowerCase() == 'm' || date_format[i].toLowerCase() == 'mm'){
			formated_date += original_date[1]+'/';
		} else if(date_format[i].toLowerCase() == 'y'){
			formated_date += original_date[0];
		}
	}
	
	return formated_date;
}

function subtractDates(date1, date2){

    var diff = (date2 - date1)/1000;
    var diff = Math.floor(diff);

    var days = Math.floor(diff/(24*60*60));
    var leftSec = diff - days * 24*60*60;

    var hrs = Math.floor(leftSec/(60*60));
    var leftSec = leftSec - hrs * 60*60;

    var min = Math.floor(leftSec/(60));
    var leftSec = leftSec - min * 60;
	
	return days;
}

function formToJson(){
	var formFields = {};
	formFields = {}
	var forms = document.getElementById('edit_form').elements
	for(var i = 0; i < forms.length; i++){
		formFields[forms[i].id] = forms[i].value;
	}
	return JSON.stringify(formFields)
}

function updateRequestForecastInfo(type, msg, element_id, disable_request_forecast = false){
	title_color = '';
	title = '';
	if(type == 'error'){
		title_color = 'red';
		title = Translator.translate('Error');
		disableRequestButton();
	}
	if(type == 'info'){
		title_color = 'blue';
		title = Translator.translate('Info');
	}
	if(type == 'ok'){
		title_color = 'green';
		title = Translator.translate('Ok!');
		enableRequestButton();
	}
	
	if(disable_request_forecast){
		disableRequestButton();
	}
	
	info_html = '';
	info_html += '<br>';
	info_html += '<span style="color:'+title_color+';font-size:12px;font-weight:bold;">';
		info_html += title;
	info_html += '</span>';
	info_html += '<br>';
	info_html += '<span style="color:black;font-weight:normal;font-size:12px">';
		info_html += msg;
	info_html += '</span>';
	
	jQuery('#'+element_id).html(info_html);
}

function disableRequestButton(show_tip = true, button_id = 'request_forecast'){
	jQuery('#'+button_id).attr("style", "border:none;background-color:silver;background-image:none");
	jQuery('#'+button_id).prop("disabled",true);
	if(show_tip){
		jQuery('#request_forecast_info').html('<br>'+Translator.translate('Click on Step 1: Check forecast price before'));
	} else {
		jQuery('#request_forecast_info').hide();
	}
}

function enableRequestButton(){
	if(global_enable_request_report){
		jQuery('#request_forecast').removeAttr("style");
		jQuery('#request_forecast').prop("disabled",false);
		info_html = '<br>';
		info_html += '<span style="color:green;font-size:12px;font-weight:bold">';
			info_html += Translator.translate('Ready to go!');
		info_html += '</span>';
	} else {
		info_html = '<br>';
		info_html += '<span style="color:red;font-size:12px;font-weight:bold">';
			info_html += 'Request forecast is disabled in this platform.';
		info_html += '</span>';
	}
	jQuery('#request_forecast_info').html(info_html);
}

function twoDigitYearToFour(year){
	aux_two_digit_year = parseInt(year);
	// Magento behavior is to consider any 2-digit year <= 29
	// in 2000's. And > 30 in 1900's
	if(aux_two_digit_year <= 29){
		aux_two_digit_year = aux_two_digit_year+2000;
	} else {
		aux_two_digit_year = aux_two_digit_year+1900;
	}
	
	return aux_two_digit_year;
}
