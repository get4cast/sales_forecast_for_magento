<?php
class Get4Cast_SalesForecast_Model_Resource_Config
    extends Mage_Core_Model_Resource_Db_Abstract
{
    protected function _construct()
    {
        $this->_init('get4cast_salesforecast/config', 'entity_id');
    }
}
