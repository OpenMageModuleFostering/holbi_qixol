<?php
class Holbi_Qixol_Model_Mysql4_Storesmap_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract {

    public function _construct() {
        parent::_construct();
        $this->_init('qixol/storesmap');
    }

}