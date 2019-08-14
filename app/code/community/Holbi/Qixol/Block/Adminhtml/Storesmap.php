<?php
class Holbi_Qixol_Block_Adminhtml_Storesmap extends Mage_Adminhtml_Block_Widget_Grid_Container {

    public function __construct() {
        $this->_controller = 'adminhtml_storesmap';
        $this->_blockGroup = 'qixol';
        $this->_headerText = Mage::helper('qixol')->__('Manage Stores Map');
        $this->_addButtonLabel = Mage::helper('qixol')->__('Add Stores Map');
        parent::__construct();
    }
}