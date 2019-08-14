<?php

class Recapture_Connector_Recaptureadmin_AuthenticateController extends Mage_Adminhtml_Controller_Action {

    public function indexAction(){

        if (Mage::helper('recapture')->getCurrentScope() == 'default'){

            Mage::getSingleton('adminhtml/session')->addError('You cannot authenticate the Default Config scope. Please change the Current Configuration Scope on the left to a specific website before authenticating.');

            return $this->_redirect('adminhtml/system_config/edit', array('section' => 'recapture'));

        }

        $scope = Mage::helper('recapture')->getScopeForUrl();

        $returnCancel = Mage::helper('adminhtml')->getUrl('adminhtml/recaptureadmin_authenticate/cancel', $scope);

        $scope['response_key'] = 'API_KEY';

        $returnConfirm = Mage::helper('adminhtml')->getUrl('adminhtml/recaptureadmin_authenticate/return', $scope);
        $baseUrl  = Mage::getUrl('recapture', array('_store' => Mage::helper('recapture')->getScopeStoreId()));

        $query = http_build_query(array(
            'return'        => $returnConfirm,
            'return_cancel' => $returnCancel,
            'base'          => $baseUrl,
            'currency'      => Mage::app()->getStore()->getBaseCurrencyCode()
        ));

        $authenticateUrl = Mage::helper('recapture')->getHomeUrl('account/auth?' . $query);
        return $this->_redirectUrl($authenticateUrl);

    }

    public function returnAction(){

        $apiKey = $this->getRequest()->getParam('response_key');

        $helper = Mage::helper('recapture');
        $scope = $helper->getCurrentScope();
        $scopeId = $helper->getCurrentScopeId();

        if ($apiKey){

            $config = new Mage_Core_Model_Config();
            $config->saveConfig('recapture/configuration/authenticated', true, $scope, $scopeId);
            $config->saveConfig('recapture/configuration/api_key', $apiKey, $scope, $scopeId);
            $config->saveConfig('recapture/configuration/enabled', true, $scope, $scopeId);

            Mage::app()->getCacheInstance()->cleanType('config');

            Mage::getSingleton('adminhtml/session')->addSuccess('Your account has been authenticated successfully!');

        } else {

            Mage::getSingleton('adminhtml/session')->addError('Unable to authenticate your account. Please ensure you are logged in to your Recapture account.');

        }

        $scope = Mage::helper('recapture')->getScopeForUrl();
        $scope['section'] = 'recapture';

        return $this->_redirect('adminhtml/system_config/edit', $scope);

    }

    public function cancelAction(){

        Mage::getSingleton('adminhtml/session')->addError('Authentication has been cancelled.');

        $scope = Mage::helper('recapture')->getScopeForUrl();
        $scope['section'] = 'recapture';

        return $this->_redirect('adminhtml/system_config/edit', $scope);

    }

}