<?php

class Holbi_Qixol_Model_Bannerimages extends Mage_Core_Model_Abstract
{
    public function _construct(){

       $this->_init("qixol/bannerimages",'banner_image_id');

    }

   public function getOptionArray(){
      $hlp = Mage::helper('qixol');
      $collections=$this->getCollection();
      $list_return=array();
          foreach ($collections as $banner_image){
                       $list_return[]=array(
                        'value' => (string)$banner_image->getBannerImageId(), 
                        'label' => $hlp->__((string)$banner_image->getFilename())
              );
          }
      return $list_return;
   }

}
	 