<?php

class Recapture_Connector_CartController extends Mage_Core_Controller_Front_Action {

    public function indexAction(){

        $helper = Mage::helper('recapture');
        if (!$helper->isEnabled() || !$helper->getApiKey()) return $this->_redirect('/');

        $hash = $this->getRequest()->getParam('hash');

        try {

            $cartId = $helper->translateCartHash($hash);

        } catch (Exception $e){

            Mage::log($e->getMessage());

        }

        if (!$cartId){

            Mage::getSingleton('core/session')->addError('There was an error retrieving your cart.');
            return $this->_redirect('/');

        }

        try {

            $result = $helper->associateCartToMe($cartId);

        } catch (Exception $e){

            Mage::log($e->getMessage());

        }

        if (!$result){

            Mage::getSingleton('core/session')->addError('There was an error retrieving your cart.');
            return $this->_redirect('/');

        } else {

            $cart = Mage::getModel('checkout/cart')->getQuote();

            $redirectSection = $helper->getReturnLanding();

            switch ($redirectSection){

                case Recapture_Connector_Model_Landing::REDIRECT_HOME:

                    return $this->_redirect('/');

                case Recapture_Connector_Model_Landing::REDIRECT_CHECKOUT:

                    return $this->getResponse()->setRedirect(Mage::helper('checkout/url')->getCheckoutUrl());

                case Recapture_Connector_Model_Landing::REDIRECT_CART:
                default:

                    return $this->_redirect('checkout/cart');

            }

            return $this->_redirect('/');



        }

    }

}