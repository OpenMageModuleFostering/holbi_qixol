<?php
class Holbi_Qixol_Block_Adminhtml_Bannerboxes_Grid extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct() {
        parent::__construct();
        $this->setId('bannerGrid');
        $this->setDefaultSort('banner_box_type');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection() {
        $collection = Mage::getModel('qixol/bannerboxes')->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns() {

        $this->addColumn('banner_box_type', array(
            'header' => Mage::helper('qixol')->__('Banner Box Type'),
            'width' => '100px',
            'index' => 'banner_box_type',
            'type' => 'options',
            'options' => array(
                'CATEGORY_TOP' => 'CATEGORY_TOP',
                'PRODUCT_BOTTOM' => 'PRODUCT_BOTTOM',
                'PRODUCT_TOP' => 'PRODUCT_TOP',
                'PRODUCT_INLINE' => 'PRODUCT_INLINE',
                'BASKET_INLINE' => 'BASKET_INLINE',
                'CATEGORY_STICKERS' => 'CATEGORY_STICKERS',
                'PRODUCT_INFO_STICKERS' => 'PRODUCT_INFO_STICKERS',
            ),
        ));

        $this->addColumn('banner_box_is_active', array(
            'header' => Mage::helper('qixol')->__('Enabled?'),
            'align' => 'left',
            'width' => '80px',
            'index' => 'banner_box_is_active',
            'type' => 'options',
            'options' => array(
                1 => 'Enabled',
                2 => 'Disabled',
            ),
        ));

        $this->addColumn('banner_box_translation_type', array(
            'header' => Mage::helper('qixol')->__('Translate Type'),
            'width' => '80px',
            'index' => 'banner_box_translation_type',
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

        //$this->addExportType('*/*/exportCsv', Mage::helper('qixol')->__('CSV'));
        //$this->addExportType('*/*/exportXml', Mage::helper('qixol')->__('XML'));
        return parent::_prepareColumns();
    }

    public function getRowUrl($row) {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }

}