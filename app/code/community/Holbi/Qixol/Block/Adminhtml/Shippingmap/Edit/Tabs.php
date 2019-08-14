<?php
class Holbi_Qixol_Block_Adminhtml_Shippingmap_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs {

    public function __construct() {
        parent::__construct();
        $this->setId('qixol_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('qixol')->__('Shipping Method'));
    }

    protected function _beforeToHtml() {
        $this->addTab('form_section', array(
            'label' => Mage::helper('qixol')->__('Shipping Method'),
            'alt' => Mage::helper('qixol')->__('Shipping Method'),
            'content' => $this->getLayout()->createBlock('qixol/adminhtml_shippingmap_edit_tab_form')->toHtml(),
        ));        
        return parent::_beforeToHtml();
    }

}