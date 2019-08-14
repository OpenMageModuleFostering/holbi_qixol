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

          foreach ($list_map_names as $list_map){
              $list_map_names_exists[$list_map->getStoreName()]=$list_map->getStoreName();
          }


            foreach (Mage::app()->getWebsites() as $website) {
                foreach ($website->getGroups() as $group) {
                    $stores = $group->getStores();
                    foreach ($stores as $store) {
                      $store_name_array_list[$store->getName()] = $store->getName();
                      if (isset($list_map_names_exists[$store->getName()])) unset($store_name_array_list[$store->getName()]);
                    }
                }
            }

          if (count($list_map_names_exists)){
              foreach ($list_map_names_exists as $exists_old_code)
                      $store_name_array_list[$exists_old_code] = $hlp->__($exists_old_code);

           }



        $fieldset->addField('store_name', 'select', array(
            'label' => Mage::helper('qixol')->__('Store Name Magento:'),
            'class' => 'required-entry',
            'required' => true,
            'name' => 'store_name',
            'values' => $store_name_array_list
        ));

        $fieldset->addField('store_name_map', 'text', array(
            'label' => Mage::helper('qixol')->__('Store Name To'),
            'class' => 'required-entry',
            'required' => true,
            'name' => 'store_name_map',
            'after_element_html' => Mage::helper('qixol')->__('Name to be send to quxiol.'),
        ));


        if (Mage::getSingleton('adminhtml/session')->getStoresmapData()) {
            $form->setValues(Mage::getSingleton('adminhtml/session')->getStoresmapData());
            Mage::getSingleton('adminhtml/session')->getStoresmapData(null);
        } elseif (Mage::registry('storesmap_data')) {
            $form->setValues(Mage::registry('storesmap_data')->getData());
        }
        return parent::_prepareForm();
    }

}