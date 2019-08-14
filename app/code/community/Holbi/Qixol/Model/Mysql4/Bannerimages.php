<?php
class Holbi_Qixol_Model_Mysql4_Bannerimages extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        $this->_init('qixol/bannerimages','banner_image_id');
    }
}