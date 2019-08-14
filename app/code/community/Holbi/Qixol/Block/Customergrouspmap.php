<?php
class Holbi_Qixol_Block_Customergrouspmap extends Mage_Core_Block_Template {

    public function _prepareLayout() {
        return parent::_prepareLayout();
    }

    public function getShippingmap() {
        if (!$this->hasData('customergrouspmap')) {
            $this->setData('customergrouspmap', Mage::registry('customergrouspmap'));
        }
        return $this->getData('customergrouspmap');
    }



}