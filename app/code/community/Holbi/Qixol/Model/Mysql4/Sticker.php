<?php
class Holbi_Qixol_Model_Mysql4_Sticker extends Mage_Core_Model_Mysql4_Abstract {
    private $category_stickers_advertisment_name='CATEGORY_STICKERS';
    private $product_stickers_advertisment_name='PRODUCT_INFO_STICKERS';

    public function _construct() {
        // Note that the banner_id refers to the key field in your database table.
        $this->_init('qixol/sticker', 'sticker_id');
    }

    public function getStickerImage($product,$adv_type='product') {
       //create the list of product->child if parent
       $product_id=$product->getId();
       $child_ids=array();
       if ($product->isConfigurable()){
           $associatedProducts = $product->getTypeInstance()->getConfigurableAttributesAsArray($product); 
           foreach ($product->getTypeInstance()->getUsedProducts() as $childProduct) {
               $child_ids[]=$childProduct->getId();
           }
       }

       switch ($adv_type){
          case "category":
              $banner_link_name=$this->category_stickers_advertisment_name;
          break;
          case "product":
          default :
              $banner_link_name=$this->product_stickers_advertisment_name;
          break;
       }

       $condition_1 = $this->_getReadAdapter()->quoteInto('(qphp.promotion_id=pt.promotion_id)','');
       $condition_2 = $this->_getReadAdapter()->quoteInto('((b.use_default_banner_group>0 && b.default_banner_group=pt.promotion_type) or (b.use_default_banner_group=0 && pt.yourref!="" && b.unique_banner_group=pt.yourref))','');
       $select = $this->_getReadAdapter()->select()->from(array('qphp'=>$this->getTable('promotionhasproduct')))
                ->join(array('pt'=>$this->getTable('promotions')), $condition_1)
                ->join(array('b'=>$this->getTable('qixol/sticker')), $condition_2)
                ->where((count($child_ids)?
                            " ((qphp.parent_product_id='".(int)$product_id."' and qphp.product_id in (".join(",",$child_ids).")) or (qphp.product_id='".(int)$product_id."' and qphp.parent_product_id=0) )":
                            " qphp.product_id='".(int)$product_id."' and qphp.parent_product_id=0")." and b.status>0 and b.filename!='' and b.banner_link_name like '%".$banner_link_name."%'".
                            " and (pt.is_everyday=0 or (pt.from_date='0000-00-00 00:00:00' or TIME(pt.from_date)<CURTIME()) and (pt.till_date='0000-00-00 00:00:00' or TIME(pt.till_date)>CURTIME()))" )
// I don't know why by they have only time when promotion works(so for all dates)
                ->group("qphp.promotion_id")->order('b.sort_order')
                ->reset('columns')->columns(array('b.filename'));

       $data=$this->_getReadAdapter()->fetchAll($select);
       if (count($data)) return $data;
       return false;
    }

}