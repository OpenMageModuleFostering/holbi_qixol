<?php
class Holbi_Qixol_Model_Mysql4_Promotions extends Mage_Core_Model_Mysql4_Abstract
{
    protected $_isPkAutoIncrement = false;

    protected function _construct()
    {
        $this->_init("qixol/promotions", "promotion_id");
    }

    function removeOldPromotion(){
      //should be one hour, but because of time difference 
      $this->_getWriteAdapter()->delete($this->getTable('promotions'), " is_for_product=1 and (update_time!='0000-00-00 00:00:00' and update_time <= '".date("Y-m-d H:i:s",strtotime(" -2 hour"))."'  )");
      
      return ;
    }

    function removeOldDayPromotion(){
      //should be one day, but because of time difference 
      $this->_getWriteAdapter()->delete($this->getTable('promotions'), " is_for_product=0 and (update_time!='0000-00-00 00:00:00' and update_time <= '".date("Y-m-d H:i:s",strtotime(" -24 hour"))."' )");//remove previous day not updated promotion
      return ;
    }
   
    function updatePromotionProduct($datatoupdate){
          //print_r($datatoupdate);

         if ($datatoupdate['product_id']>0&&$datatoupdate['promotion_id']>0){
            $condition = $this->_getWriteAdapter()->quoteInto('(product_id = ?', $datatoupdate['product_id']);
            $condition = $this->_getWriteAdapter()->quoteInto($condition. " and parent_product_id = ? ", $datatoupdate['parent_product_id']);
            $condition = $this->_getWriteAdapter()->quoteInto($condition. " and promotion_id = ? )", $datatoupdate['promotion_id']);
             
            //delete old, insert new

             $this->_getWriteAdapter()->delete($this->getTable('promotionhasproduct'), $condition);

             $this->_getWriteAdapter()->insert($this->getTable('promotionhasproduct'), $datatoupdate);
         }
       
    }   

    function removeOldPromotedProduct(){
      //should be one day, but because of time difference 
      $this->_getWriteAdapter()->delete($this->getTable('promotionhasproduct'), "(update_time!='0000-00-00 00:00:00' and update_time <= ('".date("Y-m-d H:i:s",strtotime(" -2 hour"))."') )");
      return ;
    }
}