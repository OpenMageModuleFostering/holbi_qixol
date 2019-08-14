<?php

require('iPromoService.php');
require_once('config.php');

abstract class PromoService implements iPromoService
{
    public abstract function CustomerGroupExport($data);
    public abstract function ShippingMethodsExport($data);
    public abstract function CurrenciesExport($data);
    public abstract function StoresExport($data);
    public abstract function ProductsExport($data);

    public abstract function PromotionsForProducts($data);
    public abstract function PromotionsForBaskets($data);
    
    public abstract function BasketValidate($data);
    public abstract function CouponCodeValidate($couponCode);
    public abstract function RetrieveValidatedBasket($basketRef);
    public abstract function BasketCheck();

    public function parsePromotionsForBaskets($promotion_new_xml=''){
        $xml_object = simplexml_load_string($promotion_new_xml);
                     //print_r($xml_object);
        if ($xml_object instanceof SimpleXMLElement) {     
            foreach ($xml_object as $xml_root_key=>$xml_object_sub){
                if ($xml_root_key=='promotions'){
                    foreach ($xml_object_sub as $xml_promotions){
                        unset($promotion_model);
                        $attributes=$xml_promotions->attributes();
                        $promotion_model=Mage::getModel('qixol/promotions')->load((int)$attributes['id']);
                        if (count($promotion_model->getData())>0){
                            $promotion_model->setUpdateTime(date("Y-m-d H:i:s"));
                        } else {
                            unset($promotion_model);
                            $promotion_model=Mage::getModel('qixol/promotions');
                            $promotion_model->setCreatedTime(date("Y-m-d H:i:s"));
                            $promotion_model->setUpdateTime('0000-00-00 00:00:00');
                        }

                        $promotion_model->setPromotionId((int)$attributes['id']);
                        $promotion_model->setPromotionType(isset($attributes['type'])?(string)$attributes['type']:"");

                        $promotion_model->setDiscountpercent(isset($attributes['discountpercent'])?(double)$attributes['discountpercent']:0);
                        $promotion_model->setDiscountamount(isset($attributes['discountamount'])?(double)$attributes['discountamount']:0);
                        $promotion_model->setHascouponrestrictions(isset($attributes['hascouponrestrictions'])?(int)$attributes['hascouponrestrictions']:0);

                        $promotion_model->setPromotionName(isset($xml_promotions->name)?(string)$xml_promotions->name:"");
                        $promotion_model->setPromotionText(isset($xml_promotions->displaytext)?(string)$xml_promotions->displaytext:"");

                        try {
                            $promotion_model->save();
                        } catch(Exception $e) {
                            print_r($e);
                        }
                    }
                }
            }
            //delete not updated simple way
            $promotion_has_products=Mage::getResourceModel('qixol/promotions');
            $promotion_has_products->removeOldDayPromotion();
        }
    }

    function parsePromotionsForProducts($promotion_new_xml=''){
          $active_promotions=array();
          $new_promotions=array();
          $promotions_list=Mage::getModel('qixol/Promotions')->getCollection();
          foreach ($promotions_list as $current_promotion){
              $active_promotions[$current_promotion->getPromotionId()]=$current_promotion->getPromotionType();
          }
           //first test data
          //$promotion_new_xml='<response><promotions><promotion id="106" type="BUNDLE" yourref="QIXOL-2" bundleprice="50.00"><name>Qixol 2</name><displaytext>Buy three test config products for 50</displaytext></promotion><promotion id="107" type="BOGOF" discountpercent="50.00"><name>Qixol 3a</name><displaytext>test config product 4 - BOGOF</displaytext></promotion><promotion id="108" type="BOGOF" discountpercent="50.00"><name>Qixol 3b</name><displaytext>Test Config product 5  BOGOF</displaytext></promotion><promotion id="109" type="BOGOR" discountpercent="50.00"><name>Qixol 4a</name><displaytext>test config product 6 BOGOR</displaytext></promotion><promotion id="110" type="BOGOR" discountpercent="2.00"><name>Qixol 4b</name><displaytext>test config product 7 BOGOR</displaytext></promotion><promotion id="111" type="BOGOR" discountpercent="24.99"><name>Qixol 4c</name><displaytext>Buy two "Test config product 8" get the second for 24.99</displaytext></promotion><promotion id="112" type="DEAL"><name>Qixol 5a</name><displaytext>Buy a small green, small black and small white product, get the cheapest free</displaytext></promotion><promotion id="113" type="DEAL"><name>Qixol 5b</name><displaytext>Buy a medium red, medium white and medium black product for 19.99</displaytext></promotion><promotion id="116" type="FREEPRODUCT"><name>Qixol 7a</name><displaytext>Buy "test product 1 / Test1_01" get "test product 2 / 123456" free (applies ONCE only)</displaytext></promotion><promotion id="117" type="FREEPRODUCT"><name>Qixol 7b</name><displaytext>Buy "test product 4 / test_4" get "test product 5 / test_5" free for each "test product 4"</displaytext></promotion><promotion id="118" type="ISSUECOUPON"><name>Qixol 8a</name><displaytext>Buy "testing" (test2_01) get a coupon code</displaytext></promotion><promotion id="121" type="PRODUCTSREDUCTION" discountpercent="30.00"><name>Qixol 10</name><displaytext>Use an issued coupon, get 30% off any Size = L product</displaytext></promotion></promotions><products><product productcode="config_11"/><product productcode="config_11" variantcode="test2_01_01"><promotions><promotion id="121" requiredqty="1"/></promotions></product><product productcode="config_11" variantcode="test2_01_03"/><product productcode="config_11" variantcode="test1_1"><promotions><promotion id="112" requiredqty="1" multipleproductrestrictions="1"/></promotions></product><product productcode="config_11" variantcode="test2_2"><promotions><promotion id="113" requiredqty="1" multipleproductrestrictions="1"/></promotions></product><product productcode="config_11" variantcode="test3_3"/><product productcode="config_11" variantcode="test4_4"><promotions><promotion id="113" requiredqty="1" multipleproductrestrictions="1"/></promotions></product><product productcode="config_11" variantcode="test5_5"><promotions><promotion id="112" requiredqty="1" multipleproductrestrictions="1"/></promotions></product><product productcode="config_11" variantcode="Test6_6"><promotions><promotion id="113" requiredqty="1" multipleproductrestrictions="1"/></promotions></product><product productcode="config_11" variantcode="test7_7"><promotions><promotion id="112" requiredqty="1" multipleproductrestrictions="1"/></promotions></product><product productcode="config_11" variantcode="test8_8"><promotions><promotion id="121" requiredqty="1"/></promotions></product><product productcode="config_10"/><product productcode="config_10" variantcode="test2_01_01"><promotions><promotion id="121" requiredqty="1"/></promotions></product><product productcode="config_10" variantcode="test2_01_03"/><product productcode="config_10" variantcode="test1_1"><promotions><promotion id="112" requiredqty="1" multipleproductrestrictions="1"/></promotions></product><product productcode="config_10" variantcode="test2_2"><promotions><promotion id="113" requiredqty="1" multipleproductrestrictions="1"/></promotions></product><product productcode="config_10" variantcode="test3_3"/><product productcode="config_10" variantcode="test4_4"><promotions><promotion id="113" requiredqty="1" multipleproductrestrictions="1"/></promotions></product><product productcode="config_9"/><product productcode="config_9" variantcode="test1_1"><promotions><promotion id="112" requiredqty="1" multipleproductrestrictions="1"/></promotions></product><product productcode="config_9" variantcode="test2_2"><promotions><promotion id="113" requiredqty="1" multipleproductrestrictions="1"/></promotions></product><product productcode="config_9" variantcode="test5_5"><promotions><promotion id="112" requiredqty="1" multipleproductrestrictions="1"/></promotions></product><product productcode="config_9" variantcode="Test6_6"><promotions><promotion id="113" requiredqty="1" multipleproductrestrictions="1"/></promotions></product><product productcode="config_9" variantcode="test7_7"><promotions><promotion id="112" requiredqty="1" multipleproductrestrictions="1"/></promotions></product><product productcode="config_9" variantcode="test8_8"><promotions><promotion id="121" requiredqty="1"/></promotions></product><product productcode="test8_8"/><product productcode="test7_7"/><product productcode="config_8"><promotions><promotion id="111" requiredqty="2"/></promotions></product><product productcode="config_8" variantcode="test2_01_01"><promotions><promotion id="121" requiredqty="1"/><promotion id="111" requiredqty="2"/></promotions></product><product productcode="config_8" variantcode="test2_01_03"><promotions><promotion id="111" requiredqty="2"/></promotions></product><product productcode="config_8" variantcode="test1_1"><promotions><promotion id="112" requiredqty="1" multipleproductrestrictions="1"/><promotion id="111" requiredqty="2"/></promotions></product><product productcode="config_8" variantcode="test2_2"><promotions><promotion id="113" requiredqty="1" multipleproductrestrictions="1"/><promotion id="111" requiredqty="2"/></promotions></product><product productcode="config_8" variantcode="test3_3"><promotions><promotion id="111" requiredqty="2"/></promotions></product><product productcode="config_7"><promotions><promotion id="110" requiredqty="2"/></promotions></product><product productcode="config_7" variantcode="test2_01_01"><promotions><promotion id="121" requiredqty="1"/><promotion id="110" requiredqty="2"/></promotions></product><product productcode="config_7" variantcode="test2_01_03"><promotions><promotion id="110" requiredqty="2"/></promotions></product><product productcode="config_7" variantcode="test1_1"><promotions><promotion id="112" requiredqty="1" multipleproductrestrictions="1"/><promotion id="110" requiredqty="2"/></promotions></product><product productcode="config_7" variantcode="test2_2"><promotions><promotion id="113" requiredqty="1" multipleproductrestrictions="1"/><promotion id="110" requiredqty="2"/></promotions></product><product productcode="config_7" variantcode="test3_3"><promotions><promotion id="110" requiredqty="2"/></promotions></product><product productcode="config_7" variantcode="test4_4"><promotions><promotion id="113" requiredqty="1" multipleproductrestrictions="1"/><promotion id="110" requiredqty="2"/></promotions></product><product productcode="config_7" variantcode="test5_5"><promotions><promotion id="112" requiredqty="1" multipleproductrestrictions="1"/><promotion id="110" requiredqty="2"/></promotions></product><product productcode="config_7" variantcode="Test6_6"><promotions><promotion id="113" requiredqty="1" multipleproductrestrictions="1"/><promotion id="110" requiredqty="2"/></promotions></product><product productcode="config_6"><promotions><promotion id="109" requiredqty="2"/></promotions></product><product productcode="config_6" variantcode="test1_1"><promotions><promotion id="112" requiredqty="1" multipleproductrestrictions="1"/><promotion id="109" requiredqty="2"/></promotions></product><product productcode="config_6" variantcode="test2_2"><promotions><promotion id="113" requiredqty="1" multipleproductrestrictions="1"/><promotion id="109" requiredqty="2"/></promotions></product><product productcode="config_6" variantcode="test4_4"><promotions><promotion id="113" requiredqty="1" multipleproductrestrictions="1"/><promotion id="109" requiredqty="2"/></promotions></product><product productcode="config_6" variantcode="test5_5"><promotions><promotion id="112" requiredqty="1" multipleproductrestrictions="1"/><promotion id="109" requiredqty="2"/></promotions></product><product productcode="config_6" variantcode="Test6_6"><promotions><promotion id="113" requiredqty="1" multipleproductrestrictions="1"/><promotion id="109" requiredqty="2"/></promotions></product><product productcode="config_5"><promotions><promotion id="108" requiredqty="2"/></promotions></product><product productcode="config_5" variantcode="test2_01_01"><promotions><promotion id="121" requiredqty="1"/><promotion id="108" requiredqty="2"/></promotions></product><product productcode="config_5" variantcode="test2_01_03"><promotions><promotion id="108" requiredqty="2"/></promotions></product><product productcode="config_5" variantcode="test1_1"><promotions><promotion id="112" requiredqty="1" multipleproductrestrictions="1"/><promotion id="108" requiredqty="2"/></promotions></product><product productcode="config_5" variantcode="test3_3"><promotions><promotion id="108" requiredqty="2"/></promotions></product><product productcode="config_5" variantcode="test4_4"><promotions><promotion id="113" requiredqty="1" multipleproductrestrictions="1"/><promotion id="108" requiredqty="2"/></promotions></product><product productcode="config_5" variantcode="test5_5"><promotions><promotion id="112" requiredqty="1" multipleproductrestrictions="1"/><promotion id="108" requiredqty="2"/></promotions></product><product productcode="config_5" variantcode="Test6_6"><promotions><promotion id="113" requiredqty="1" multipleproductrestrictions="1"/><promotion id="108" requiredqty="2"/></promotions></product><product productcode="Test6_6"/><product productcode="test5_5"/><product productcode="test4_4"/><product productcode="test3_3"/><product productcode="config_4"><promotions><promotion id="107" requiredqty="2"/></promotions></product><product productcode="config_4" variantcode="test2_01_01"><promotions><promotion id="121" requiredqty="1"/><promotion id="107" requiredqty="2"/></promotions></product><product productcode="config_4" variantcode="test2_01_03"><promotions><promotion id="107" requiredqty="2"/></promotions></product><product productcode="config_4" variantcode="test1_1"><promotions><promotion id="112" requiredqty="1" multipleproductrestrictions="1"/><promotion id="107" requiredqty="2"/></promotions></product><product productcode="config_4" variantcode="test2_2"><promotions><promotion id="113" requiredqty="1" multipleproductrestrictions="1"/><promotion id="107" requiredqty="2"/></promotions></product><product productcode="test2_2"/><product productcode="test1_1"/><product productcode="config_3"><promotions><promotion id="106" requiredqty="1" multipleproductrestrictions="1"/></promotions></product><product productcode="config_3" variantcode="test2_01_01"><promotions><promotion id="121" requiredqty="1"/><promotion id="106" requiredqty="1" multipleproductrestrictions="1"/></promotions></product><product productcode="config_3" variantcode="test2_01_03"><promotions><promotion id="106" requiredqty="1" multipleproductrestrictions="1"/></promotions></product><product productcode="config_2"><promotions><promotion id="106" requiredqty="1" multipleproductrestrictions="1"/></promotions></product><product productcode="config_2" variantcode="test2_01_01"><promotions><promotion id="121" requiredqty="1"/><promotion id="106" requiredqty="1" multipleproductrestrictions="1"/></promotions></product><product productcode="config_2" variantcode="test2_01_03"><promotions><promotion id="106" requiredqty="1" multipleproductrestrictions="1"/></promotions></product><product productcode="config_1"><promotions><promotion id="106" requiredqty="1" multipleproductrestrictions="1"/></promotions></product><product productcode="config_1" variantcode="test2_01_01"><promotions><promotion id="121" requiredqty="1"/><promotion id="106" requiredqty="1" multipleproductrestrictions="1"/></promotions></product><product productcode="config_1" variantcode="test2_01_03"><promotions><promotion id="106" requiredqty="1" multipleproductrestrictions="1"/></promotions></product><product productcode="test_5"/><product productcode="test_4"><promotions><promotion id="117" requiredqty="1"/></promotions></product><product productcode="test_3"/><product productcode="123456"/><product productcode="test2_01_03"/><product productcode="test2_01_01"><promotions><promotion id="121" requiredqty="1"/></promotions></product><product productcode="test2_01"><promotions><promotion id="118" requiredqty="1"/></promotions></product><product productcode="test2_01" variantcode="test2_01_01"><promotions><promotion id="118" requiredqty="1"/><promotion id="121" requiredqty="1"/></promotions></product><product productcode="test2_01" variantcode="test2_01_03"><promotions><promotion id="118" requiredqty="1"/></promotions></product><product productcode="Test1_01"><promotions><promotion id="116" requiredqty="1"/></promotions></product></products><summary result="true"/></response>';
          $xml_object = simplexml_load_string($promotion_new_xml);
                     //print_r($xml_object);
          if ($xml_object instanceof SimpleXMLElement) {     
                          foreach ($xml_object->promotions->promotion as $xml_promotions){
                            unset($promotion_model);
                            $attributes=$xml_promotions->attributes();
                           $promotion_model=Mage::getModel('qixol/promotions')->load((int)$attributes['id']);
                          if (count($promotion_model->getData())>0){
                                $promotion_model->setUpdateTime(date("Y-m-d H:i:s"));
                             
                          } else {
                              unset($promotion_model);
                              $promotion_model=Mage::getModel('qixol/promotions');
                              $promotion_model->setCreatedTime(date("Y-m-d H:i:s"));
                              $promotion_model->setUpdateTime('0000-00-00 00:00:00');
                          }

                          $promotion_model->setPromotionId((int)$attributes['id']);
                          $promotion_model->setIsForProduct(1);
                          $promotion_model->setPromotionType(isset($attributes['type'])?(string)$attributes['type']:"");
                          $new_promotions[$promotion_model->getPromotionId()]=$promotion_model->getPromotionType();

                          $promotion_model->setDiscountamount(isset($attributes['discountamount'])?(double)$attributes['discountamount']:0);

                          $promotion_model->setDiscountpercent(isset($attributes['discountpercent'])?(double)$attributes['discountpercent']:0);
                          $promotion_model->setYourref(isset($attributes['yourref'])?(string)$attributes['yourref']:"");

                          $promotion_model->setBundleprice(isset($attributes['bundleprice'])?(double)$attributes['bundleprice']:0);
                          $promotion_model->setPromotionName(isset($xml_promotions->name)?(string)$xml_promotions->name:"");
                          $promotion_model->setPromotionText(isset($xml_promotions->displaytext)?(string)$xml_promotions->displaytext:"");
                          $promotion_model->setFromDate('0000-00-00 00:00:00');
                          $promotion_model->setTillDate('0000-00-00 00:00:00');
                          
                          if (isset($xml_promotions->availabletimes)&&isset($xml_promotions->availabletimes->availabletime)){
                            foreach ($xml_promotions->availabletimes->availabletime as $availabletime){
                                $time_attributes=$availabletime->attributes();
                                if ((string)$time_attributes['start']!=''){
                                    $promotion_model->setFromDate('2000-01-01 '.(string)$time_attributes['start']);
                                    $promotion_model->setIsEveryday(1);
                                }
                                if ((string)$time_attributes['start']!=''){
                                    $promotion_model->setTillDate('2000-01-01 '.(string)$time_attributes['end']);
                                    $promotion_model->setIsEveryday(1);
                                }
                            }
                          }
                          if ($promotion_model->getFromDate()=='0000-00-00 00:00:00'&&$promotion_model->setTillDate()=='0000-00-00 00:00:00'){
                                    $promotion_model->setIsEveryday(0);
                          }
                          try {
                            $promotion_model->save();
                          } catch(Exception $e) {
                            print_r($e);
                          }
  

                          }

                        //temporary because of bag settings this flag
                        $write_data = Mage::getSingleton('core/resource')->getConnection('core_write');
                        $write_data->query("update qixol_promotions_type set is_everyday=1 where from_date!='0000-00-00 00:00:00' or till_date!='0000-00-00 00:00:00'");

                          foreach ($xml_object->products->product as $xml_products){
//print_r($xml_products);
                                    $attributes=$xml_products->attributes();
                                if (isset($xml_products->promotions)&&!isset($xml_products->promotions->promotion[0])){
                                   $ttemp_data=$xml_products->promotions->promotion;
                                   unset($xml_products->promotions->promotion);
                                   $xml_products->promotions->promotion[0]=$ttemp_data;
                                   unset($ttemp_data);
                                }
                                foreach  ($xml_products->promotions->promotion as $idx_name=>$xml_promotion_data){


                                    //if ($idx_name=='promotions'){
                                        //foreach ($xml_promotion as $xml_promotion_data){
                                        $xml_promotion_data_attributes=$xml_promotion_data->attributes();
                                        $promotion_to_product_array=array();
                                       $promotion_to_product_array['created_time']=date("Y-m-d H:i:s");
                                       $promotion_to_product_array['update_time']=date("Y-m-d H:i:s");
                                       //get product id
                                         $promotion_to_product_array['product_id']=0;
                                          $product_search_tmp = Mage::getModel('catalog/product');              
                                          if ($product_id_tmp=$product_search_tmp->getIdBySku(((string)$attributes['variantcode']!=''?(string)$attributes['variantcode']:(string)$attributes['productcode']))){
                                               $promotion_to_product_array['product_id']=(int)$product_id_tmp;
                                          }
                                       //get parent_id
                                         $promotion_to_product_array['parent_product_id']=0;
                                          if ((string)$attributes['variantcode']!=''){
                                              $product_search_tmp = Mage::getModel('catalog/product');              
                                              if ($product_id_tmp=$product_search_tmp->getIdBySku((string)$attributes['productcode'])){
                                                  $promotion_to_product_array['parent_product_id']=(int)$product_id_tmp;
                                              }
                                          }

                                       $promotion_to_product_array['promotion_id']=(int)$xml_promotion_data_attributes['id'];;
                                       $promotion_to_product_array['parentsku']=((string)$attributes['variantcode']!=''?(string)$attributes['productcode']:'');
                                       $promotion_to_product_array['sku']=((string)$attributes['variantcode']!=''?(string)$attributes['variantcode']:(string)$attributes['productcode']);

                                       $promotion_to_product_array['requiredqty']=(isset($xml_promotion_data_attributes['requiredqty'])?(int)$xml_promotion_data_attributes['requiredqty']:0);
                                       $promotion_to_product_array['multipleproductrestrictions']=isset($xml_promotion_data_attributes['multipleproductrestrictions'])?(int)$xml_promotion_data_attributes['multipleproductrestrictions']:0;
                                        $promotion_has_products=Mage::getResourceSingleton('qixol/promotions');
                                        $promotion_has_products->updatePromotionProduct($promotion_to_product_array);
                                        }
                                   // }
                                //} 

                          }
                     //delete not updated simple way
                       $promotion_has_products=Mage::getResourceModel('qixol/promotions');
                       $promotion_has_products->removeOldPromotion();
                       $promotion_has_products->removeOldPromotedProduct();
                       /* $condition=$promotion_has_products->_getWriteAdapter()->quoteInto('(update_time <= ?)', "(now() - interval 1 hour)");
                        $promotion_has_products->_getWriteAdapter()->delete($this->getTable('promotions'), $condition);
                       */

                     /*foreach ($active_promotions as $p_id=>$p_type){
                       if (!isset($new_promotions[$p_id])){
                             //delete
                       }elseif($new_promotions[$p_id]!=$p_type){
                          //promotion type changed ???
                       }
                            
                     }*/

          }
    }
}
