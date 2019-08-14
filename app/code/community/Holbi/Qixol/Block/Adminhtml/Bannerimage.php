<?php
class Holbi_Qixol_Block_Adminhtml_Bannerimage extends Mage_Adminhtml_Block_Widget_Grid_Container {

    public function __construct() {
        $this->_controller = 'adminhtml_bannerimage';
        $this->_blockGroup = 'qixol';
        $this->_headerText = Mage::helper('qixol')->__('Banner Image Manager');
        $this->_addButtonLabel = Mage::helper('qixol')->__('Add Banner image');
        parent::__construct();
    }
}