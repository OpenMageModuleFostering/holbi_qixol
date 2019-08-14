<?php
class Holbi_Qixol_Block_Adminhtml_Shippingmap extends Mage_Adminhtml_Block_Widget_Grid_Container {

    public function __construct() {
        $this->_controller = 'adminhtml_shippingmap';
        $this->_blockGroup = 'qixol';
        $this->_headerText = Mage::helper('qixol')->__('Manage Shippings Map');
        $this->_addButtonLabel = Mage::helper('qixol')->__('Add Shipping Map');
        parent::__construct();
    }
}