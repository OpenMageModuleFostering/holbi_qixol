<?php
class Holbi_Qixol_Block_Adminhtml_Storesmap extends Mage_Adminhtml_Block_Widget_Grid_Container {

    public function __construct() {
        $this->_controller = 'adminhtml_storesmap';
        $this->_blockGroup = 'qixol';
        $this->_headerText = Mage::helper('qixol')->__('Store Integration Codes');
        $this->_addButtonLabel = Mage::helper('qixol')->__('Add Store Integration Code');
        parent::__construct();
    }
}