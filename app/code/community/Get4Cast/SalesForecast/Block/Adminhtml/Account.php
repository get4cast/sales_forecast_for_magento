<?php

class Get4Cast_SalesForecast_Block_Adminhtml_Account
	extends Mage_Adminhtml_Block_Widget
	implements Varien_Data_Form_Element_Renderer_Interface
{
	public function render(Varien_Data_Form_Element_Abstract $element) {
		$account_info = $this->getData('account_info');
		$account_balance = $account_info['account_balance'];
		$html = '<td class="label">'.$element->getLabelHtml().'</td>';
		$html .= '<td class = "value">';
			$html .= '<ul>';
				if($account_info['account_notifications']){
					foreach($account_info['account_notifications'] as $notification){
						$html .= '<li>'.$notification.'</li>';
					}
				}
			$html .= '</ul>';
		$html .= '</td>';
		
		return $html; 
	}
}
