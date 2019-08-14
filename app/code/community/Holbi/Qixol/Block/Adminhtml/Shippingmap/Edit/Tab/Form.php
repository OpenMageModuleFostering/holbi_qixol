<?php
class Holbi_Qixol_Block_Adminhtml_Shippingmap_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form {

    protected function _prepareForm() {
        $hlp=Mage::helper('qixol');
        $form = new Varien_Data_Form();
        $this->setForm($form);
        $fieldset = $form->addFieldset('shippingmap_form', array('legend' => $hlp->__('Item information')));
        //$version = substr(Mage::getVersion(), 0, 3);
        //$config = (($version == '1.4' || $version == '1.5') ? "'config' => Mage::getSingleton('quxol/wysiwyg_config')->getConfig()" : "'class'=>''");



        $shipping_name_array_list=Array();
        

          $list_map_names=Mage::getModel('qixol/shippingmap')->getCollection();
          $list_map_names_exists=array();

          foreach ($list_map_names as $list_map){
              $list_map_names_exists[$list_map->getShippingName()]=$list_map->getShippingName();
          }
          //returns only active list
          $only_active=Mage::getStoreConfig('qixol/shippings/onlyactive');
          /*if ($only_active>0)
             $methods = Mage::getSingleton('shipping/config')->getActiveCarriers();
          else */
          $methods = Mage::getSingleton('shipping/config')->getAllCarriers();

          //$options = array();

          foreach($methods as $_ccode => $_carrier)
          {
              $_methodOptions = array();
           try{ //some methods not allowed getAllowedMethods
              if($_methods = $_carrier->getAllowedMethods())
              {
                  foreach($_methods as $_mcode => $_method)
                  {
                      $_code = $_ccode . '_' . $_mcode;
                      $shipping_name_array_list[] = array('value'=>$_code ,'label'=>$hlp->__(trim($_method)==''?$_code:$_method));
                      if (isset($list_map_names_exists[$_code])) unset($_code);
                  }

                 /* if(!$_title = Mage::getStoreConfig("carriers/$_ccode/title"))
                      $_title = $_ccode;

                  $options[] = array('value' => $_methodOptions, 'label' => $hlp->__($_title));*/
              }
            }
            catch(Exception $e) {
            continue;
            }
          }
          if (count($list_map_names_exists)){
              foreach ($list_map_names_exists as $exists_old_code)
                      $shipping_name_array_list[] = array('value'=>$exists_old_code,'label'=>$hlp->__($exists_old_code));

           }



        $fieldset->addField('shipping_name', 'select', array(
            'label' => Mage::helper('qixol')->__('Shipping Name Magento:'),
            'class' => 'required-entry',
            'required' => true,
            'name' => 'shipping_name',
            'values' => $shipping_name_array_list
        ));

        $fieldset->addField('shipping_name_map', 'text', array(
            'label' => Mage::helper('qixol')->__('Shipping Name'),
            'class' => 'required-entry',
            'required' => true,
            'name' => 'shipping_name_map',
            'after_element_html' => Mage::helper('qixol')->__('Name to be send to quxion.'),
        ));


        if (Mage::getSingleton('adminhtml/session')->getShippingmapData()) {
            $form->setValues(Mage::getSingleton('adminhtml/session')->getShippingmapData());
            Mage::getSingleton('adminhtml/session')->setShippingmapData(null);
        } elseif (Mage::registry('shippingmap_data')) {
            $form->setValues(Mage::registry('shippingmap_data')->getData());
        }
        return parent::_prepareForm();
    }

}