<?php
class Holbi_Qixol_Block_Storesmap extends Mage_Core_Block_Template {

    public function _prepareLayout() {
        return parent::_prepareLayout();
    }

    public function getShippingmap() {
        if (!$this->hasData('storesmap')) {
            $this->setData('storesmap', Mage::registry('storesmap'));
        }
        return $this->getData('storesmap');
    }



}