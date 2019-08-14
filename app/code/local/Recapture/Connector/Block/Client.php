<?php 

class Recapture_Connector_Block_Client extends Mage_Core_Block_Template {
    
    private $_cartId = null;
    
    public function shouldTrack(){
        
        if (!Mage::helper('recapture')->isEnabled()) return false;
        if (!Mage::helper('recapture')->canTrackEmail()) return false;
        
        $apiKey = $this->getApiKey();
        if (empty($apiKey)) return false;
        
        $cartId = $this->getCartId();
        if (empty($cartId)) return false;
        
        return true;
        
    }
    
    public function getApiKey(){
        
        return Mage::helper('recapture')->getApiKey();
        
    }
    
    public function getCartId(){
        
        if (empty($this->_cartId)){
        
            $cart = Mage::getModel('checkout/cart')->getQuote();
            $this->_cartId = $cart->getId();
            
        }
        
        return $this->_cartId;
        
    }
    
}