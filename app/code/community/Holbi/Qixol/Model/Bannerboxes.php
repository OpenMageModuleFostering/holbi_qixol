<?php
class Holbi_Qixol_Model_Bannerboxes extends Mage_Core_Model_Abstract {

    public function _construct() {
        parent::_construct();
        $this->_init('qixol/bannerboxes', "banner_box_type");
    }
    
   public function getOptionArray(){
      $hlp = Mage::helper('qixol');
      $collections=$this->getCollection();
      $list_return=array();
          foreach ($collections as $banner_box){
                       if (strpos((string)$banner_box->getBannerBoxType(),'STICKER')===false)
                       $list_return[]=array(
                        'value' => (string)$banner_box->getBannerBoxType(), 
                        'label' => $hlp->__((string)$banner_box->getBannerBoxType())
              );
          }
      return $list_return;
   }

   public function getStickerOptionArray(){
      $hlp = Mage::helper('qixol');
      $collections=$this->getCollection();
      $list_return=array();
          foreach ($collections as $banner_box){
                       if (strpos((string)$banner_box->getBannerBoxType(),'STICKER')!==false)
                       $list_return[]=array(
                        'value' => (string)$banner_box->getBannerBoxType(), 
                        'label' => $hlp->__((string)$banner_box->getBannerBoxType())
              );
          }
      return $list_return;
   }
}