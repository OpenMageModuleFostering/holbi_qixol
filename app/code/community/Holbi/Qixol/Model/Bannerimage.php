<?php
//class Mage_Api_Model_User extends Mage_Core_Model_Abstract
class Holbi_Qixol_Model_Bannerimage extends Mage_Core_Model_Abstract
{
    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'qixol_bannerimage';

    /**
     * Constructor
     */
    protected function _construct()
    {
        $this->_init('qixol/bannerimage', 'banner_image_id');
    }

//    public function save()
//    {
//        $this->_beforeSave();
//        $data = array(
//                'filename'      => $this->getFilename(),
//                'sort_order'    => $this->getSortorder()
//            );
//
//        if ($this->getId() > 0) {
//            $data['banner_image_id']   = $this->getId();
//        }
//
//        if ($this->getPromotionReference())
//        {
//            $data['promotion_reference'] = $this->getPromotionReference();
//        }
//        
//        if ($this->getUrl()) {
//            $data['url']   = $this->_getEncodedApiKey($this->getUrl());
//        }
//
//        if ($this->getComment()) {
//            $data['comment']   = $this->_getEncodedApiKey($this->getComment());
//        }
//
//        $this->setData($data);
//        $this->_getResource()->save($this);
//        $this->_afterSave();
//        return $this;
//    }
//
//    public function delete()
//    {
//        $this->_beforeDelete();
//        $this->_getResource()->delete($this);
//        $this->_afterDelete();
//        return $this;
//    }
//
//    public function saveRelations()
//    {
//        $this->_getResource()->_saveRelations($this);
//        return $this;
//    }
//
//    public function deleteFromRole()
//    {
//        $this->_getResource()->deleteFromRole($this);
//        return $this;
//    }
//
//    public function add()
//    {
//        $this->_getResource()->add($this);
//        return $this;
//    }

    public function getCollection() {
        return Mage::getResourceModel('qixol/bannerimage_collection');
    }

    /**
     * Get helper instance
     *
     * @param string $helperName
     * @return Mage_Core_Helper_Abstract
     */
    protected function _getHelper($helperName)
    {
        return Mage::helper($helperName);
    }
}
