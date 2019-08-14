<?php
class Holbi_Qixol_Block_Adminhtml_Banner_Edit_Bannerimage_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setDefaultSort('sort_order');
        $this->setDefaultDir('asc');
        $this->setId('bannerImageGrid');
        $this->setDefaultFilter(array('banner_id'=>1));
        $this->setUseAjax(true);
    }

    protected function _addColumnFilterToCollection($column)
    {
        if ($column->getId() == 'banner_id') {
            $inBannerIds = $this->_getBannerimages();
            if (empty($inBannerIds)) {
                $inBannerIds = 0;
            }
            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter('banner_id', array('in'=>$inBannerIds));
            }
            else {
                if($inBannerIds) {
                    $this->getCollection()->addFieldToFilter('banner_id', array('nin'=>$inBannerIds));
                }
            }
        }
        else {
            parent::_addColumnFilterToCollection($column);
        }
        return $this;
    }

    protected function _prepareCollection()
    {
        $bannerId = $this->getRequest()->getParam('id');
        Mage::register('BANNERID', $bannerId);
        $collection = Mage::getModel('qixol/banner')->getBannerImageCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('banner_id', array(
            'header'            => Mage::helper('qixol')->__('Banner id'),
            'name'              => 'banner_id',
            'index'             => 'banner_id',
            'values'            => $this->_getBannerimages(),
            'column_css_class'  => 'no-display',
            'header_css_class'  => 'no-display'
        ));

        $this->addColumn('banner_image_id', array(
            'header'            => Mage::helper('qixol')->__('Id'),
            'name'              => 'banner_image_id',
            'index'             => 'banner_image_id',
            'column_css_class'  => 'no-display',
            'header_css_class'  => 'no-display'
        ));

        $this->addColumn('filename', array(
            'header'    =>Mage::helper('qixol')->__('Filename'),
            'name'      => 'filename',
            'index'     => 'filename'
        ));

        $this->addColumn('sort_order', array(
            'header'    =>Mage::helper('qixol')->__('Sort order'),
            'name'      => 'sort_order',
            'index'     => 'sort_order'
        ));

        $this->addColumn('promotion_reference', array(
            'header'    =>Mage::helper('qixol')->__('Promotion reference'),
            'name'      => 'promotion_reference',
            'index'     => 'promotion_reference'
        ));

        $this->addColumn('comment', array(
            'header'    =>Mage::helper('qixol')->__('Comment'),
            'name'      => 'comment',
            'index'     => 'comment'
        ));

        $this->addColumn('url', array(
            'header'    =>Mage::helper('qixol')->__('URL'),
            'name'      => 'url',
            'index'     => 'url'
        ));

       /*
        $this->addColumn('grid_actions',
            array(
                'header'=>Mage::helper('adminhtml')->__('Actions'),
                'width'=>5,
                'sortable'=>false,
                'filter'    =>false,
                'type' => 'action',
                'actions'   => array(
                                    array(
                                        'caption' => Mage::helper('adminhtml')->__('Remove'),
                                        'onClick' => 'role.deleteFromRole($role_id);'
                                    )
                                )
            )
        );
        */

        return parent::_prepareColumns();
    }
//
//    public function getGridUrl()
//    {
//        $bannerId = $this->getRequest()->getParam('id');
//        // TODO: {action} is...?
//        return $this->getUrl('*/*/{action}', array('id' => $bannerId));
//    }

    public function getRowUrl($row) {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }
    
    protected function _getBannerimages($json=false)
    {
        $bannerId = ( $this->getRequest()->getParam('id') > 0 ) ? $this->getRequest()->getParam('id') : Mage::registry('BANNERID');
        $bannerImages = Mage::getModel('qixol/banner')->setId($bannerId)->getBannerImageIds();

        if (count($bannerImages) > 0) {
            if ( $json ) {
                $jsonBannerImages = Array();
                foreach($bannerImages as $bannerImage) $jsonBannerImages[$bannerImage] = 0;
                return Mage::helper('core')->jsonEncode((object)$jsonBannerImages);
            } else {
                return array_values($bannerImages);
            }
        } else {
            if ( $json ) {
                return '{}';
            } else {
                return array();
            }
        }
    }
}

