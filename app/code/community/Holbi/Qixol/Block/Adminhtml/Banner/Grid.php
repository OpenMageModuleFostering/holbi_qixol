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

        $this->addColumn('title', array(
            'header' => Mage::helper('qixol')->__('Title'),
            'width' => '150px',
            'index' => 'title',
        ));

        $this->addColumn('display_zone', array(
            'header' => Mage::helper('qixol')->__('Display Zone'),
            'width' => '250px',
            'index' => 'display_zone',
        ));        

        /* TODO: should use the Holbi_Qixol_Model_Status class for this? */
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