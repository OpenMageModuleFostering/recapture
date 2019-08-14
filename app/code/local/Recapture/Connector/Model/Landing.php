<?php

class Recapture_Connector_Model_Landing {
    
    const REDIRECT_HOME     = 'home';
    const REDIRECT_CART     = 'cart';
    const REDIRECT_CHECKOUT = 'checkout';
    
    public function toOptionArray(){
        
        return array(
            array('value' => self::REDIRECT_HOME, 'label' => Mage::helper('recapture')->__('Home Page')),
            array('value' => self::REDIRECT_CART, 'label' => Mage::helper('recapture')->__('Cart Page')),
            array('value' => self::REDIRECT_CHECKOUT, 'label' => Mage::helper('recapture')->__('Checkout Page')),
        );
    }

}