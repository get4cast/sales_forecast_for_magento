<?php
class Get4Cast_SalesForecast_Model_Resource_Forecast
    extends Mage_Core_Model_Resource_Db_Abstract
{
    protected function _construct()
    {
        $this->_init('get4cast_salesforecast/forecast', 'entity_id');
    }
}
