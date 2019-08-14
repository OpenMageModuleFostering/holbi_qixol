<?php
class Holbi_Qixol_Block_Adminhtml_Sticker_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form {

    protected function _prepareForm() {
        $form = new Varien_Data_Form();
        $this->setForm($form);
        $fieldset = $form->addFieldset('sticker_form', array('legend' => Mage::helper('qixol')->__('Item information')));
        $version = substr(Mage::getVersion(), 0, 3);
        //$config = (($version == '1.4' || $version == '1.5') ? "'config' => Mage::getSingleton('sticker/wysiwyg_config')->getConfig()" : "'class'=>''");

        $fieldset->addField('filename', 'image', array(
            'label' => Mage::helper('qixol')->__('Upload and use Image'),
            'required' => true,
            'name' => 'filename',
        ));

        $fieldset->addField('is_default_for_type', 'select', array(
            'label' => Mage::helper('qixol')->__('Default for Promotion Type?'),
            'class' => 'required-entry',
            'name' => 'is_default_for_type',
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

        $fieldset->addField('promo_type_name', 'select', array(
            'label' => Mage::helper('qixol')->__('Promotion Type'),
            'class' => 'required-entry',
            'required' => false,
            'name' => 'promo_type_name',
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

        $field_last = $fieldset->addField('promo_reference', 'text', array(
            'label' => Mage::helper('qixol')->__('Promotion reference'),
            'name' => 'promo_reference',
        ));

        $field_last->setAfterElementHtml('<script>
          //< ![C
          function on_change_is_default_for_type(){
                if ($(\'is_default_for_type\')[$(\'is_default_for_type\').selectedIndex].value==\'1\'){
                    $(\'promo_reference\').setValue("");
                    $(\'promo_reference\').disable();
                    $(\'promo_type_name\').enable();
                }else {
                    $(\'promo_reference\').enable();
                    $(\'promo_type_name\').selectedIndex=0;
                    $(\'promo_type_name\').disable();
                }

          }
 
        document.observe("dom:loaded", function() {

            $("is_default_for_type").observe("change",function(e){
                   on_change_is_default_for_type();
            });
            on_change_is_default_for_type();
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