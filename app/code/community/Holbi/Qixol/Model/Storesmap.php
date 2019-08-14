<?php
class Holbi_Qixol_Model_Storesmap extends Mage_Core_Model_Abstract {

    public function _construct() {
        parent::_construct();
        $this->_init('qixol/storesmap', "store_name");
    }
    
   public function getOptionArray(){
      $hlp = Mage::helper('qixol');
      $collections=$this->getCollection();
      $list_return=array();
          foreach ($collections as $item){
                       $list_return[]=array(
                        'value' => (string)$item->getStoreName(), 
                        'label' => $hlp->__((string)$item->getStoreName())
              );
          }
      return $list_return;
   }
}