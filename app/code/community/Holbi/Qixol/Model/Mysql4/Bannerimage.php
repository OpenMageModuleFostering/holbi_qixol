<?php
class Holbi_Qixol_Model_Mysql4_Bannerimage extends Mage_Core_Model_Mysql4_Abstract {

    public function _construct() {
        $this->_init('qixol/bannerimage', 'banner_image_id');
    }
}