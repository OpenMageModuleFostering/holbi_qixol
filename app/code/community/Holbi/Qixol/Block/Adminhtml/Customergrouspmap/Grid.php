<?php
class Holbi_Qixol_Block_Adminhtml_Customergrouspmap_Grid extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct() {
        parent::__construct();
        $this->setId('customergrouspmapGrid');
        $this->setDefaultSort('integration_code');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection() {
        $collection = Mage::getModel('qixol/customergrouspmap')->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns() {
        $hlp = Mage::helper('qixol');
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


        
        $this->addColumn('customer_group_name', array(
            'header'        => $hlp->__('Customer Group'),
            'type'          => 'customer',
            'align'         => 'left',
            'width' => '250px',
            'index'         => 'customer_group_name',
            'type' => 'options',
            'options' => $customer_group_name_array_list
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