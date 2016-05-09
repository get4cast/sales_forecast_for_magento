<?php
class Get4Cast_SalesForecast_Model_Resource_Forecast_Collection
    extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    protected function _construct()
    {
        parent::_construct();

        $this->_init(
            'get4cast_salesforecast/forecast', 
            'get4cast_salesforecast/forecast'
        );
    }
}
