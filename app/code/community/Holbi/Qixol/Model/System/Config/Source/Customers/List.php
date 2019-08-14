<?php
class Holbi_Qixol_Model_System_Config_Source_Customers_List
{

    public function toOptionArray(){
        $hlp = Mage::helper('qixol');
        $customerGroups=array();

          $customerGroupModel = new Mage_Customer_Model_Group();
          $customerGroups = array();
          $allCustomerGroups  = $customerGroupModel->getCollection()->toOptionHash();
          foreach($allCustomerGroups as $key => $group){
            $customerGroups[] = array('value'=>$key,'label'=>$hlp->__($group));
          }

       return $customerGroups;
    }

}