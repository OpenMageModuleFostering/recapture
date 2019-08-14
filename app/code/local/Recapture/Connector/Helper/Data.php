<?php

class Recapture_Connector_Helper_Data extends Mage_Core_Helper_Abstract {
    
    public function isEnabled(){
        
        return Mage::getStoreConfig('recapture/configuration/enabled');
        
    }
    
    public function getApiKey(){
        
        return Mage::getStoreConfig('recapture/configuration/api_key');
        
    }
    
    public function getScopeStoreId(){
        
        $website = Mage::app()->getRequest()->getParam('website') ? Mage::app()->getRequest()->getParam('website') : Mage::getSingleton('adminhtml/config_data')->getWebsite();
        $store   = Mage::app()->getRequest()->getParam('store') ? Mage::app()->getRequest()->getParam('store') : Mage::getSingleton('adminhtml/config_data')->getStore();
        
        if (!$website && !$store) return '0';
        
        if ($store) return Mage::getModel('core/store')->load($store)->getId();
        if ($website) return Mage::getModel('core/website')->load($website)->getDefaultGroup()->getDefaultStoreId();
        
        
        
    }
    
    public function getCurrentScope(){
        
        $website = Mage::app()->getRequest()->getParam('website') ? Mage::app()->getRequest()->getParam('website') : Mage::getSingleton('adminhtml/config_data')->getWebsite();
        $store   = Mage::app()->getRequest()->getParam('store') ? Mage::app()->getRequest()->getParam('store') : Mage::getSingleton('adminhtml/config_data')->getStore();
        
        if (!$website && !$store) return 'default';
        
        if ($store) return 'stores';
        if ($website) return 'websites';
        
    }
    
    public function getScopeForUrl(){
        
        $website = Mage::app()->getRequest()->getParam('website') ? Mage::app()->getRequest()->getParam('website') : Mage::getSingleton('adminhtml/config_data')->getWebsite();
        $store   = Mage::app()->getRequest()->getParam('store') ? Mage::app()->getRequest()->getParam('store') : Mage::getSingleton('adminhtml/config_data')->getStore();
        
        if (!$website && !$store) return array();
        
        if ($store) return array('website' => $website, 'store' => $store);
        if ($website) return array('website' => $website);
        
    }
    
    public function getCurrentScopeId(){
        
        $website = Mage::app()->getRequest()->getParam('website') ? Mage::app()->getRequest()->getParam('website') : Mage::getSingleton('adminhtml/config_data')->getWebsite();
        $store   = Mage::app()->getRequest()->getParam('store') ? Mage::app()->getRequest()->getParam('store') : Mage::getSingleton('adminhtml/config_data')->getStore();
        
        if (!$website && !$store) return 0;
        
        if ($store) return Mage::getModel('core/store')->load($store)->getId();
        if ($website) return Mage::getModel('core/website')->load($website)->getId();
        
    }
    
    public function translateCartHash($hash = ''){
        
        if (empty($hash)) return false;
        
        $result = Mage::helper('recapture/transport')->dispatch('cart/retrieve', array(
            'hash' => $hash
        ));
        
        $body = @json_decode($result->getBody());
        
        if ($body->status == 'success'){
            
            return $body->data->cart_id;
            
        } else return false;
        
    }
    
    public function associateCartToMe($cartId = null){
        
        if (empty($cartId)) return false;
        
        $session = Mage::getSingleton('checkout/session');
        
        $session->clear();
        $session->setQuoteId($cartId);
        
        $quote = $session->getQuote();
        
        //if this cart somehow was already converted, we're not going to be able to load it. as such, we can't associate it.
        if ($quote->getId() != $cartId) return false;
        
        return true;
        
    }
    
}
