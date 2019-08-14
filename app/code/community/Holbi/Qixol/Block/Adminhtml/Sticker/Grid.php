<?php
class Holbi_Qixol_Block_Adminhtml_Sticker_Grid extends Holbi_Qixol_Block_Adminhtml_Widget_Grid {

    public function __construct() {
        parent::__construct();
        $this->setId('stickerGrid');
        $this->setDefaultSort('sticker_id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection() {
        $collection = Mage::getModel('qixol/sticker')->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns() {
        $this->addColumn('sticker_id', array(
            'header' => Mage::helper('qixol')->__('ID'),
            'align' => 'center',
            'width' => '30px',
            'index' => 'sticker_id',
        ));

        $this->addColumn('filename', array(
            'header' => Mage::helper('qixol')->__('Image'),
            'align' => 'center',
            'index' => 'filename',
            'type' => 'sticker',
            'escape' => true,
            'sortable' => false,
            'width' => '150px',
        ));

        $this->addColumn('banner_link_name', array(
            'header' => Mage::helper('qixol')->__('Link Name'),
            'width' => '250px',
            'index' => 'banner_link_name',
        ));        

        $this->addColumn('use_default_banner_group', array(
            'header' => Mage::helper('qixol')->__('Use Dafault Banner Promotion?'),
            'align' => 'left',
            'width' => '80px',
            'index' => 'use_default_banner_group',
            'type' => 'options',
            'options' => array(
                0 => Mage::helper('qixol')->__('No'),
                1 => Mage::helper('qixol')->__('Yes'),
            ),
        ));


        $this->addColumn('default_banner_group', array(
            'header' => Mage::helper('qixol')->__('Default Banner Promotion name'),
            'width' => '100px',
            'index' => 'default_banner_group',
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
                'PRODUCTSREDUCTION' => Mage::helper('qixol')->__('Product reduction'),
            ),
        ));

        $this->addColumn('unique_banner_group', array(
            'header' => Mage::helper('qixol')->__('Unique Banner Promotion refference'),
            'width' => '250px',
            'index' => 'unique_banner_group',
        ));    

        $this->addColumn('status', array(
            'header' => Mage::helper('qixol')->__('Status'),
            'align' => 'left',
            'width' => '80px',
            'index' => 'status',
            'type' => 'options',
            'options' => array(
                0 => 'Disabled',
                1 => 'Enabled',
            ),
        ));

        $this->addColumn('sort_order', array(
            'header' => Mage::helper('qixol')->__('Sort Order'),
            'width' => '80px',
            'index' => 'sort_order',
            'align' => 'center',
        ));


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

        return parent::_prepareColumns();
    }

    protected function _prepareMassaction() {
        $this->setMassactionIdField('sticker_id');
        $this->getMassactionBlock()->setFormFieldName('sticker');

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