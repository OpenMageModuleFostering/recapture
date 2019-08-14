<?php

class Recapture_Connector_EmailController extends Mage_Core_Controller_Front_Action {
    
    public function subscribeAction(){
        
        $emailHashes = $this->getRequest()->getParam('hashes');
        
        $emails = Mage::helper('recapture')->translateEmailHashes($emailHashes);
        
        foreach ($emails as $emailAddress){
            
            Mage::getModel('newsletter/subscriber')->setImportMode(true)->subscribe($emailAddress);
            
            $subscriber = Mage::getModel('newsletter/subscriber')->loadByEmail($emailAddress);
            $subscriber->setStatus(Mage_Newsletter_Model_Subscriber::STATUS_SUBSCRIBED);
            $subscriber->save();
            
        }
        
        $response = array('status' => 'success');
        
        $this->getResponse()->clearHeaders()->setHeader('Content-type','application/json',true);
        $this->getResponse()->setBody(json_encode($response));
        
    }
}  