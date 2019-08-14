<?php

class Holbi_Qixol_Model_Issuedcoupon extends Mage_Core_Model_Abstract
{
    public function _construct(){

       $this->_init("qixol/issuedcoupon",'entity_id');

    }

    public function getCustomerIssuedcoupons($customer_id) {
        $collection = Mage::getResourceModel('qixol/issuedcoupon_collection');
        $collection->getSelect()->where('customer_id = ?', $customer_id);
        return $collection;
    }
}