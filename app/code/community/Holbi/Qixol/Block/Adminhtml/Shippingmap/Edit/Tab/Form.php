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
          
          //$carriers = Mage::getSingleton('shipping/config')->getActiveCarriers();
          $carriers = Mage::getSingleton('shipping/config')->getAllCarriers();

          $shippingMethodDropDownValues = array();
          
          foreach($carriers as $_ccode => $_carrier)
          {
              $carrierMethods = array();
              
                if(!$_title = Mage::getStoreConfig("carriers/$_ccode/title"))
                {
                        $_title = $_ccode;
                }

           try{ //some methods not allowed getAllowedMethods
              if($_methods = $_carrier->getAllowedMethods())
              {
                  foreach($_methods as $_mcode => $_method)
                  {
                      $_code = $_ccode . '_' . $_mcode;
                      $shippingMethod = $hlp->__(trim($_method) == '' ? $_code : $_method);
                      $carrierMethods[] = array(
                          'value' => $_code . '::' . $_title . '::' . $shippingMethod,
                          'label' => $shippingMethod
                        );
                      if (isset($list_map_names_exists[$_code]))
                      {
                          unset($_code);
                      }
                  }

                $shippingMethodDropDownValues[] = array('label' => $_title, 'value' => $carrierMethods);
              }
            }
            catch(Exception $e) {
            continue;
            }
              
          }

//$shipping_name_array_list = array(
//    array(
//        'label' => 'Flatrate',
//        'value' =>  array(array('label' => 'Fixed', 'value' => 'flatrate_flatrate'))
//    ),
//    array(
//        'label' => 'Free Shipping',
//        'value' => array(array('label' => 'Free', 'value' => 'freeshipping_freeshipping'))
//    ),
//    array(
//        'label' => 'Federal Express',
//        'value' => array(
//                        array('label' => '2 Day', 'value' => 'fedex_FEDEX_2_DAY'),
//                        array('label' => 'Ground', 'value' => 'fedex_FEDEX_GROUND'),
//                        array('label' => 'First Overnight', 'value' => 'fedex_FIRST_OVERNIGHT')
//    ))
//);

        $fieldset->addField('shipping_method', 'select', array(
            'label' => Mage::helper('qixol')->__('Shipping Method'),
            'class' => 'required-entry',
            'required' => true,
            'name' => 'shipping_method',
            'values' => $shippingMethodDropDownValues
        ));

        $fieldset->addField('integration_code', 'text', array(
            'label' => Mage::helper('qixol')->__('Integration Code'),
            'class' => 'required-entry',
            'required' => true,
            'name' => 'integration_code',
            'after_element_html' => Mage::helper('qixol')->__('Code to be synchronised to Promo'),
        ));

        $fieldset->addField('shipping_name', 'hidden', array(
            'name' => 'shipping_name'
        ));
        
        $fieldset->addField('carrier_title', 'hidden', array(
            'name' => 'carrier_title'
        ));

        $lastField = $fieldset->addField('carrier_method', 'hidden', array(
            'name' => 'carrier_method'
        ));

                
        $lastField->setAfterElementHtml($this->prepareScript());

        if (Mage::getSingleton('adminhtml/session')->getShippingmapData()) {
            $form->setValues(Mage::getSingleton('adminhtml/session')->getShippingmapData());
            Mage::getSingleton('adminhtml/session')->setShippingmapData(null);
        } elseif (Mage::registry('shippingmap_data')) {
            $form->setValues(Mage::registry('shippingmap_data')->getData());
        }
        return parent::_prepareForm();
    }

    private function prepareScript()
    {
        return '<script>
          //< ![C
            function shipping_method_onChange(){
                setDataFields();
            }

            function setDataFields() {
                var shippingMethodDetails = $("shipping_method").value.split(\'::\');
                $("shipping_name").value = shippingMethodDetails[0];
                $("carrier_title").value = shippingMethodDetails[1];
                $("carrier_method").value = shippingMethodDetails[2];
            }
            
            document.observe("dom:loaded", function() {

                $("shipping_method").observe("change",function(e){
                       shipping_method_onChange();
                });

                setDataFields();

            });


          //]]>
          </script>';
    }
}