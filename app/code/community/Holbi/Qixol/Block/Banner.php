<?php
class Holbi_Qixol_Block_Banner extends Mage_Core_Block_Template {

    public function _prepareLayout() {
        return parent::_prepareLayout();
    }

    public function getBanner() {
        if (!$this->hasData('banner')) {
            $this->setData('banner', Mage::registry('banner'));
        }
        return $this->getData('banner');
    }

    public function getResizeImage($bannerPath, $groupName, $w = 0, $h = 0) {
        $name = '';
        $_helper = Mage::helper('qixol');
        $bannerDirPath = $_helper->updateDirSepereator($bannerPath);
        $baseDir = Mage::getBaseDir();
        $mediaDir = Mage::getBaseDir('media');
        $mediaUrl = Mage::getBaseUrl('media');
        $resizeDir = $mediaDir . DS . 'custom' . DS . 'banners' . DS . 'resize' . DS;
        $resizeUrl = $mediaUrl.'custom/banners/resize/';
        $imageName = basename($bannerDirPath);

        if (@file_exists($mediaDir . DS . $bannerDirPath)) {
            $name = $mediaDir . DS . $bannerPath;
            $this->checkDir($resizeDir . $groupName);
            $smallImgPath = $resizeDir . $groupName . DS . $imageName;
            $smallImg = $resizeUrl . $groupName .'/'. $imageName;
        }

        if ($name != '') {
            $resizeObject = Mage::getModel('qixol/bannerresize');
            $resizeObject->setImage($name);
            if ($resizeObject->resizeLimitwh($w, $h, $smallImgPath) === false) {
                return $resizeObject->error();
            } else {                
                return $smallImg;                
            }
        } else {
            return '';
        }
    }

    protected function checkDir($directory) {
        if (!is_dir($directory)) {
            umask(0);
            mkdir($directory, 0777,true);
            return true;
        }
    }

}