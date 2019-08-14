<?php
class Holbi_Qixol_Model_Mysql4_Deletedproduct extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init("qixol/deletedproduct", "entity_id");
    }
}