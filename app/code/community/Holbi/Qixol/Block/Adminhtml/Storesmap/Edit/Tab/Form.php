<?php
class Holbi_Qixol_Block_Adminhtml_Storesmap_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form {

    protected function _prepareForm() {
        $hlp=Mage::helper('qixol');
        $form = new Varien_Data_Form();
        $this->setForm($form);
        $fieldset = $form->addFieldset('storesmap_form', array('legend' => $hlp->__('Item information')));
        //$version = substr(Mage::getVersion(), 0, 3);
        //$config = (($version == '1.4' || $version == '1.5') ? "'config' => Mage::getSingleton('quxol/wysiwyg_config')->getConfig()" : "'class'=>''");



        $store_name_array_list=Array();
        $list_map_names_exists=array();

        foreach ($list_map_names as $list_map)
        {
            $list_map_names_exists[$list_map->getWebsite()][$list_map->getStoreGroup()][$list_map->getStoreName()] = $list_map->getStoreName();
        }

        $dropDownList = array();
        
        foreach (Mage::app()->getWebsites() as $website)
        {
            $storeGroups = array();
            
            foreach ($website->getGroups() as $group)
            {
                $stores = $group->getStores();
                $storesArray = array();
                foreach ($stores as $store)
                {
                    $storesArray[] = array(
                        'value' => $website->getName() . '::' . $group->getName() . '::' . $store->getName(),
                        'label' => $store->getName()
                    );
                    /*
                    $store_name_array_list[$website->getName()][$group->getName()][$store->getName()] = $store->getName();
                    if (isset($list_map_names_exists[$website->getName()][$group->getName()][$store->getName()]))
                    {
                        unset($store_name_array_list[$website->getName()][$group->getName()][$store->getName()]);
                    }
                    */
                }
                $storeGroups[] = array(
                    'label' => $group->getName(),
                    'value' => $storesArray
                );
            }
            $dropDownList[] = array(
                'label' => $website->getName(),
                'value' => $storeGroups
            );
        }

//        if (count($list_map_names_exists))
//        {
//            foreach ($list_map_names_exists as $exists_old_code)
//            {
//                $store_name_array_list[$exists_old_code] = $hlp->__($exists_old_code);
//            }
//        }

        $fieldset->addField('store_dropdown', 'select', array(
            'label' => Mage::helper('qixol')->__('Store Name'),
            'class' => 'required-entry',
            'required' => true,
            'name' => 'store_dropdown',
            'values' => $dropDownList
        ));

        $fieldset->addField('integration_code', 'text', array(
            'label' => Mage::helper('qixol')->__('Integration Code'),
            'class' => 'required-entry',
            'required' => true,
            'name' => 'integration_code',
            'after_element_html' => Mage::helper('qixol')->__('Code to be sent to Promo.'),
        ));

        $fieldset->addField('website', 'hidden', array(
            'name' => 'website'
        ));

        $fieldset->addField('store_group', 'hidden', array(
            'name' => 'store_group'
        ));

        $lastField = $fieldset->addField('store_name', 'hidden', array(
            'name' => 'store_name'
        ));

        $lastField->setAfterElementHtml($this->prepareScript());

        if (Mage::getSingleton('adminhtml/session')->getStoresmapData()) {
            $form->setValues(Mage::getSingleton('adminhtml/session')->getStoresmapData());
            Mage::getSingleton('adminhtml/session')->getStoresmapData(null);
        } elseif (Mage::registry('storesmap_data')) {
            $form->setValues(Mage::registry('storesmap_data')->getData());
        }
        return parent::_prepareForm();
    }

    private function prepareScript()
    {
        return '<script>
          //< ![C
            function store_dropdown_onChange(){
                setDataFields();
            }

            function setDataFields() {
                var storeDetails = $("store_dropdown").value.split(\'::\');
                $("website").value = storeDetails[0];
                $("store_group").value = storeDetails[1];
                $("store_name").value = storeDetails[2];
            }
            
            document.observe("dom:loaded", function() {

                $("store_dropdown").observe("change",function(e){
                       store_dropdown_onChange();
                });

                setDataFields();

            });


          //]]>
          </script>';
    }
}