<?php
class Holbi_Qixol_Block_Adminhtml_Banner_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs {

    public function __construct() {
        parent::__construct();
        $this->setId('qixol_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('qixol')->__('Item Information'));
    }

    protected function _beforeToHtml() {
        $this->addTab('form_section', array(
            'label' => Mage::helper('qixol')->__('Banner Information'),
            'alt' => Mage::helper('qixol')->__('Banner information'),
            'content' => $this->getLayout()->createBlock('qixol/adminhtml_banner_edit_tab_form')->toHtml(),
        ));        
        return parent::_beforeToHtml();
    }

}