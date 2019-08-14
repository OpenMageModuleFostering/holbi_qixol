<?php
class Holbi_Qixol_Model_System_Config_Source_Attributes
{

    public function toOptionArray(){
        $hlp = Mage::helper('qixol');
        $attributes_return=array();

          $attributes = Mage::getResourceModel('catalog/product_attribute_collection')
              ->getItems();

          foreach ($attributes as $attribute){
             $attributes_return[]=array(
                        'value' => (string)$attribute->getAttributecode(), 
                        'label' => $hlp->__($attribute->getFrontendLabel()!=''?$attribute->getFrontendLabel():$attribute->getAttributecode())
              );
          }

       return $attributes_return;
    }
}