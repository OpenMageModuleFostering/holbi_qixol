<?php
class Holbi_Qixol_Block_Shippingmap extends Mage_Core_Block_Template {

    public function _prepareLayout() {
        return parent::_prepareLayout();
    }

    public function getShippingmap() {
        if (!$this->hasData('shippingmap')) {
            $this->setData('shippingmap', Mage::registry('shippingmap'));
        }
        return $this->getData('shippingmap');
    }



}