<?php
class Holbi_Qixol_Block_Adminhtml_Banner_Grid extends Holbi_Qixol_Block_Adminhtml_Widget_Grid {

    public function __construct() {
        parent::__construct();
        $this->setId('bannerGrid');
        $this->setDefaultSort('banner_id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection() {
        $collection = Mage::getModel('qixol/banner')->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns() {
        $this->addColumn('banner_id', array(
            'header' => Mage::helper('qixol')->__('ID'),
            'align' => 'center',
            'width' => '30px',
            'index' => 'banner_id',
        ));

        /*$this->addColumn('filename', array(
            'header' => Mage::helper('qixol')->__('Image'),
            'align' => 'center',
            'index' => 'filename',
            'type' => 'banner',
            'escape' => true,
            'sortable' => false,
            'width' => '150px',
        ));*/

        $this->addColumn('title', array(
            'header' => Mage::helper('qixol')->__('Title'),
            'width' => '150px',
            'index' => 'title',
        ));

        $this->addColumn('banner_link_name', array(
            'header' => Mage::helper('qixol')->__('Display Zone'),
            'width' => '250px',
            'index' => 'banner_link_name',
        ));        


        $this->addColumn('banner_group', array(
            'header' => Mage::helper('qixol')->__('Promotion Ref'),
            'width' => '250px',
            'index' => 'banner_group',
        ));   

        /*$this->addColumn('banner_group', array(
            'header' => Mage::helper('qixol')->__('Promotion Ref'),
            'width' => '100px',
            'index' => 'banner_group',
            'type' => 'options',
            'options' => array(
                '' => Mage::helper('qixol')->__('Not defined'),
                'BOGOF' => Mage::helper('qixol')->__('Buy one get one free'),
                'BOGOR' => Mage::helper('qixol')->__('Buy one get one reduced'),
                'BUNDLE' => Mage::helper('qixol')->__('Bundle'),
                'DEAL' => Mage::helper('qixol')->__('Deal'),
                'FREEPRODUCT' => Mage::helper('qixol')->__('Free product'),
                'ISSUECOUPON' => Mage::helper('qixol')->__('Issue coupon'),

                'ISSUEPOINTS' => Mage::helper('qixol')->__('Issue points'),
                'BASKETREDUCTION' => Mage::helper('qixol')->__('Basket reduction'),
                'DELIVERYREDUCTION' => Mage::helper('qixol')->__('Delivery coupon'),

                /*7 => 'Issue points',
                8 => 'Multiple promos',*/
                /*'PRODUCTSREDUCTION' => Mage::helper('qixol')->__('Product reduction'),
            ),
        ));*/

        $this->addColumn('status', array(
            'header' => Mage::helper('qixol')->__('Status'),
            'align' => 'left',
            'width' => '80px',
            'index' => 'status',
            'type' => 'options',
            'options' => array(
                1 => 'Enabled',
                0 => 'Disabled',
            ),
        ));

        $this->addColumn('sort_order', array(
            'header' => Mage::helper('qixol')->__('Sort Order'),
            'width' => '80px',
            'index' => 'sort_order',
            'align' => 'center',
        ));

        /*$this->addColumn('is_default', array(
            'header' => Mage::helper('qixol')->__('Default?'),
            'align' => 'left',
            'width' => '80px',
            'index' => 'is_default',
            'type' => 'options',
            'options' => array(
                1 => 'Yes',
                0 => 'No',
            ),
        ));*/

        $this->addColumn('action',
                array(
                    'header' => Mage::helper('qixol')->__('Action'),
                    'width' => '80',
                    'type' => 'action',
                    'getter' => 'getId',
                    'actions' => array(
                        array(
                            'caption' => Mage::helper('qixol')->__('Edit'),
                            'url' => array('base' => '*/*/edit'),
                            'field' => 'id'
                        )
                    ),
                    'filter' => false,
                    'sortable' => false,
                    'index' => 'stores',
                    'is_system' => true,
        ));

        //$this->addExportType('*/*/exportCsv', Mage::helper('qixol')->__('CSV'));
        //$this->addExportType('*/*/exportXml', Mage::helper('qixol')->__('XML'));

        return parent::_prepareColumns();
    }

    protected function _prepareMassaction() {
        $this->setMassactionIdField('banner_id');
        $this->getMassactionBlock()->setFormFieldName('banner');

        $this->getMassactionBlock()->addItem('delete', array(
            'label' => Mage::helper('qixol')->__('Delete'),
            'url' => $this->getUrl('*/*/massDelete'),
            'confirm' => Mage::helper('qixol')->__('Are you sure?')
        ));

        $statuses = Mage::getSingleton('qixol/status')->getOptionArray();

        array_unshift($statuses, array('label' => '', 'value' => ''));
        $this->getMassactionBlock()->addItem('status', array(
            'label' => Mage::helper('qixol')->__('Change status'),
            'url' => $this->getUrl('*/*/massStatus', array('_current' => true)),
            'additional' => array(
                'visibility' => array(
                    'name' => 'status',
                    'type' => 'select',
                    'class' => 'required-entry',
                    'label' => Mage::helper('qixol')->__('Status'),
                    'values' => $statuses
                )
            )
        ));
        return $this;
    }

    public function getRowUrl($row) {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }

}