<?php

class Recapture_Connector_Model_Observer {
    
    public function itemUpdate($observer){
        
        try {
        
            $result = $this->_updateQuote($observer->getEvent()->getQuoteItem()->getQuote());
            
        } catch (Exception $e){
            
            Mage::log($e, null, 'recapture.log');
            
        }
        
        return $result;
        
    }
    
    public function quoteUpdate($observer){
        
        try {
        
            $result = $this->_updateQuote($observer->getEvent()->getQuote());
            
        } catch (Exception $e){
            
            Mage::log($e, null, 'recapture.log');
            
        }
        
        return $result;
        
    }
    
    protected function _updateQuote(Mage_Sales_Model_Quote $quote){
        
        if (!Mage::helper('recapture')->isEnabled()) return $this;
        
        if (!$quote->getId()) return;
        
        //sales_quote_save_before gets called like 5 times on some page loads, we don't want to do 5 updates per page load
        if (Mage::registry('recapture_has_posted')) return;
        
        Mage::register('recapture_has_posted', true);
        
        $quote->collectTotals();
        
        $mediaConfig = Mage::getModel('catalog/product_media_config');
        $storeId     = Mage::app()->getStore();
        
        $transportData = array(
            'first_name'   => Mage::helper('recapture')->getCustomerFirstname($quote),
            'last_name'    => Mage::helper('recapture')->getCustomerLastname($quote),
            'email'        => Mage::helper('recapture')->getCustomerEmail($quote),
            'external_id'  => $quote->getId(),
            'grand_total'  => $quote->getGrandTotal(),
            'products'     => array(),
            'totals'       => array()
        );
        
        $cartItems = $quote->getAllVisibleItems();
        
        foreach ($cartItems as $item){
            
            $productModel = $item->getProduct();
            
            $productImage = false;
            
            $image = Mage::getResourceModel('catalog/product')->getAttributeRawValue($productModel->getId(), 'thumbnail', $storeId);
            if ($image && $image != 'no_selection') $productImage = $mediaConfig->getMediaUrl($image);
            
            //check configurable first
            if ($item->getProductType() == 'configurable'){
                
                if (Mage::getStoreConfig('checkout/cart/configurable_product_image') == 'itself'){
                
                    $child = $productModel->getIdBySku($item->getSku());
                    
                    $image = Mage::getResourceModel('catalog/product')->getAttributeRawValue($child, 'thumbnail', $storeId);
                    if ($image && $image != 'no_selection') $productImage = $mediaConfig->getMediaUrl($image);
                    
                }
            }
            
            //then check grouped
            if (Mage::getStoreConfig('checkout/cart/grouped_product_image') == 'parent'){
                
                $options = $productModel->getTypeInstance(true)->getOrderOptions($productModel);
                
                if (isset($options['super_product_config']) && $options['super_product_config']['product_type'] == 'grouped'){
                    
                    $parent = $options['super_product_config']['product_id'];
                    $image = Mage::getResourceModel('catalog/product')->getAttributeRawValue($parent, 'thumbnail', $storeId);
                    
                    if ($image && $image != 'no_selection') $productImage = $mediaConfig->getMediaUrl($image);
                    
                }
            }
            
            //if after all that, we still don't have a product image, we get the placeholder image
            if (!$productImage) {
                
                $productImage = $mediaConfig->getMediaUrl('placeholder/' . Mage::getStoreConfig("catalog/placeholder/image_placeholder"));
                
            }
            
            $optionsHelper = Mage::helper('catalog/product_configuration');
            
            if ($item->getProductType() == 'configurable'){
                
                $visibleOptions = $optionsHelper->getConfigurableOptions($item);
                
            } else {
                
                $visibleOptions = $optionsHelper->getCustomOptions($item);
                
            }
            
            $product = array(
                'name'    => $item->getName(),
                'sku'     => $item->getSku(),
                'price'   => $item->getPrice(),
                'qty'     => $item->getQty(),
                'image'   => $productImage,
                'options' => $visibleOptions
            );
            
            $transportData['products'][] = $product;
            
        }
        
        $totals = $quote->getTotals();
        
        foreach ($totals as $total){
            
            //we pass grand total on the top level
            if ($total->getCode() == 'grand_total') continue;
            
            $total = array(
                'name'   => $total->getTitle(),
                'amount' => $total->getValue()
            );
            
            $transportData['totals'][] = $total;
            
        }
        
        Mage::helper('recapture/transport')->dispatch('cart', $transportData);
        
        return $this;
        
    }

    public function quoteDelete($observer){
        
        try {
        
            if (!Mage::helper('recapture')->isEnabled()) return $this;
            
            $quote = $observer->getEvent()->getQuote();
            
            $transportData = array(
                'external_id'  => $quote->getId()
            );
            
            Mage::helper('recapture/transport')->dispatch('cart/remove', $transportData);
            
        } catch (Exception $e){
            
            Mage::log($e, null, 'recapture.log');
            
        }
            
        return $this;
        
    }
    
    public function cartConversion($observer){
        
        try {
        
            if (!Mage::helper('recapture')->isEnabled()) return $this;
            
            $order = $observer->getEvent()->getOrder();
            
            $transportData = array(
                'external_id'  => $order->getQuoteId()
            );
            
            Mage::helper('recapture/transport')->dispatch('conversion', $transportData);
            
        } catch (Exception $e){
            
            Mage::log($e, null, 'recapture.log');
            
        }
        
        return $this;
        
    }
    
    public function newsletterSubscriber($observer){
        
        try {
            
            if (!Mage::helper('recapture')->isEnabled()) return $this;
            if (!Mage::helper('recapture')->shouldCaptureSubscriber()) return $this;
            
            $subscriber = $observer->getEvent()->getSubscriber();
            $email = $subscriber->getSubscriberEmail();
            
            $quote = Mage::getSingleton('checkout/session')->getQuote();
            $quote->setCustomerEmail($email);
            $quote->save();
            
            //we do this in case the customer didn't have a cart to begin with
            Mage::helper('recapture')->associateCartToMe($quote->getId());
            
        } catch (Exception $e){
            
            Mage::log($e, null, 'recapture.log');
            
        }
        
        return $this;
        
        
    }
    
}