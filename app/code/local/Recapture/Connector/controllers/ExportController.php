<?php

class Soshify_Connector_ExportController extends Mage_Core_Controller_Front_Action {
  
    public function indexAction(){
      
        $resource        = Mage::getSingleton('core/resource');
        $readConnection  = $resource->getConnection('core_read');
        $productResource = Mage::getModel('catalog/product')->getResource();
        
        $name       = $productResource->getAttribute('name');
        $visibility = $productResource->getAttribute('visibility');
        $status     = $productResource->getAttribute('status');
        
        $nameId       = $name->getId();
        $visibilityId = $visibility->getId();
        $statusId     = $status->getId();
         
        $select = $readConnection->select();
        $select = $select->from(array('e' => $resource->getTableName('catalog/product')), array('sku'))->where(new Zend_Db_Expr('sku IS NOT NULL'));
        
        //is active
        $select->joinLeft(array('table_status' => $status->getBackendTable()), "(table_status.entity_id = e.entity_id AND table_status.attribute_id = $statusId)", null)->where("table_status.value = '" . Mage_Catalog_Model_Product_Status::STATUS_ENABLED . "'");
        
        //name
        $select->joinLeft(array('table_name' => $name->getBackendTable()), "(table_name.entity_id = e.entity_id AND table_name.attribute_id = $nameId)", array('name' => 'value'));
        
        //visibility
        $select->joinLeft(array('table_visibility' => $visibility->getBackendTable()), "(table_visibility.entity_id = e.entity_id AND table_visibility.attribute_id = $visibilityId)", array('visibility' => 'value'))->where("table_visibility.value != '" . Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE . "'");
        
        $query = $select->query();
        
        $dir = Mage::getBaseDir('var') . '/soshify/';
        $file =  'soshify.csv';
         
        mkdir($dir, 0777, true);
        
        $fh = fopen($dir . '/' . $file, 'w');
        $csv = new Varien_File_Csv();
        
        while ($product = $query->fetch()){
          
          $csv->fputcsv($fh, $product);
          
        }
        
        fclose($fh);
      
    }
    
    public function exportAction(){
      
        $resource        = Mage::getSingleton('core/resource');
        $readConnection  = $resource->getConnection('core_read');
        $productResource = Mage::getModel('catalog/product')->getResource();
        
        $name   = $productResource->getAttribute('name');
        $photos = $productResource->getAttribute('instagram_photos');
        
        $nameId   = $name->getId();
        $photosId = $photos->getId();
         
        $select = $readConnection->select();
        $select = $select->from(array('e' => $resource->getTableName('catalog/product')), array('sku'))->where(new Zend_Db_Expr('sku IS NOT NULL'));
        
        //name
        $select->joinLeft(array('table_name' => $name->getBackendTable()), "(table_name.entity_id = e.entity_id AND table_name.attribute_id = $nameId)", array('name' => 'value'));
        
        //name
        $select->joinLeft(array('table_photos' => $photos->getBackendTable()), "(table_photos.entity_id = e.entity_id AND table_photos.attribute_id = $photosId)", array('photos' => 'value'));
        
        $query = $select->query();
        
        $ids = array();
        
        while ($product = $query->fetch()){
          
          $theseIds = explode(',', $product['photos']);
          
          if (count($theseIds)){
            
            foreach ($theseIds as $id){
              
              if (empty($id)) continue;
              
              if (!isset($ids[$id])) $ids[$id] = array();
              
              $ids[$id][] = $product['sku'];
              
            }
            
          }
          
        }
        
        $dir = Mage::getBaseDir('var') . '/soshify/';
        $file =  'soshify-photos.csv';
        
        $fh = fopen($dir . '/' . $file, 'w');
        $csv = new Varien_File_Csv();
        
        foreach ($ids as $id => $products){
          
          $csv->fputcsv($fh, array(
            'code' => $id,
            'products' => implode(',', $products)
          ));
          
        }
        
        fclose($fh);
      
    }
    
}
