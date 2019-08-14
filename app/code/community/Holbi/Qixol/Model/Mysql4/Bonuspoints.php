<?php
class Holbi_Qixol_Model_Mysql4_Bonuspoints extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        $this->_init('qixol/bonuspoints','points_account_id');
    }
}