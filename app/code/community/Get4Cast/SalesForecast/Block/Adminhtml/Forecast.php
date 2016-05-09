<?php
class Get4Cast_SalesForecast_Block_Adminhtml_Forecast
    extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    protected function _construct()
    {
        parent::_construct();
        $this->_blockGroup = 'get4cast_salesforecast_adminhtml';
        $this->_controller = 'forecast';
        $this->_headerText = Mage::helper('get4cast_salesforecast')
            ->__('Forecast history');
    }
    
    protected function _prepareLayout() {
		$admin_session = Mage::getSingleton('admin/session');
		if(!$admin_session->isAllowed('admin/report/get4cast_salesforecast/sales_forecast/new_forecast')){
			$this->_removeButton('add');
		}
		
        return parent::_prepareLayout();
    }
    
    public function getCreateUrl()
    {
        return $this->getUrl(
            'get4cast_salesforecast_admin/forecast/edit'
        );
    }
}
