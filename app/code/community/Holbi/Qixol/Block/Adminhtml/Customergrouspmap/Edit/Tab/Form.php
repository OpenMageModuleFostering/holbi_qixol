<?php
class Holbi_Qixol_Block_Adminhtml_Customergrouspmap_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form {

    protected function _prepareForm() {
        $hlp=Mage::helper('qixol');
        $form = new Varien_Data_Form();
        $this->setForm($form);
        $fieldset = $form->addFieldset('customergrouspmap_form', array('legend' => $hlp->__('Item information')));
        //$version = substr(Mage::getVersion(), 0, 3);
        //$config = (($version == '1.4' || $version == '1.5') ? "'config' => Mage::getSingleton('quxol/wysiwyg_config')->getConfig()" : "'class'=>''");

        $list_map_names=Mage::getModel('qixol/customergrouspmap')->getCollection();

        $customer_group_name_array_list=Array();
        $list_map_names_exists=array();

          foreach ($list_map_names as $list_map){
              $list_map_names_exists[$list_map->getCustomerGroupName()]=$list_map->getCustomerGroupName();
          }


          $customerGroupModel = new Mage_Customer_Model_Group();
          $allCustomerGroups  = $customerGroupModel->getCollection()->toOptionHash();
          foreach($allCustomerGroups as $key => $group){
            $customer_group_name_array_list[$group] = $hlp->__($group);
            if (isset($list_map_names_exists[$group])) unset($list_map_names_exists[$group]);

          }



          if (count($list_map_names_exists)){
              foreach ($list_map_names_exists as $exists_old_code)
                      $customer_group_name_array_list[$exists_old_code] = $hlp->__($exists_old_code);

           }



        $fieldset->addField('customer_group_name', 'select', array(
            'label' => Mage::helper('qixol')->__('Customer Group:'),
            'class' => 'required-entry',
            'required' => true,
            'name' => 'customer_group_name',
            'values' => $customer_group_name_array_list
        ));

        $fieldset->addField('integration_code', 'text', array(
            'label' => Mage::helper('qixol')->__('Integration Code'),
            'class' => 'required-entry',
            'required' => true,
            'name' => 'integration_code',
            'after_element_html' => Mage::helper('qixol')->__('Name to be send to qixol.'),
        ));


        if (Mage::getSingleton('adminhtml/session')->getCustomergrouspmapData()) {
            $form->setValues(Mage::getSingleton('adminhtml/session')->getCustomergrouspmapData());
            Mage::getSingleton('adminhtml/session')->getCustomergrouspmapData(null);
        } elseif (Mage::registry('customergrouspmap_data')) {
            $form->setValues(Mage::registry('customergrouspmap_data')->getData());
        }
        return parent::_prepareForm();
    }

}