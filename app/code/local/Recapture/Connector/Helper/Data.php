<?php

class Recapture_Connector_Helper_Data extends Mage_Core_Helper_Abstract {
    
    private $_registry = array();
    
    public function isEnabled(){
        
        return Mage::getStoreConfig('recapture/configuration/enabled');
        
    }
    
    public function isIpIgnored(){
        
        $ips = Mage::getStoreConfig('recapture/abandoned_carts/ignore_ips');
        $exploded = explode(',', $ips);
        
        foreach ($exploded as $ip){
            
            $ip = trim($ip);
            
            if ($ip == Mage::helper('core/http')->getRemoteAddr()){
                return true;
            }
            
        }
        
        return false;
        
    }
    
    public function shouldCaptureSubscriber(){
        
        return Mage::getStoreConfig('recapture/abandoned_carts/capture_subscriber');
        
    }
    
    public function getHomeUrl($path){
        
        $baseUrl = Mage::getStoreConfig('recapture/configuration/dev_base_url');
        if (!$baseUrl) $baseUrl = 'http://www.recapture.io/';
        
        return $baseUrl . $path;
        
    }
    
    public function canTrackEmail(){
        
        return Mage::getStoreConfig('recapture/abandoned_carts/track_email');
        
    }
    
    public function getReturnLanding(){
    
        return Mage::getStoreConfig('recapture/abandoned_carts/return_landing');
    
    }
    
    public function getApiKey(){
        
        return Mage::getStoreConfig('recapture/configuration/api_key');
        
    }
    
    public function getScopeStoreId(){
        
        $website = Mage::app()->getRequest()->getParam('website');
        $website = !empty($website) ? $website : Mage::getSingleton('adminhtml/config_data')->getWebsite();
        
        $store   = Mage::app()->getRequest()->getParam('store');
        $store   = !empty($store) ? $store : Mage::getSingleton('adminhtml/config_data')->getStore();
        
        if (!$website && !$store) return '0';
        
        if ($store) return Mage::getModel('core/store')->load($store)->getId();
        if ($website) return Mage::getModel('core/website')->load($website)->getDefaultGroup()->getDefaultStoreId();
        
        
        
    }
    
    public function getCurrentScope(){
        
        $website = Mage::app()->getRequest()->getParam('website');
        $website = !empty($website) ? $website : Mage::getSingleton('adminhtml/config_data')->getWebsite();
        
        $store   = Mage::app()->getRequest()->getParam('store');
        $store   = !empty($store) ? $store : Mage::getSingleton('adminhtml/config_data')->getStore();
        
        if (!$website && !$store) return 'default';
        
        if ($store) return 'stores';
        if ($website) return 'websites';
        
    }
    
    public function getScopeForUrl(){
        
        $website = Mage::app()->getRequest()->getParam('website');
        $website = !empty($website) ? $website : Mage::getSingleton('adminhtml/config_data')->getWebsite();
        
        $store   = Mage::app()->getRequest()->getParam('store');
        $store   = !empty($store) ? $store : Mage::getSingleton('adminhtml/config_data')->getStore();
        
        if (!$website && !$store) return array();
        
        if ($store) return array('website' => $website, 'store' => $store);
        if ($website) return array('website' => $website);
        
    }
    
    public function getCurrentScopeId(){
        
        $website = Mage::app()->getRequest()->getParam('website');
        $website = !empty($website) ? $website : Mage::getSingleton('adminhtml/config_data')->getWebsite();
        
        $store   = Mage::app()->getRequest()->getParam('store');
        $store   = !empty($store) ? $store : Mage::getSingleton('adminhtml/config_data')->getStore();
        
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
    
    public function getCustomerFirstname(Mage_Sales_Model_Quote $quote){
        
        //we first check the quote model itself
        $customerFirstname = $quote->getCustomerFirstname();
        if (!empty($customerFirstname)) return $customerFirstname;
        
        //if not on the quote model, we check the billing address
        $billingAddress = $quote->getBillingAddress();
        if ($billingAddress){
            
            $customerFirstname = $billingAddress->getFirstname();
            if (!empty($customerFirstname)) return $customerFirstname;
            
        }
        
        //if not in the billing address, last resort we check the shipping address
        $shippingAddress = $quote->getShippingAddress();
        if ($shippingAddress){
            
            $customerFirstname = $shippingAddress->getFirstname();
            if (!empty($customerFirstname)) return $customerFirstname;
            
        }
        
        return null;
        
    }
    
    public function getCustomerLastname(Mage_Sales_Model_Quote $quote){
        
        //we first check the quote model itself
        $customerLastname = $quote->getCustomerLastname();
        if (!empty($customerLastname)) return $customerLastname;
        
        //if not on the quote model, we check the billing address
        $billingAddress = $quote->getBillingAddress();
        if ($billingAddress){
            
            $customerLastname = $billingAddress->getLastname();
            if (!empty($customerLastname)) return $customerLastname;
            
        }
        
        //if not in the billing address, last resort we check the shipping address
        $shippingAddress = $quote->getShippingAddress();
        if ($shippingAddress){
            
            $customerLastname = $shippingAddress->getLastname();
            if (!empty($customerLastname)) return $customerLastname;
            
        }
        
        return null;
        
    }
    
    public function getCustomerEmail(Mage_Sales_Model_Quote $quote){
        
        //we first check the quote model itself
        $customerEmail = $quote->getCustomerEmail();
        if (!empty($customerEmail)) return $customerEmail;
        
        //if not on the quote model, we check the billing address
        $billingAddress = $quote->getBillingAddress();
        if ($billingAddress){
            
            $customerEmail = $billingAddress->getEmail();
            if (!empty($customerEmail)) return $customerEmail;
            
        }
        
        //if not in the billing address, last resort we check the shipping address
        $shippingAddress = $quote->getShippingAddress();
        if ($shippingAddress){
            
            $customerEmail = $shippingAddress->getEmail();
            if (!empty($customerEmail)) return $customerEmail;
            
        }
        
        return null;
        
    }
    
    
    public function getCustomerHash(){
        
        return isset($_COOKIE['ra_customer_id']) ? $_COOKIE['ra_customer_id'] : null;
        
    }
    
    
    public function translateEmailHashes($hashes = array()){
        
        if (empty($hashes)) return false;
        
        $result = Mage::helper('recapture/transport')->dispatch('email/retrieve', array(
            'hashes' => $hashes
        ));
        
        $body = @json_decode($result->getBody());
        
        if ($body->status == 'success'){
            
            return $body->data->emails;
            
        } else return false;
        
    }
    
}
