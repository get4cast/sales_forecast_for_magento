<?php

class Get4Cast_SalesForecast_Block_Adminhtml_Url 
	extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
	public function render(Varien_Object $row) {
		$value =  $row->getData($this->getColumn()->getIndex());
        return '<a target=\'_blank\' href="'.$value.'">'.$this->__('Click here').'</a>';
	}
}
