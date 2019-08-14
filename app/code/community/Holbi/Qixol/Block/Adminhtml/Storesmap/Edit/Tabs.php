<?php
class Holbi_Qixol_Block_Adminhtml_Storesmap_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs {

    public function __construct() {
        parent::__construct();
        $this->setId('qixol_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('qixol')->__('Store Map Information'));
    }

    protected function _beforeToHtml() {
        $this->addTab('form_section', array(
            'label' => Mage::helper('qixol')->__('Store Map Information'),
            'alt' => Mage::helper('qixol')->__('Store Map Information'),
            'content' => $this->getLayout()->createBlock('qixol/adminhtml_storesmap_edit_tab_form')->toHtml(),
        ));        
        return parent::_beforeToHtml();
    }

}