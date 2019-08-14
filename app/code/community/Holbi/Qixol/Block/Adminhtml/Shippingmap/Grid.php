<?php
class Holbi_Qixol_Block_Adminhtml_Shippingmap_Grid extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct() {
        parent::__construct();
        $this->setId('shippingmapGrid');
        $this->setDefaultSort('shipping_name_map');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection() {
        $collection = Mage::getModel('qixol/shippingmap')->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns() {
        $hlp = Mage::helper('qixol');
        $list_map_names=Mage::getModel('qixol/shippingmap')->getCollection();

        $shipping_name_array_list=Array();
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
                  if(!$_title = Mage::getStoreConfig("carriers/$_ccode/title"))
                      $_title = $_ccode;

                  foreach($_methods as $_mcode => $_method)
                  {
                      $_code = $_ccode . '_' . $_mcode;
                      $shipping_name_array_list[$_code] = $hlp->__(trim($_method)==''?$_code:$_method)." - /".$_code."/";
                      if (isset($list_map_names_exists[$_code])) unset($shipping_name_array_list[$_code]);
                  }


                 // $options[] = array('value' => $_methodOptions, 'label' => $hlp->__($_title));*/
              }
            }
            catch(Exception $e) {
            continue;
            }
          }

          if (count($list_map_names_exists)){
              foreach ($list_map_names_exists as $exists_old_code)
                      $shipping_name_array_list[$exists_old_code] = $hlp->__($exists_old_code);

           }


        
        $this->addColumn('shipping_name', array(
            'header'        => $hlp->__('Shipping Name Magento:'),
            'type'          => 'shipping',
            'align'         => 'left',
            'width' => '250px',
            'index'         => 'shipping_name',
            'type' => 'options',
            'options' => $shipping_name_array_list
        ));



        $this->addColumn('shipping_name_map', array(
            'header' => $hlp->__('Shipping Name To'),
            'width' => '350px',
            'index' => 'shipping_name_map'
        ));

        $this->addColumn('action',
                array(
                    'header' => $hlp->__('Action'),
                    'width' => '80',
                    'type' => 'action',
                    'getter' => 'getId',
                    'actions' => array(
                        array(
                            'caption' => $hlp->__('Edit'),
                            'url' => array('base' => '*/*/edit'),
                            'field' => 'id'
                        ),
                        array(
                            'caption' => $hlp->__('Delete'),
                            'url' => array('base' => '*/*/delete'),
                            'field' => 'id'
                        )
                    ),
                    'filter' => false,
                    'sortable' => false,
                    'index' => 'stores',
                    'is_system' => true,
        ));

        return parent::_prepareColumns();
    }

    public function getRowUrl($row) {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }

}