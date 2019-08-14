<?php

class Recapture_Connector_IndexController extends Mage_Core_Controller_Front_Action {
    
    public function versionAction(){
        
        $version  = (string)Mage::getConfig()->getModuleConfig("Recapture_Connector")->version;
        $response = array('version' => $version);
        
        $this->getResponse()->clearHeaders()->setHeader('Content-type','application/json',true);
        $this->getResponse()->setBody(json_encode($response));
        
    }
    
}  