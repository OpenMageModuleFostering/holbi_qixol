<?php
class Holbi_Qixol_Model_Banner extends Mage_Core_Model_Abstract {

    public function _construct() {
        parent::_construct();
        $this->_init('qixol/banner');
    }
    
    public function load($id){
      parent::load($id);
      $this->getBannerImages();
      return $this;
    }
    public function save(){
      $banner_images=array();

      if ($this->_data['banner_images']){
          $banner_images=$this->_data['banner_images'];
          unset($this->_data['banner_images']);
      }
      parent::save();
      if ($this->getBannerId()>0){
         $this->setSaveBannerImages($banner_images);
      }
    }
    public function getAllAvailBannerIds(){
        $collection = Mage::getResourceModel('qixol/banner_collection')
                        ->getAllIds();
        return $collection;
    }

    public function getAllBanners() {
        $collection = Mage::getResourceModel('qixol/banner_collection');
        $collection->getSelect()->where('status = ?', 1);
        $data = array();
        foreach ($collection as $record) {
            $data[$record->getId()] = array('value' => $record->getId(), 'label' => $record->getfilename());
        }
        return $data;
    }

    public function getDataByBannerIds($bannerIds) {
        $data = array();
        if ($bannerIds != '') {
            $collection = Mage::getResourceModel('qixol/banner_collection');
            $collection->getSelect()->where('banner_id IN (' . $bannerIds . ')')->order('sort_order');
            foreach ($collection as $record) {
                $status = $record->getStatus();
                if ($status == 1) {
                    $data[] = $record;
                }
            }
        }
        return $data;
    }

    public function getBannerImages($bannerid=0) {
        if ($bannerid==0) $bannerid=$this->getBannerId();
        $banner_images = Mage::getResourceModel('qixol/banner')->getBannerImages($bannerid);
        $parced_images=Array();
        if (is_array($banner_images))
        foreach ($banner_images as $banner_image){
           $parced_images[]=$banner_image['banner_image_id'];
        }
        $this->setBannerImages($parced_images);
    }

    public function setSaveBannerImages($banner_images) {
        Mage::getResourceModel('qixol/banner')->setBannerImages($this->getbannerId(),$banner_images);        
    }    

   public function getBannerImageCollection()
   {
        return Mage::getResourceModel('qixol/bannerimage_collection');
   }
   
   public function getBannerImageIds()
   {
       return $this->getResource()->getBannerImageIds($this);
   }
}