<?php
class Get4Cast_SalesForecast_Block_Adminhtml_Forecast_Edit
    extends Mage_Adminhtml_Block_Widget_Form_Container
{
    protected function _construct()
    {
        $this->_blockGroup = 'get4cast_salesforecast_adminhtml';
        $this->_controller = 'forecast';

        $this->_mode = 'edit';
        
        $newOrEdit = $this->getRequest()->getParam('id')
            ? $this->__('Edit') 
            : $this->__('New');
        $this->_headerText =  $newOrEdit . ' ' . $this->__('Forecast');
    }
    
    protected function _prepareLayout() {
		$this->_removeButton('save');
		$this->_removeButton('delete');
		$this->_removeButton('reset');
		
        return parent::_prepareLayout();
    }
}
