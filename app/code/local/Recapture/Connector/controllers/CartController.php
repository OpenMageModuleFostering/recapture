<?php

class Recapture_Connector_CartController extends Mage_Core_Controller_Front_Action {
    
    public function testAction(){
    
        echo 'Api Key: ' . Mage::helper('recapture')->getApiKey() . '<br>';
        echo 'Enabled: ' . Mage::getStoreConfig('recapture/configuration/enabled') . '<br>';
    
    }
    
    public function indexAction(){
        
        $helper = Mage::helper('recapture');
        if (!$helper->isEnabled() || !$helper->getApiKey()) return $this->_redirect('/');
        
        $hash = $this->getRequest()->getParam('hash');
        
        try {
        
            $cartId = $helper->translateCartHash($hash);
            
        } catch (Exception $e){
            
            Mage::log($e, null, 'recapture.log');
            
        }
        
        if (!$cartId){
            
            Mage::getSingleton('core/session')->addError('There was an error retrieving your cart.');
            return $this->_redirect('/');
            
        }
        
        try {
        
            $result = $helper->associateCartToMe($cartId);
            
        } catch (Exception $e){
            
            Mage::log($e, null, 'recapture.log');
            
        }
        
        if (!$result){
            
            Mage::getSingleton('core/session')->addError('There was an error retrieving your cart.');
            return $this->_redirect('/');
            
        } else {
            
            $cart = Mage::getModel('checkout/cart')->getQuote();
            
            return $this->_redirect('checkout/cart');
            
        }
        
    }
    
}  