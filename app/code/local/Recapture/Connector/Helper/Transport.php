<?php

class Recapture_Connector_Helper_Transport extends Mage_Core_Helper_Abstract {
  
    public function dispatch($route = '', $data = array()){
        
        if (!Mage::helper('recapture')->isEnabled()) return false;
        if (empty($route)) return false;
        
        $adapter = new Zend_Http_Client_Adapter_Curl();
        $client  = new Zend_Http_Client('http://www.recapture.io/beacon/' . $route, array(
            'timeout' => 1
        ));
        
        //this is the users publicly accessible session ID
        $data['session'] = Mage::getSingleton('core/session')->getEncryptedSessionId();
        
        $client->setParameterPost($data);
        $client->setAdapter($adapter);
        $client->setHeaders('Api-Key', Mage::helper('recapture')->getApiKey());
        $response = $client->request('POST');
        
        return $response;
        
    }
    
}
