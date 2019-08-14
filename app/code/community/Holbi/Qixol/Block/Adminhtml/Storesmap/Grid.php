<?php
class Holbi_Qixol_Block_Adminhtml_Storesmap_Grid extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct() {
        parent::__construct();
        $this->setId('storesmapGrid');
        $this->setDefaultSort('store_name');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection() {
        $collection = Mage::getModel('qixol/storesmap')->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns() {
        $hlp = Mage::helper('qixol');
        $list_map_names=Mage::getModel('qixol/storesmap')->getCollection();

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


        $this->addColumn('website', array(
            'header'        => $hlp->__('Website'),
            'align'         => 'left',
            'width' => '250px',
            'index'         => 'website'
        ));

        $this->addColumn('store_group', array(
            'header'        => $hlp->__('Store Group'),
            'align'         => 'left',
            'width' => '250px',
            'index'         => 'store_group'
        ));
        
        $this->addColumn('store_name', array(
            'header'        => $hlp->__('Store Name'),
            'type'          => 'stores',
            'align'         => 'left',
            'width' => '250px',
            'index'         => 'store_name',
            'type' => 'options',
            'options' => $store_name_array_list
        ));



        $this->addColumn('integration_code', array(
            'header' => $hlp->__('Integration Code'),
            'width' => '350px',
            'index' => 'integration_code'
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