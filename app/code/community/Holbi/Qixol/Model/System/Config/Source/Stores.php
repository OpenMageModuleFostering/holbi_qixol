<?php
class Holbi_Qixol_Model_System_Config_Source_Stores
{

    public function toOptionArray(){
        $hlp = Mage::helper('qixol');
        $stores=array(
            array(
                'value' => '0',
                'label' => $hlp->__('All stores'),
            )
        );
        foreach (Mage::app()->getWebsites() as $website) {
            foreach ($website->getGroups() as $group) {
            $stores_list = $group->getStores();
              foreach ($stores_list as $store) {
              $stores[]=array('value' => (string)$store->getId(), 'label' => $hlp->__($store->getName()."  - (".$store->getCode().")"));
              }
          }
        }
       return $stores;
    }
}