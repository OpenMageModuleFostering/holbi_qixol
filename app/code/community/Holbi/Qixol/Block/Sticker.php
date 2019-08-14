<?php
class Holbi_Qixol_Block_Sticker extends Mage_Core_Block_Template {

    public function _prepareLayout() {
        return parent::_prepareLayout();
    }

    public function getSticker() {
        if (!$this->hasData('sticker')) {
            $this->setData('sticker', Mage::registry('sticker'));
        }
        return $this->getData('sticker');
    }

    public function getResizeImage($bannerPath, $groupName, $w = 0, $h = 0) {
        $name = '';
        $_helper = Mage::helper('qixol');
        $bannerDirPath = $_helper->updateDirSepereator($bannerPath);
        $baseDir = Mage::getBaseDir();
        $mediaDir = Mage::getBaseDir('media');
        $mediaUrl = Mage::getBaseUrl('media');
        $resizeDir = $mediaDir . DS . 'custom' . DS . 'stickers' . DS . 'resize' . DS;
        $resizeUrl = $mediaUrl.'custom/stickers/resize/';
        $imageName = basename($bannerDirPath);

        if (@file_exists($mediaDir . DS . $bannerDirPath)) {
            $name = $mediaDir . DS . $bannerPath;
            $this->checkDir($resizeDir . $groupName);
            $smallImgPath = $resizeDir . $groupName . DS . $imageName;
            $smallImg = $resizeUrl . $groupName .'/'. $imageName;
        }

        if ($name != '') {
            $resizeObject = Mage::getModel('qixol/stickerresize');
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