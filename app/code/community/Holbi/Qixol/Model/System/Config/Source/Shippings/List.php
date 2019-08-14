<?php
class Holbi_Qixol_Model_System_Config_Source_Shippings_List
{

    public function toOptionArray(){
        $hlp = Mage::helper('qixol');
        //$methods = Mage::getSingleton('shipping/config')->getActiveCarriers();
        $methods = Mage::getSingleton('shipping/config')->getAllCarriers();

          $options = array();

          foreach($methods as $_ccode => $_carrier)
          {
              $_methodOptions = array();
           try{ //some methods not allowed getAllowedMethods
              if($_methods = $_carrier->getAllowedMethods())
              {
                  foreach($_methods as $_mcode => $_method)
                  {
                      $_code = $_ccode . '_' . $_mcode;
                      $_methodOptions[] = array('value' => $_code, 'label' => $hlp->__(trim($_method)==''?$_code:$_method));
                  }

                  if(!$_title = Mage::getStoreConfig("carriers/$_ccode/title"))
                      $_title = $_ccode;

                  $options[] = array('value' => $_methodOptions, 'label' => $hlp->__($_title));
              }
            }
            catch(Exception $e) {
            continue;
            }
          }

          return $options;
    }

}