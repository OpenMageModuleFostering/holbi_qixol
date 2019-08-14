<?php
class Holbi_Qixol_Model_Customergrouspmap extends Mage_Core_Model_Abstract {

    public function _construct() {
        parent::_construct();
        $this->_init('qixol/customergrouspmap', "customer_group_name");
    }
    
   public function getOptionArray(){
      $hlp = Mage::helper('qixol');
      $collections=$this->getCollection();
      $list_return=array();
          foreach ($collections as $item){
                       $list_return[]=array(
                        'value' => (string)$item->getCustomerGroupName(), 
                        'label' => $hlp->__((string)$item->getCustomerGroupName())
              );
          }
      return $list_return;
   }
}