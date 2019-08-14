<?php
class Holbi_Qixol_Block_Adminhtml_Bannerboxes_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form {

    protected function _prepareForm() {
        $form = new Varien_Data_Form();
        $this->setForm($form);
        $fieldset = $form->addFieldset('bannerbox_form', array('legend' => Mage::helper('qixol')->__('Item information')));
        $version = substr(Mage::getVersion(), 0, 3);
        //$config = (($version == '1.4' || $version == '1.5') ? "'config' => Mage::getSingleton('banner/wysiwyg_config')->getConfig()" : "'class'=>''");






        $fieldset->addField('banner_box_type', 'select', array(
            'label' => Mage::helper('qixol')->__('Box Position'),
            'class' => 'required-entry',
            'required' => true,
            'name' => 'banner_box_type',
            'values' => array(
                array(
                    'value' => 'CATEGORY_TOP',
                    'label' => Mage::helper('qixol')->__('CATEGORY_TOP - the top box of category listsing'),
                ),
                array(
                    'value' => 'PRODUCT_BOTTOM',
                    'label' => Mage::helper('qixol')->__('bottom of the product view page'),
                ),
                array(
                    'value' => 'PRODUCT_TOP',
                    'label' => Mage::helper('qixol')->__('PRODUCT_TOP - top of product view page'),
                ),
                array(
                    'value' => 'PRODUCT_INLINE',
                    'label' => Mage::helper('qixol')->__('PRODUCT_INLINE - inline in add to catrt box on product page'),
                ),
                array(
                    'value' => 'BASKET_INLINE',
                    'label' => Mage::helper('qixol')->__('BASKET_INLINE - box on basket'),
                ),
                array(
                    'value' => 'CATEGORY_STICKERS',
                    'label' => Mage::helper('qixol')->__('CATEGORY_STICKERS - stickers on category listing'),
                ),
                array(
                    'value' => 'PRODUCT_INFO_STICKERS',
                    'label' => Mage::helper('qixol')->__('PRODUCT_INFO_STICKERS - stickers on product info'),
                ),

            ),
        ));

        $fieldset->addField('banner_box_translation_type', 'text', array(
            'label' => Mage::helper('qixol')->__('Translation Type'),
            'class' => 'required-entry',
            'required' => true,
            'name' => 'banner_box_translation_type',
            'after_element_html' => Mage::helper('qixol')->__('fadeout, scrollHorz , tileSlide or others from Cycle2 for myltyple data'),
        ));


        $fieldset->addField('banner_box_is_active', 'select', array(
            'label' => Mage::helper('qixol')->__('Status'),
            'class' => 'required-entry',
            'name' => 'banner_box_is_active',
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

        if (Mage::getSingleton('adminhtml/session')->getBannerboxData()) {
            $form->setValues(Mage::getSingleton('adminhtml/session')->getBannerboxData());
            Mage::getSingleton('adminhtml/session')->setBannerboxData(null);
        } elseif (Mage::registry('bannerbox_data')) {
            $form->setValues(Mage::registry('bannerbox_data')->getData());
        }
        return parent::_prepareForm();
    }

}