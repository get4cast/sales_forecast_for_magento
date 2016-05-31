<?php

class Get4Cast_SalesForecast_Block_Adminhtml_Translate 
	extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
	public function render(Varien_Object $row) {
		$value =  $row->getData($this->getColumn()->getIndex());
		return $this->__($value);
	}
}
