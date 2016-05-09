<?php
//Register app
$api_client = Mage::getModel('get4cast_salesforecast/apiclient');
$response = $api_client->register();
