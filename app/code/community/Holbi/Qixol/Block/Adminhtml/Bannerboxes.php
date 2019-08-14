<?php
class Holbi_Qixol_Block_Adminhtml_Bannerboxes extends Mage_Adminhtml_Block_Widget_Grid_Container {

    public function __construct() {
        $this->_controller = 'adminhtml_bannerboxes';
        $this->_blockGroup = 'qixol';
        $this->_headerText = Mage::helper('qixol')->__('Banner Box Manager');
        $this->_addButtonLabel = Mage::helper('qixol')->__('Add Banner box');
        parent::__construct();
    }
}