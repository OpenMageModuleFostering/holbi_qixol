<?php
class Holbi_Qixol_Block_Adminhtml_Sticker_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form {

    protected function _prepareForm() {
        $form = new Varien_Data_Form();
        $this->setForm($form);
        $fieldset = $form->addFieldset('sticker_form', array('legend' => Mage::helper('qixol')->__('Item information')));
        $version = substr(Mage::getVersion(), 0, 3);
        //$config = (($version == '1.4' || $version == '1.5') ? "'config' => Mage::getSingleton('sticker/wysiwyg_config')->getConfig()" : "'class'=>''");


        $fieldset->addField('banner_link_name', 'multiselect', array(
            'label' => Mage::helper('qixol')->__('Sticker show on:'),
            'class' => 'required-entry',
            'required' => true,
            'name' => 'banner_link_name',
            'values' => Mage::getModel('qixol/bannerboxes')->getStickerOptionArray(),
            'after_element_html' => Mage::helper('qixol')->__(' '),
        ));

        $fieldset->addField('filename', 'image', array(
            'label' => Mage::helper('qixol')->__('Upload and use Image'),
            'required' => false,
            'name' => 'filename',
        ));

        /*$fieldset->addField('use_default_sticker', 'select', array(
            'label' => Mage::helper('qixol')->__('Or Default sticker for'),
            'class' => 'required-entry',
            'required' => false,
            'name' => 'use_default_sticker',
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
                array(
                    'value' => 'MULTIPLEPROMO',
                    'label' => Mage::helper('qixol')->__('Multiple promos'),
                ),
                array(
                    'value' => 'PRODUCTSREDUCTION',
                    'label' => Mage::helper('qixol')->__('Product reduction'),
                ),
            ),
        ));*/

        $fieldset->addField('use_default_banner_group', 'select', array(
            'label' => Mage::helper('qixol')->__('For Default Promotion Type?'),
            'class' => 'required-entry',
            'name' => 'use_default_banner_group',
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
        ));

        $fieldset->addField('default_banner_group', 'select', array(
            'label' => Mage::helper('qixol')->__('Default promotion type'),
            'class' => 'required-entry',
            'required' => false,
            'name' => 'default_banner_group',
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
                ),*/
                array(
                    'value' => 'PRODUCTSREDUCTION',
                    'label' => Mage::helper('qixol')->__('Product reduction'),
                ),
            ),
        ));

        $fieldset->addField('unique_banner_group', 'text', array(
            'label' => Mage::helper('qixol')->__('Unique Promotion reference'),
            'name' => 'unique_banner_group',
        ));

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


        $field_last=$fieldset->addField('status', 'select', array(
            'label' => Mage::helper('qixol')->__('Status'),
            'class' => 'required-entry',
            'name' => 'status',
            'values' => array(
                array(
                    'value' => 1,
                    'label' => Mage::helper('qixol')->__('Enabled'),
                ),
                array(
                    'value' => 0,
                    'label' => Mage::helper('qixol')->__('Disabled'),
                ),
            ),
        ));

         $field_last->setAfterElementHtml('<script>
          //< ![C
          function on_change_default_banner_group(){
                if ($(\'use_default_banner_group\')[$(\'use_default_banner_group\').selectedIndex].value==\'1\'){
                    $(\'unique_banner_group\').setValue("");
                    $(\'unique_banner_group\').disable();
                    $(\'default_banner_group\').enable();
                }else {
                    $(\'unique_banner_group\').enable();
                    $(\'default_banner_group\').selectedIndex=0;
                    $(\'default_banner_group\').disable();
                }

          }
          if ($(\'use_default_banner_group\')[$(\'use_default_banner_group\').selectedIndex].value==\'1\'){
              $(\'unique_banner_group\').disable();
          }else {
              $(\'default_banner_group\').selectedIndex=0;
              $(\'default_banner_group\').disable();
          }

 
            document.observe("dom:loaded", function() {

                $("use_default_banner_group").observe("change",function(e){
                       on_change_default_banner_group();
                });
            });


          //]]>
          </script>');

        if (Mage::getSingleton('adminhtml/session')->getStickerData()) {
            $form->setValues(Mage::getSingleton('adminhtml/session')->getStickerData());
            Mage::getSingleton('adminhtml/session')->setStickerData(null);
        } elseif (Mage::registry('sticker_data')) {
            $form->setValues(Mage::registry('sticker_data')->getData());
        }
        return parent::_prepareForm();
    }

}