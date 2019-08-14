<?php
class Holbi_Qixol_Model_Sticker extends Mage_Core_Model_Abstract {
    private $default_stickers=array('BOGOF'=>'BOGOF.png',
                                    'BOGOR'=>'BOGOR.png',
                                    'BUNDLE'=>'BUNDLE.png',
                                    'DEAL'=>'DEAL.png',
                                    'DELIVERYREDUCTION'=>'DELIVERYREDUCTION.png',
                                    'FREEPRODUCT'=>'FREEPRODUCT.png',
                                    'ISSUECOUPON'=>'ISSUECOUPON.png',
                                    'ISSUEPOINTS'=>'ISSUEPOINTS.png',
                                    'BASKETREDUCTION'=>'BASKETREDUCTION.png',
                                    'PRODUCTSREDUCTION'=>'PRODUCTSREDUCTION.png',
                                    );
    protected static $defaultstickerDir = null;
    protected static $defaultstickerURL = null;

    private $default_sticker_folder='frontend/base/default/images/qixol';

    public function _construct() {
        self::$defaultstickerDir = Mage::getBaseDir('skin') . DS;
        self::$defaultstickerURL = Mage::getBaseUrl('skin');
        parent::_construct();
        $this->_init('qixol/sticker');
    }

    public function getAllAvailStickerIds(){
        $collection = Mage::getResourceModel('qixol/sticker_collection')
                        ->getAllIds();
        return $collection;
    }

    public function getAllStickers() {
        $collection = Mage::getResourceModel('qixol/sticker_collection');
        $collection->getSelect()->where('status = ?', 1);
        $data = array();
        foreach ($collection as $record) {
            $data[$record->getId()] = array('value' => $record->getId(), 'label' => $record->getfilename());
        }
        return $data;
    }

    public function getDataByStickerIds($bannerIds) {
        $data = array();
        if ($bannerIds != '') {
            $collection = Mage::getResourceModel('qixol/sticker_collection');
            $collection->getSelect()->where('sticker_id IN (' . $bannerIds . ')')->order('sort_order');
            foreach ($collection as $record) {
                $status = $record->getStatus();
                if ($status == 1) {
                    $data[] = $record;
                }
            }
        }
        return $data;
    }

    public function getDefaultSticker($stickername) {
        $data='';
        if ($stickername!=''&&$this->default_stickers[$stickername]!=''){
           if (file_exists(self::$defaultstickerDir."/".$this->default_sticker_folder."/".$this->default_stickers[$stickername])){
               $data=self::$defaultstickerURL."/".$this->default_sticker_folder."/".$this->default_stickers[$stickername];
           }
        }
        return $data;
    }

}