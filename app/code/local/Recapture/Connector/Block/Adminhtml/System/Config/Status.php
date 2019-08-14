<?php 

class Recapture_Connector_Block_Adminhtml_System_Config_Status extends Mage_Adminhtml_Block_System_Config_Form_Field {
    
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element) {
        
        $authenticated = Mage::getStoreConfig('recapture/configuration/authenticated', Mage::helper('recapture')->getScopeStoreId());
        
        $image = $this->getSkinUrl('images/' . ($authenticated ? 'success' : 'error') . '_msg_icon.gif');
        $text = '<img style="float: left; margin-right: 6px;" src="' . $image . '" /> ';
        $text .= '<span class="' . ($authenticated ? 'success' : 'error') . '">';
        $text .= $authenticated ? 'Authenticated!' : 'Not Authenticated';
        $text .= '</span>';
        
        return $text;
        
    }
    
}