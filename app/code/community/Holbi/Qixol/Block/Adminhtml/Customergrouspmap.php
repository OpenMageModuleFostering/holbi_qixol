<?php
class Holbi_Qixol_Block_Adminhtml_Customergrouspmap extends Mage_Adminhtml_Block_Widget_Grid_Container {

    public function __construct() {
        $this->_controller = 'adminhtml_customergrouspmap';
        $this->_blockGroup = 'qixol';
        $this->_headerText = Mage::helper('qixol')->__('Customer Group Map');
        $this->_addButtonLabel = Mage::helper('qixol')->__('Add Customer Group Map');
        parent::__construct();
    }
}