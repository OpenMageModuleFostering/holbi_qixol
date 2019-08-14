<?php
class Holbi_Qixol_Model_Shippingmap extends Mage_Core_Model_Abstract {

    public function _construct() {
        parent::_construct();
        $this->_init('qixol/shippingmap', "shipping_name");
    }
    
   public function getOptionArray(){
      $hlp = Mage::helper('qixol');
      $collections=$this->getCollection();
      $list_return=array();
          foreach ($collections as $item){
                       $list_return[]=array(
                        'value' => (string)$item->getShippingName(), 
                        'label' => $hlp->__((string)$item->getShippingName())
              );
          }
      return $list_return;
   }
}