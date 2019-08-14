<?php
class Holbi_Qixol_Model_Mysql4_Storesmap extends Mage_Core_Model_Mysql4_Abstract
{
    protected $_isPkAutoIncrement = false;
    public function _construct()
    {
        $this->_init("qixol/storesmap", "store_name");
    }
}