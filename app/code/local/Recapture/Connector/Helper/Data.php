<?php

class Recapture_Connector_Helper_Data extends Mage_Core_Helper_Abstract {
    
    public function isEnabled(){
        
        return Mage::getStoreConfig('recapture/configuration/enabled');
        
    }
    
    public function getApiKey(){
        
        return Mage::getStoreConfig('recapture/configuration/api_key');
        
    }
    
}
