<?php
class Holbi_Qixol_Model_Mysql4_Banner extends Mage_Core_Model_Mysql4_Abstract {
    private $category_top_advertisment_name='CATEGORY_TOP';
    private $product_top_advertisment_name='PRODUCT_TOP';
    private $product_bottom_advertisment_name='PRODUCT_BOTTOM';
    private $product_inline_advertisment_name='PRODUCT_INLINE';
    private $backet_inline_advertisment_name='BACKET_INLINE';
    private $category_stickers_advertisment_name='CATEGORY_STICKERS';
    private $product_stickers_advertisment_name='PRODUCT_INFO_STICKERS';

    public function _construct() {
        // Note that the banner_id refers to the key field in your database table.
        $this->_init('qixol/banner', 'banner_id');
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

       $condition_1 = $this->_getReadAdapter()->quoteInto('qphp.promotion_id=pt.promotion_id','');
       $condition_2 = $this->_getReadAdapter()->quoteInto('b.banner_group=pt.promotion_type','');
       $select = $this->_getReadAdapter()->select()->from(array('qphp'=>$this->getTable('promotionhasproduct')))
                ->join(array('pt'=>$this->getTable('promotions')), $condition_1)
                ->join(array('b'=>$this->getTable('qixol/banner')), $condition_2)
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

    public function getCategoryTopAdv($_productCollection) {
      $advertisment_text='';
      $product_to_child=array();
      foreach ($_productCollection as $_product){
          $product_to_child[$_product->getId()]=array();
            if ($_product->isConfigurable()){
                $child_ids=array();
                $associatedProducts = $_product->getTypeInstance()->getConfigurableAttributesAsArray($_product); 
                foreach ($_product->getTypeInstance()->getUsedProducts() as $childProduct) {
                    $child_ids[]=$childProduct->getId();
                }
             $product_to_child[$_product->getId()]=$child_ids;
            }
         
      }

      $where='';
      foreach ($product_to_child as $product_id=>$childs){
          $where.=(strlen($where)>0?" or ":"")."(".((count($childs)?
                            " ((qphp.parent_product_id='".(int)$product_id."' and qphp.product_id in (".join(",",$childs).")) or (qphp.product_id='".(int)$product_id."' and qphp.parent_product_id=0) )":
                            " qphp.product_id='".(int)$product_id."' and qphp.parent_product_id=0")." ")." ) ";
      }

/*      $where=" (".$where.") and b.status>0 and (pt.promotion_text!='' or bi.filename!='') and b.banner_link_name like '%".$this->category_top_advertisment_name."%' ";

       $condition_1 = $this->_getReadAdapter()->quoteInto("qphp.promotion_id=pt.promotion_id ",'');
       $condition_2 = $this->_getReadAdapter()->quoteInto('b.banner_group=pt.promotion_type','');
       $condition_3 = $this->_getReadAdapter()->quoteInto('bhi.banner_id=b.banner_id','');
       $condition_4 = $this->_getReadAdapter()->quoteInto('bi.banner_image_id=bhi.banner_image_id','');
       $select = $this->_getReadAdapter()->select()->from(array('qphp'=>$this->getTable('promotionhasproduct')))
                ->join(array('pt'=>$this->getTable('promotions')), $condition_1)
                ->join(array('b'=>$this->getTable('qixol/banner')), $condition_2)
                ->joinLeft(array('bhi'=>$this->getTable('qixol/bannertoimage')), $condition_3)
                ->joinLeft(array('bi'=>$this->getTable('qixol/bannerimages')), $condition_4)
                ->where($where)->group("qphp.promotion_id")->order('b.sort_order')->reset('columns')->columns(array('pt.promotion_text','bi.filename',"b.url"));
*/
       $condition_1 = $this->_getReadAdapter()->quoteInto('bhi.banner_id=b.banner_id','');
       $condition_2 = $this->_getReadAdapter()->quoteInto('bi.banner_image_id=bhi.banner_image_id','');
       $condition_3 = $this->_getReadAdapter()->quoteInto('b.banner_group=pt.promotion_type','');
       $condition_4 = $this->_getReadAdapter()->quoteInto("qphp.promotion_id=pt.promotion_id ",'');

       $where=" b.status>0 and (pt.promotion_text!='' or pt.promotion_text is null or bi.filename!='') and b.banner_link_name like '%".$this->category_top_advertisment_name."%' and (qphp.promotion_id is null or (".$where."))";

       $select = $this->_getReadAdapter()->select()->from(array('b'=>$this->getTable('qixol/banner')))
                ->joinLeft(array('bhi'=>$this->getTable('qixol/bannertoimage')), $condition_1)
                ->joinLeft(array('bi'=>$this->getTable('qixol/bannerimages')), $condition_2)
                ->joinLeft(array('pt'=>$this->getTable('promotions')), $condition_3)
                ->joinLeft(array('qphp'=>$this->getTable('promotionhasproduct')), $condition_4)
                ->where($where)->group(array("b.banner_id","bi.banner_image_id"))->order('b.sort_order')->reset('columns')->columns(array('pt.promotion_text','bi.filename',"b.url"));


       $data=$this->_getReadAdapter()->fetchAll($select);

       if (count($data)) return $data;
       return false;
    }   


    public function getProductTextAdv($product,$adv_type='Inline') {
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
          case "Bottom":
              $banner_link_name=$this->product_bottom_advertisment_name;
          break;
          case "Top":
              $banner_link_name=$this->product_top_advertisment_name;
          break;
          case "Inline":
          default :
              $banner_link_name=$this->product_inline_advertisment_name;
          break;
       }
/*
       $condition_1 = $this->_getReadAdapter()->quoteInto("qphp.promotion_id=pt.promotion_id ",'');
       $condition_2 = $this->_getReadAdapter()->quoteInto('b.banner_group=pt.promotion_type','');
       $condition_3 = $this->_getReadAdapter()->quoteInto('bhi.banner_id=b.banner_id','');
       $condition_4 = $this->_getReadAdapter()->quoteInto('bi.banner_image_id=bhi.banner_image_id','');
       $select = $this->_getReadAdapter()->select()->from(array('qphp'=>$this->getTable('promotionhasproduct')))
                ->join(array('pt'=>$this->getTable('promotions')), $condition_1)
                ->join(array('b'=>$this->getTable('qixol/banner')), $condition_2)
                ->joinLeft(array('bhi'=>$this->getTable('qixol/bannertoimage')), $condition_3)
                ->joinLeft(array('bi'=>$this->getTable('qixol/bannerimages')), $condition_4)
                ->where((count($child_ids)?
                            " ((qphp.parent_product_id='".(int)$product_id."' and qphp.product_id in (".join(",",$child_ids).")) or (qphp.product_id='".(int)$product_id."' and qphp.parent_product_id=0) )":
                            " qphp.product_id='".(int)$product_id."' and qphp.parent_product_id=0")." and b.status>0 and (pt.promotion_text!='' or bi.filename!='') and b.banner_link_name like '%".$banner_link_name."%'")
                ->group("qphp.promotion_id")->order('b.sort_order')
                ->reset('columns')->columns(array('pt.promotion_text','bi.filename',"b.url"));
*/
       $condition_1 = $this->_getReadAdapter()->quoteInto('bhi.banner_id=b.banner_id','');
       $condition_2 = $this->_getReadAdapter()->quoteInto('bi.banner_image_id=bhi.banner_image_id','');
       $condition_3 = $this->_getReadAdapter()->quoteInto('b.banner_group=pt.promotion_type','');
       $condition_4 = $this->_getReadAdapter()->quoteInto("qphp.promotion_id=pt.promotion_id ",'');

       $where=" b.status>0 and (pt.promotion_text is null or pt.promotion_text!='' or bi.filename!='') and b.banner_link_name like '%".$banner_link_name."%' and (qphp.promotion_id is null or (".
                            (count($child_ids)?
                            " ((qphp.parent_product_id='".(int)$product_id."' and qphp.product_id in (".join(",",$child_ids).")) or (qphp.product_id='".(int)$product_id."' and qphp.parent_product_id=0) )":
                            " qphp.product_id='".(int)$product_id."' and qphp.parent_product_id=0")
                            ."))";

       $select = $this->_getReadAdapter()->select()->from(array('b'=>$this->getTable('qixol/banner')))
                ->joinLeft(array('bhi'=>$this->getTable('qixol/bannertoimage')), $condition_1)
                ->joinLeft(array('bi'=>$this->getTable('qixol/bannerimages')), $condition_2)
                ->joinLeft(array('pt'=>$this->getTable('promotions')), $condition_3)
                ->joinLeft(array('qphp'=>$this->getTable('promotionhasproduct')), $condition_4)
                ->where($where)->group(array("b.banner_id","bi.banner_image_id"))->order('b.sort_order')->reset('columns')->columns(array('pt.promotion_text','bi.filename',"b.url"));

       $data=$this->_getReadAdapter()->fetchAll($select);

       if (count($data)) return $data;
       return false;
    }

    public function getAllProductTextAdv($product,$adv_type='Inline') {
       //create the list of product->child if parent
       $product_id=$product->getId();
       $child_ids=array();
       if ($product->isConfigurable()){
           $associatedProducts = $product->getTypeInstance()->getConfigurableAttributesAsArray($product); 
           foreach ($product->getTypeInstance()->getUsedProducts() as $childProduct) {
               $child_ids[]=$childProduct->getId();
           }
       }
// 
       $condition_4 = $this->_getReadAdapter()->quoteInto("qphp.promotion_id=pt.promotion_id ",'');
       $where=
                            (count($child_ids)?
                            " ((qphp.parent_product_id='".(int)$product_id."' and qphp.product_id in (".join(",",$child_ids).")) or (qphp.product_id='".(int)$product_id."' and qphp.parent_product_id=0) )":
                            " qphp.product_id='".(int)$product_id."' and qphp.parent_product_id=0");

       $select = $this->_getReadAdapter()->select()->from(array('qphp'=>$this->getTable('promotionhasproduct')))
                ->joinLeft(array('pt'=>$this->getTable('promotions')), $condition_4)
                ->where($where)->group(array("pt.promotion_id"))->reset('columns')->columns(array('pt.promotion_text','pt.discountpercent','pt.discountamount'));

       $data=$this->_getReadAdapter()->fetchAll($select);
       if (count($data)) return $data;
       return false;
    }

    public function getCartInlineAdv(){
      $where = $this->_getReadAdapter()->quoteInto(" is_for_product = ? ",0);
      $condition_1 = $this->_getReadAdapter()->quoteInto('b.banner_group=pt.promotion_type','');
      $condition_3 = $this->_getReadAdapter()->quoteInto('bhi.banner_id=b.banner_id','');
      $condition_4 = $this->_getReadAdapter()->quoteInto('bi.banner_image_id=bhi.banner_image_id','');
      $select = $this->_getReadAdapter()->select()->from(array('pt'=>$this->getTable('promotions')))
                ->join(array('b'=>$this->getTable('qixol/banner')), $condition_1)
                ->joinLeft(array('bhi'=>$this->getTable('qixol/bannertoimage')), $condition_3)
                ->joinLeft(array('bi'=>$this->getTable('qixol/bannerimages')), $condition_4)
                ->where($where." and b.status>0 and b.banner_link_name like '%".$this->backet_inline_advertisment_name."%'")
                ->order('b.sort_order')
                 ->reset('columns')->columns(array('promotion_text','bi.filename',"b.url","b.url"));
       $data=$this->_getReadAdapter()->fetchAll($select);
       if (count($data)) return $data;
       return false;
    }

   public function getBannerImages($bannerid){
      $where = $this->_getReadAdapter()->quoteInto(" banner_id = ? ",$bannerid);
      $select = $this->_getReadAdapter()->select()->from(array('b'=>$this->getTable('qixol/bannertoimage')))->where($where);
      $data=$this->_getReadAdapter()->fetchAll($select);
      if (count($data)) return $data;
      return array();
   }

   public function setBannerImages($bannerid,$banner_images){
       if ($bannerid>0&&count($banner_images)>0){
       $where = $this->_getWriteAdapter()->quoteInto(" banner_id = ? ",$bannerid);
       $this->_getWriteAdapter()->delete($this->getTable('qixol/bannertoimage'),$where);
        foreach ($banner_images as $banner_image_id){
            $this->_getWriteAdapter()->insert(
            $this->getTable('qixol/bannertoimage'), 
              array("banner_id" => $bannerid, "banner_image_id" => $banner_image_id)
            );
        }
      }
   }
}