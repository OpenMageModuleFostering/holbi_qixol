<?php
class Holbi_Qixol_Model_Mysql4_Sticker extends Mage_Core_Model_Mysql4_Abstract {
    private $category_stickers_advertisment_name='CATEGORY_STICKERS';

    public function _construct() {
        // Note that the banner_id refers to the key field in your database table.
        $this->_init('qixol/sticker', 'sticker_id');
    }

    public function getStickerImage($product) {
        
        // How to get the stickers:
        // get promotions that are valid for this product
        // get stickers for specific promotion references for these promotions - take ALL stickers matching this reference
        // next get custom stickers for promotion types other than any matched in the second step
        // finally get system default stickers for any remaining unmatched promotion types

        /**************************************************************************
        // get promotions that are valid for this product (and child products)
        **************************************************************************/
        $promotions = $this->getPromotionsForProduct($product);
        
        if (count($promotions) == 0) {
            return false;
        }
        
        /**************************************************************************
        // get stickers for any promotion references (!= '')
        **************************************************************************/
        $promotionSpecificStickers = $this->getPromotionSpecificStickers($promotions);
        
        $matchedTypes = array();
        $filenames = array();

        if (is_array($promotionSpecificStickers)) {
            if (count($promotionSpecificStickers) > 0) {
                foreach ($promotionSpecificStickers as $promotionSpecificSticker) {
                    array_push($matchedTypes, $promotionSpecificSticker['promotion_type_name']);
                    array_push($filenames, array('filename' => $promotionSpecificSticker['filename']));
                }
            }
        }
        
        /**************************************************************************
        // next get custom stickers for promotion types other than any matched in the second step
        **************************************************************************/
        $defaultStickers = $this->getPromotionDefaultStickers($promotions, $matchedTypes);
        
        if (is_array($defaultStickers)) {
            if (count($defaultStickers) > 0) {
                foreach ($defaultStickers as $defaultSticker) {
                    array_push($matchedTypes, $defaultSticker['promotion_type_name']);
                    array_push($filenames, array('filename' => $defaultSticker['filename']));
                }
            }
        }
        
        /**************************************************************************
        // next get system stickers for remaining promotion types
        **************************************************************************/
        $systemStickers = $this->getPromotionSystemStickers($promotions, $matchedTypes);
        
        if (is_array($systemStickers)) {
            if (count($systemStickers) > 0) {
                foreach ($systemStickers as $systemSticker) {
                    array_push($matchedTypes, $systemSticker['promotion_type_name']);
                    array_push($filenames, array('filename' => $systemSticker['filename']));
                }
            }
        }
        
        return $filenames;
    }

    public function getAllProductTextAdv($product) {
        /*
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
                ->where($where)->group(array("pt.promotion_id"))->reset('columns')
               ->columns(array('pt.promotion_text','pt.discountpercent','pt.discountamount'));

       $data=$this->_getReadAdapter()->fetchAll($select);
       */
       $promotions = $this->getPromotionsForProduct($product);
       
       if (count($promotions)) return $promotions;
       return false;
    }

    private function getPromotionsForProduct($product){
        //create the list of product->child if parent
        $product_id=$product->getId();
        $child_ids=array();
        if ($product->isConfigurable()){
            $associatedProducts = $product->getTypeInstance()->getConfigurableAttributesAsArray($product); 
            foreach ($product->getTypeInstance()->getUsedProducts() as $childProduct) {
                $child_ids[]=$childProduct->getId();
            }
        }

        $join = 'product.promotion_id = promo.promotion_id';
        $whereString = '';
        $whereString = '(';

        $whereString .= "(";
        $whereString .= "(promo.is_everyday=0) and ";
        $whereString .= "((promo.from_date='0000-00-00 00:00:00') or (TIME(promo.from_date) < CURTIME())) and ";
        $whereString .= "((promo.till_date='0000-00-00 00:00:00') or (TIME(promo.till_date) > CURTIME()))";
        $whereString .= ")";
        
        $whereString .= " or ";
        
        $whereString .= "(";
        $whereString .= "(promo.is_everyday=1) and ";
        $whereString .= "(TIME(promo.from_date) < CURTIME()) and ";
        $whereString .= "(TIME(promo.till_date) > CURTIME())";
        $whereString .= ")";
        
        $whereString .= ")";
        
        $whereString .= " and ";
            
        if (count($child_ids)) {
            $whereString .= "((product.parent_product_id = '".(int)$product_id."' and "
                             ."product.product_id in (".join(",",$child_ids).")) or "
                                     ."(product.product_id = '".(int)$product_id."' and product.parent_product_id = 0) )";
        } else {
            $whereString .= "product.product_id = '".(int)$product_id."' and product.parent_product_id = 0";
        }
       
        $select = $this->_getReadAdapter()->select()->distinct()
            ->from(array('product' => $this->getTable('promotionhasproduct')))
            ->join(array('promo' => $this->getTable('promotions')), $join)
            ->where($whereString)
            ->reset('columns')
            ->columns(array('promotion_type' => 'promo.promotion_type',
                'promotion_name' => 'promo.promotion_name',
                'promo_reference' => 'promo.yourref',
                'promotion_text' => 'promo.promotion_text',
                'discountpercent' => 'promo.discountpercent',
                'discountamount' => 'promo.discountamount'));

        return $this->_getReadAdapter()->fetchAll($select);
    }
    
    private function getPromotionSpecificStickers($promotions) {
        $references = array();
        foreach($promotions as $key => $value) {
            if ($value[promo_reference] != '') {
                array_push($references, $value[promo_reference]);
            }
        }
        
        if (count($references) == 0) {
            return;
        }
        
        $join = 'sticker.promo_reference = promo.yourref';

        $whereStringReferences = join('\',\'', $references);
        $whereStringReferences = 'promo_reference in (\'' . $whereStringReferences . '\')';
        
        $select = $this->_getReadAdapter()->select()
                ->from(array('sticker' => $this->getTable('sticker')))
                ->join(array('promo' => $this->getTable('promotions')), $join)
                ->where($whereStringReferences)
                ->reset('columns')
                ->columns(array('filename' => 'filename', 'promotion_type_name' => 'promo.promotion_type'));

        return $this->_getReadAdapter()->fetchAll($select);
    }
    
    private function getPromotionDefaultStickers($promotions, $matchedTypes) {
        $whereString = '(is_default_for_type = 1) and (is_system_default_for_type = 0)';
        if (is_array($matchedTypes)) {
            if (count($matchedTypes) > 0) {
                $whereString .= ' and promo_type_name not in (\'' . join('\',\'', $matchedTypes) . '\')';
            }
        }

        $unmatchedTypes = array();
        foreach($promotions as $key => $value) {
            if ($value[promotion_type] != '') {
                array_push($unmatchedTypes, $value[promotion_type]);
            }
        }
        $whereString .= ' and promo_type_name in (\'' . join('\',\'', $unmatchedTypes) . '\')';
        
        $select = $this->_getReadAdapter()->select()
                ->from($this->getTable('sticker'))
                ->where($whereString)
                ->reset('columns')
                ->columns(array('filename' => 'filename', 'promotion_type_name' => 'promo_type_name'));

        return $this->_getReadAdapter()->fetchAll($select);
    }

    private function getPromotionSystemStickers($promotions, $matchedTypes) {
        $whereString = '(is_default_for_type = 1) and (is_system_default_for_type = 1)';
        if (is_array($matchedTypes)) {
            if (count($matchedTypes) > 0) {
                $whereString .= ' and promo_type_name not in (\'' . join('\',\'', $matchedTypes) . '\')';
            }
        }

        $unmatchedTypes = array();
        foreach($promotions as $key => $value) {
            if ($value[promotion_type] != '') {
                array_push($unmatchedTypes, $value[promotion_type]);
            }
        }
        $whereString .= ' and promo_type_name in (\'' . join('\',\'', $unmatchedTypes) . '\')';
        
        $select = $this->_getReadAdapter()->select()
                ->from($this->getTable('sticker'))
                ->where($whereString)
                ->reset('columns')
                ->columns(array('filename' => 'filename', 'promotion_type_name' => 'promo_type_name'));

        return $this->_getReadAdapter()->fetchAll($select);
    }
}