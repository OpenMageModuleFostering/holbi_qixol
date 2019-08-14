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

        $fieldset->addField('banner_link_name', 'multiselect', array(
            'label' => Mage::helper('qixol')->__('Display Zone'),
            'class' => 'required-entry',
            'required' => true,
            'name' => 'banner_link_name',
            'values' => Mage::getModel('qixol/bannerboxes')->getOptionArray(),
            'after_element_html' => Mage::helper('qixol')->__('use: <br>CATEGORY_TOP for showing on category list<br>PRODUCT_BOTTOM on bottom product page<br>PRODUCT_TOP on top on product page<br>PRODUCT_INLINE on top on product page<br>BACKET_INLINE on shopping cart<br>separate by ; '),
        ));

        $fieldset->addField('banner_images', 'multiselect', array(
            'label' => Mage::helper('qixol')->__('Images'),
            'required' => false,
            'name' => 'banner_images',
            'values' => Mage::getModel('qixol/bannerimages')->getOptionArray()
        ));


        $fieldset->addField('filename', 'image', array(
            'label' => Mage::helper('qixol')->__('Image'),
            'required' => false,
            'name' => 'filename',
        ));

        $fieldset->addField('banner_group', 'text', array(
            'label' => Mage::helper('qixol')->__('Promotion Ref'),
            'required' => false,
            'name' => 'banner_group',
        ));

        /*$fieldset->addField('banner_group', 'select', array(
            'label' => Mage::helper('qixol')->__('Banner Group'),
            'class' => 'required-entry',
            'required' => true,
            'name' => 'banner_group',
            'values' => array(
                array(
                    'value' => '',
                    'label' => Mage::helper('qixol')->__('Not defined'),
                ),
                array(
                    'value' => 'BOGOF',
                    'label' => Mage::helper('qixol')->__('Buy one get one free'),
                ),
                array(
                    'value' => 'BOGOR',
                    'label' => Mage::helper('qixol')->__('Buy one get one reduced'),
                ),
                array(
                    'value' => 'BUNDLE',
                    'label' => Mage::helper('qixol')->__('Bundle'),
                ),
                array(
                    'value' => 'DEAL',
                    'label' => Mage::helper('qixol')->__('Deal'),
                ),
                array(
                    'value' => 'FREEPRODUCT',
                    'label' => Mage::helper('qixol')->__('Free product'),
                ),
                array(
                    'value' => 'ISSUECOUPON',
                    'label' => Mage::helper('qixol')->__('Issue coupon'),
                ),
                array(
                    'value' => 'ISSUEPOINTS',
                    'label' => Mage::helper('qixol')->__('Issue points'),
                ),
                array(
                    'value' => 'BASKETREDUCTION',
                    'label' => Mage::helper('qixol')->__('Backet reduction'),
                ),
                array(
                    'value' => 'DELIVERYREDUCTION',
                    'label' => Mage::helper('qixol')->__('Delivery reduction'),
                ),
                /*array(
                    'value' => 8,
                    'label' => Mage::helper('qixol')->__('Multiple promos'),
                ),*//*
                array(
                    'value' => 'PRODUCTSREDUCTION',
                    'label' => Mage::helper('qixol')->__('Product reduction'),
                ),
            ),
        ));*/

        /*if ($version == '1.4' || $version == '1.5') {
            $fieldset->addField('banner_content', 'editor', array(
                'name' => 'banner_content',
                'label' => Mage::helper('qixol')->__('Content'),
                'title' => Mage::helper('qixol')->__('Content'),
                'style' => 'width:600px; height:250px;',
                'config' => Mage::getSingleton('banner/wysiwyg_config')->getConfig(),
                'wysiwyg' => true,
                'required' => false,
            ));
        } else {
            $fieldset->addField('banner_content', 'editor', array(
                'name' => 'banner_content',
                'label' => Mage::helper('cms')->__('Content'),
                'title' => Mage::helper('cms')->__('Content'),
                'style' => 'width:600px; height:250px;',                
                'wysiwyg' => false,
                'required' => false,
            ));
        }*/

        $fieldset->addField('sort_order', 'text', array(
            'label' => Mage::helper('qixol')->__('Sort Order'),
            'name' => 'sort_order',
        ));

        /*$fieldset->addField('is_default', 'select', array(
            'label' => Mage::helper('qixol')->__('Default?'),
            'class' => 'required-entry',
            'name' => 'is_default',
            'values' => array(
                array(
                    'value' => 1,
                    'label' => Mage::helper('qixol')->__('Yes'),
                ),
                array(
                    'value' => 0,
                    'label' => Mage::helper('qixol')->__('No'),
                ),
            ),
        ));*/

        $fieldset->addField('url', 'text', array(
            'label' => Mage::helper('qixol')->__('Url'),
            'required' => false,
            'name' => 'url',
        ));

        $fieldset->addField('status', 'select', array(
            'label' => Mage::helper('qixol')->__('Status'),
            'class' => 'required-entry',
            'name' => 'status',
            'values' => array(
                array(
                    'value' => 1,
                    'label' => Mage::helper('qixol')->__('Enabled'),
                ),
                array(
                    'value' => 2,
                    'label' => Mage::helper('qixol')->__('Disabled'),
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