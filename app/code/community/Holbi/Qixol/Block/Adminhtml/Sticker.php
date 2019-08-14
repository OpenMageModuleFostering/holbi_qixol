<?php
class Holbi_Qixol_Block_Adminhtml_Sticker extends Mage_Adminhtml_Block_Widget_Grid_Container {

    public function __construct() {
        $this->_controller = 'adminhtml_sticker';
        $this->_blockGroup = 'qixol';
        $this->_headerText = Mage::helper('qixol')->__('Stickers Manager');
        $this->_addButtonLabel = Mage::helper('qixol')->__('Add Sticker');
        parent::__construct();
    }
}