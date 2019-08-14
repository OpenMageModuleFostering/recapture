<?php 

class Recapture_Connector_Block_Client extends Mage_Core_Block_Template {
    
    private $_cartId = null;
    
    public function shouldTrack(){
        
        if (!Mage::helper('recapture')->isEnabled()) return false;
        
        $apiKey = $this->getApiKey();
        if (empty($apiKey)) return false;
        
        return true;
        
    }
    
    public function shouldTrackEmail(){
        
        if (!$this->shouldTrack()) return false;
        if (!Mage::helper('recapture')->canTrackEmail()) return false;
        
        return true;
        
    }
    
    public function getApiKey(){
        
        return Mage::helper('recapture')->getApiKey();
        
    }
    
    public function getQueueUrl(){
        
        $queueUrl = Mage::getStoreConfig('recapture/configuration/dev_queue_url');
        if (!$queueUrl) $queueUrl = '//cdn.recapture.io/sdk/v1/ra-queue.min.js';
        
        //append a timestamp that changes every 10 minutes
        $queueUrl .= '?v=' . round(time() / (60 * 10));
        
        return $queueUrl;
        
    }
    
}