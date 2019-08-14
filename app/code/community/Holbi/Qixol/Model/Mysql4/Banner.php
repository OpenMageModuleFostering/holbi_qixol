<?php
class Holbi_Qixol_Model_Mysql4_Banner extends Mage_Core_Model_Mysql4_Abstract
{
    private $category_top_advertisment_name='CATEGORY_TOP';
    private $product_top_advertisment_name='PRODUCT_TOP';
    private $product_bottom_advertisment_name='PRODUCT_BOTTOM';
    private $product_inline_advertisment_name='PRODUCT_INLINE';

    public function _construct() {
        $this->_init('qixol/banner', 'banner_id');
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

       $condition_2 = $this->_getReadAdapter()->quoteInto('bi.banner_id=b.banner_id','');
       $condition_3 = $this->_getReadAdapter()->quoteInto('bi.promotion_reference=pt.yourref',''); // and bi.promotion_reference != \'\','');
       $condition_4 = $this->_getReadAdapter()->quoteInto("qphp.promotion_id=pt.promotion_id ",'');

       $where=" b.status>0 and (pt.promotion_text!='' or pt.promotion_text is null or bi.filename!='') and b.display_zone like '%".$this->category_top_advertisment_name."%' and (qphp.promotion_id is null or (".$where."))";

       $select = $this->_getReadAdapter()->select()->from(array('b'=>$this->getTable('qixol/banner')))
                ->join(array('bi'=>$this->getTable('qixol/bannerimage')), $condition_2)
                ->joinLeft(array('pt'=>$this->getTable('promotions')), $condition_3)
                ->joinLeft(array('qphp'=>$this->getTable('promotionhasproduct')), $condition_4)
                ->where($where)->group(array("b.banner_id","bi.banner_image_id"))
                ->order('bi.sort_order')
                ->reset('columns')
                ->columns(array('bi.comment','bi.filename',"bi.url"));

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
              $display_zone=$this->product_bottom_advertisment_name;
          break;
          case "Top":
              $display_zone=$this->product_top_advertisment_name;
          break;
          case "Inline":
          default :
              $display_zone=$this->product_inline_advertisment_name;
          break;
       }

       $condition_2 = $this->_getReadAdapter()->quoteInto('bi.banner_id=b.banner_id','');
       $condition_3 = $this->_getReadAdapter()->quoteInto('bi.promotion_reference=pt.yourref', ''); // and bi.promotion_reference != \'\','');
       $condition_4 = $this->_getReadAdapter()->quoteInto("qphp.promotion_id=pt.promotion_id ",'');

       $where = "b.status > 0 and ";
       $where .= "(pt.promotion_text is null or pt.promotion_text!='' or bi.filename!='') and ";
       $where .= "b.display_zone like '%" . $display_zone . "%' and ";
       $where .= "(qphp.promotion_id is null or (";
        if (count($child_ids))
        {
            $where .= " ((qphp.parent_product_id='" . (int)$product_id . "' and ";
            $where .= "qphp.product_id in (" . join(",",$child_ids) . ")) or ";
            $where .= "(qphp.product_id='" . (int)$product_id."' and ";
            $where .= "qphp.parent_product_id=0) )";
        }
        else
        {
            $where .= " qphp.product_id='" . (int)$product_id . "' and qphp.parent_product_id=0";
        }
        $where .= "))";
                            
       $select = $this->_getReadAdapter()->select()->from(array('b'=>$this->getTable('qixol/banner')))
                ->join(array('bi'=>$this->getTable('qixol/bannerimage')), $condition_2)
                ->joinLeft(array('pt'=>$this->getTable('promotions')), $condition_3)
                ->joinLeft(array('qphp'=>$this->getTable('promotionhasproduct')), $condition_4)
                ->where($where)
                ->group(array("bi.banner_id","bi.banner_image_id"))
                ->order('bi.sort_order')
                ->reset('columns')
                ->columns(array('bi.comment','bi.filename',"bi.url"));

       $data=$this->_getReadAdapter()->fetchAll($select);

       if (count($data)) return $data;
       return false;
    }

   public function getBannerImages($bannerid){
      $where = $this->_getReadAdapter()->quoteInto(" banner_id = ? ",$bannerid);
      $select = $this->_getReadAdapter()->select()->from(array('b'=>$this->getTable('qixol/bannerimage')))->where($where);
      $data=$this->_getReadAdapter()->fetchAll($select);
      if (count($data)) return $data;
      return array();
   }
   
   public function getBannerImageIds(Holbi_Qixol_Model_Banner $banner)
   {
       $read = $this->_getReadAdapter();
       $select = $read->select()
               ->from($this->getTable('qixol/bannerimage'), array('banner_id'))
               ->where('banner_id = ?', $banner->getId());
       return $read->fetchCol($select);
   }
   
   public function setBannerImages($bannerid,$banner_images){
       if ($bannerid>0&&count($banner_images)>0){
       $where = $this->_getWriteAdapter()->quoteInto(" banner_id = ? ",$bannerid);
       $this->_getWriteAdapter()->delete($this->getTable('qixol/bannerimage'),$where);
        foreach ($banner_images as $banner_image_id){
            $this->_getWriteAdapter()->insert(
            $this->getTable('qixol/bannerimage'), 
              array("banner_id" => $bannerid, "banner_image_id" => $banner_image_id)
            );
        }
      }
   }
}