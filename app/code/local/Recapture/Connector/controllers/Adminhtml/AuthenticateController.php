<?php

class Recapture_Connector_Adminhtml_AuthenticateController extends Mage_Adminhtml_Controller_Action {
    
    public function indexAction(){
        
        $query = http_build_query(array(
            'return'    => Mage::helper('adminhtml')->getUrl('recapture_admin/authenticate/return'),
            'recapture' => Mage::getUrl('recapture/cart/index', array('hash' => 'CART_HASH'))
        ));
        
        $authenticateUrl = 'http://recapture.io/account/auth?' . $query;
        return $this->_redirectUrl($authenticateUrl);
        
    }
    
    public function returnAction(){
        
        $apiKey = $this->getRequest()->getParam('api_key');
        
        if ($apiKey){
        
            $config = new Mage_Core_Model_Config();
            $config->saveConfig('recapture/configuration/authenticated', true, 'default', 0);
            $config->saveConfig('recapture/configuration/api_key', $apiKey, 'default', 0);
            
            Mage::getSingleton('adminhtml/session')->addSuccess('Your account has been authenticated successfully!'); 
            
        } else {
            
            Mage::getSingleton('adminhtml/session')->addError('Unable to authenticate your account. Please ensure you are logged in to your Recapture account.'); 
            
        }
        
        return $this->_redirect('adminhtml/system_config/edit', array('section' => 'recapture'));
        
    }
    
}  