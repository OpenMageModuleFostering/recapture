<?php
class Recapture_Connector_Block_Adminhtml_System_Config_Authenticate extends Mage_Adminhtml_Block_System_Config_Form_Field 
implements Varien_Data_Form_Element_Renderer_Interface {
    
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element){
        
        $buttonBlock = Mage::app()->getLayout()->createBlock('adminhtml/widget_button');

        $data = array(
            'label'     => Mage::helper('adminhtml')->__('Authenticate Account'),
            'onclick'   => 'setLocation(\''.Mage::helper('adminhtml')->getUrl("adminhtml/recaptureadmin_authenticate", Mage::helper('recapture')->getScopeForUrl()) . '\' )',
            'class'     => '',
        );

        $html = $buttonBlock->setData($data)->toHtml();

        return $html;
    }
}