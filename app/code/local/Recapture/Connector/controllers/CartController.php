<?php

class Recapture_Connector_CartController extends Mage_Core_Controller_Front_Action {
    
    public function indexAction(){
        
        $helper = Mage::helper('recapture');
        
        $hash = $this->getRequest()->getParam('hash');
        
        $cartId = $helper->translateCartHash($hash);
        
        if (!$cartId){
            
            Mage::getSingleton('core/session')->addError('There was an error retrieving your cart.');
            $this->_redirect('/');
            
        }
        
        $result = $helper->associateCartToMe($cartId);
        
        if (!$result){
            
            Mage::getSingleton('core/session')->addError('There was an error retrieving your cart.');
            $this->_redirect('/');
            
        } else {
            
            $cart = Mage::getModel('checkout/cart')->getQuote();
            
            $this->_redirect('checkout/cart');
            
        }
        
    }
    
}  