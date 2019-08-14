<?php
class Holbi_Qixol_Model_Mysql4_Customergrouspmap extends Mage_Core_Model_Mysql4_Abstract
{
    protected $_isPkAutoIncrement = false;
    public function _construct()
    {
        $this->_init("qixol/customergrouspmap", "customer_group_name");
    }
}