<?php
class Holbi_Qixol_Block_Adminhtml_Banner_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form {

    protected function _prepareForm() {
        $form = new Varien_Data_Form();
        $this->setForm($form);
        $fieldset = $form->addFieldset('banner_form', array('legend' => Mage::helper('qixol')->__('Item information')));
        $version = substr(Mage::getVersion(), 0, 3);
        //$config = (($version == '1.4' || $version == '1.5') ? "'config' => Mage::getSingleton('banner/wysiwyg_config')->getConfig()" : "'class'=>''");

        $fieldset->addField('title', 'text', array(
            'label' => Mage::helper('qixol')->__('Title'),
            'class' => 'required-entry',
            'required' => true,
            'name' => 'title',
        ));

        $fieldset->addField('display_zone', 'multiselect', array(
            'label' => Mage::helper('qixol')->__('Display Zone'),
            'class' => 'required-entry',
            'required' => true,
            'name' => 'display_zone',
            'values' => Mage::getModel('qixol/bannerboxes')->getOptionArray(),
            'after_element_html' => Mage::helper('qixol')->__('use: <br>CATEGORY_TOP for showing on category list<br>PRODUCT_BOTTOM on bottom product page<br>PRODUCT_TOP on top on product page<br>PRODUCT_INLINE on top on product page<br>separate by ; '),
        ));

        $fieldset->addField('status', 'select', array(
            'label' => Mage::helper('qixol')->__('Status'),
            'class' => 'required-entry',
            'name' => 'status',
            'values' => array(
                array(
                    'value' => 0,
                    'label' => Mage::helper('qixol')->__('Disabled'),
                ),
                array(
                    'value' => 1,
                    'label' => Mage::helper('qixol')->__('Enabled'),
                ),
            ),
        ));

        if (Mage::getSingleton('adminhtml/session')->getBannerData()) {
            $form->setValues(Mage::getSingleton('adminhtml/session')->getBannerData());
            Mage::getSingleton('adminhtml/session')->setBannerData(null);
        } elseif (Mage::registry('banner_data')) {
            $form->setValues(Mage::registry('banner_data')->getData());
        }
        return parent::_prepareForm();
    }

}