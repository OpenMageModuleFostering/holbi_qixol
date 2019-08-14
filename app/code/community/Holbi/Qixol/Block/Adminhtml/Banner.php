<?php
class Holbi_Qixol_Block_Adminhtml_Banner extends Mage_Adminhtml_Block_Widget_Grid_Container {

    public function __construct() {
        $this->_controller = 'adminhtml_banner';
        $this->_blockGroup = 'qixol';
        $this->_headerText = Mage::helper('qixol')->__('Banner Manager');
        $this->_addButtonLabel = Mage::helper('qixol')->__('Add Banner');
        parent::__construct();
    }
}