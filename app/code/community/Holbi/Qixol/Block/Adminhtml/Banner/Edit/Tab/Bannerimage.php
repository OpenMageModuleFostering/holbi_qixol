<?php
class Holbi_Qixol_Block_Adminhtml_Banner_Edit_Tab_Bannerimage extends Mage_Adminhtml_Block_Widget_Tabs {

    public function __construct()
    {
        parent::__construct();

        $bannerId = $this->getRequest()->getParam('id', false);

        $bannerImages = Mage::getModel("qixol/bannerimage")->getCollection()->load();
        $this->setTemplate('qixol/bannerimage.phtml')
            ->assign('bannerImages', $bannerImages->getItems())
            ->assign('bannerId', $bannerId);
    }

    protected function _prepareLayout()
    {
        $this->setChild('bannerImageGrid', $this->getLayout()->createBlock('qixol/adminhtml_banner_edit_bannerimage_grid', 'bannerImageGrid'));
        return parent::_prepareLayout();
    }

    protected function _getGridHtml()
    {
        return $this->getChildHtml('bannerImageGrid');
    }

    protected function _getJsObjectName()
    {
        return $this->getChild('bannerImageGrid')->getJsObjectName();
    }
}
