<?php

class Get4Cast_SalesForecast_Block_Adminhtml_Link
	extends Mage_Adminhtml_Block_Widget
	implements Varien_Data_Form_Element_Renderer_Interface
{
	public function render(Varien_Data_Form_Element_Abstract $element) {
		$forecast = $this->getData('report_link');
		
		$html = '<td class="label">'.$element->getLabelHtml().'</td>';
		$html .= '<td class = "value">';
			$html .= '<ul>';
					$html .= "<a href='".$forecast->getUrl()."' target='_blank'>".$this->__('Click here to open your report')."</a>";
			$html .= '</ul>';
		$html .= '</td>';
		
		return $html; 
	}
}
