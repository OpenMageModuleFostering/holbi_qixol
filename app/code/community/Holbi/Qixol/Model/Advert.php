<?php
class Holbi_Qixol_Model_Advert extends Mage_Core_Model_Abstract {
    function __construct(){

    }
    function isSticked($product,$type=''){
     $image=false;
     $products_has_promotion_image=Mage::getResourceSingleton('qixol/sticker');
     $product_stick_images=$products_has_promotion_image->getStickerImage($product,$type);

     if (is_array($product_stick_images)&&count($product_stick_images)==1) { //if 1 sticker
      $image=$product_stick_images[0]['filename'];
      if (($image!==false)&&strlen($image)>5)
      $image=Mage::getBaseUrl('media').$image;
      return $image;
     }elseif(is_array($product_stick_images)&&count($product_stick_images)>1){
       foreach ($product_stick_images as $image_array){
             if (($image_array['filename']!==false)&&strlen($image_array['filename'])>5){
                 $image[]=Mage::getBaseUrl('media').$image_array['filename'];
             }
       }
      return $image;
     }
      return $image;
    }

    function getCategoryTopAdv($_productCollection){
      //replace with promotion text

      $text_to_return='';

      $advertisment=Mage::getResourceSingleton('qixol/banner');
      $adv_array=$advertisment->getCategoryTopAdv($_productCollection);
      if ($adv_array!==false&&count($adv_array)){
         foreach($adv_array as $advert){
            if ($advert['filename']!=''&&strlen($advert['filename'])>5){
                $text_to_return.="<div>".(trim($advert['url'])!=''?"<a href='".$advert['url']."'>":"")."<img title='".$advert['promotion_text']."' src='".Mage::getBaseUrl('media').$advert['filename']."'>".(trim($advert['url'])!=''?"</a>":"")."</div>";
            }else 
                $text_to_return.="<div>".(trim($advert['url'])!=''?"<a href='".$advert['url']."'>":"").$advert['promotion_text'].(trim($advert['url'])!=''?"</a>":"")."</div>";
         }
      }

      return $text_to_return!=''?/*"<div><ul>".*/$text_to_return/*."</ul></div>"*/:"";
    }

   function getProductBottmAdv($product){
      //replace with promotion text
     $text_to_return='';

     $products_has_promotion=Mage::getResourceSingleton('qixol/banner');
     $product_data=$products_has_promotion->getProductTextAdv($product,'Bottom');
      if ($product_data!==false&&count($product_data)){
         foreach($product_data as $advert){
            if ($advert['filename']!=''&&strlen($advert['filename'])>5){
                $text_to_return.="<div>".(trim($advert['url'])!=''?"<a href='".$advert['url']."'>":"")."<img title='".$advert['promotion_text']."' src='".Mage::getBaseUrl('media').$advert['filename']."'>".(trim($advert['url'])!=''?"</a>":"")."</div>";
            }else 
                $text_to_return.="<div>".(trim($advert['url'])!=''?"<a href='".$advert['url']."'>":"").$advert['promotion_text'].(trim($advert['url'])!=''?"</a>":"")."</div>";
         }
      }
      return $text_to_return!=''?/*"<div><ul>".*/$text_to_return/*."</ul></div>"*/:"";

   }
   
  function getProductInfoTopAdv($product){

     $text_to_return='';

     $products_has_promotion=Mage::getResourceSingleton('qixol/banner');
     $product_data=$products_has_promotion->getProductTextAdv($product,'Top');
      if ($product_data!==false&&count($product_data)){
         foreach($product_data as $advert){
            if ($advert['filename']!=''&&strlen($advert['filename'])>5){
                $text_to_return.="<div>".(trim($advert['url'])!=''?"<a href='".$advert['url']."'>":"")."<img title='".$advert['promotion_text']."' src='".Mage::getBaseUrl('media').$advert['filename']."'>".(trim($advert['url'])!=''?"</a>":"")."</div>";
            }else 
                $text_to_return.="<div>".(trim($advert['url'])!=''?"<a href='".$advert['url']."'>":"").$advert['promotion_text'].(trim($advert['url'])!=''?"</a>":"")."</div>";
         }
      }
      return $text_to_return!=''?/*"<div><ul>".*/$text_to_return/*."</ul></div>"*/:"";

  }
  
  function getProductInlineAdv($product){

     $text_to_return='';

     $products_has_promotion=Mage::getResourceSingleton('qixol/banner');
     $product_data=$products_has_promotion->getProductTextAdv($product,'Inline');
      if ($product_data!==false&&count($product_data)){
         foreach($product_data as $advert){
            if ($advert['filename']!=''&&strlen($advert['filename'])>5){
                $text_to_return.="<div>".(trim($advert['url'])!=''?"<a href='".$advert['url']."'>":"")."<img title='".$advert['promotion_text']."' src='".Mage::getBaseUrl('media').$advert['filename']."'>".(trim($advert['url'])!=''?"</a>":"")."</div>";
            }else 
                $text_to_return.="<div>".(trim($advert['url'])!=''?"<a href='".$advert['url']."'>":"").$advert['promotion_text'].(trim($advert['url'])!=''?"</a>":"")."</div>";
         }
      }
      return $text_to_return!=''?/*"<div><ul>".*/$text_to_return/*."</ul></div>"*/:"";
  }

 function getCartInlineAdv(){
   $cart_promotion=Mage::getResourceSingleton('qixol/banner');

     $promotion_data=$cart_promotion->getCartInlineAdv();
      if ($promotion_data!==false&&count($promotion_data)){
         foreach($promotion_data as $advert){
            if ($advert['filename']!=''&&strlen($advert['filename'])>5){
                $text_to_return.="<div>".(trim($advert['url'])!=''?"<a href='".$advert['url']."'>":"")."<img title='".$advert['promotion_text']."' src='".Mage::getBaseUrl('media').$advert['filename']."'>".(trim($advert['url'])!=''?"</a>":"")."</div>";
            }else 
                $text_to_return.="<div>".(trim($advert['url'])!=''?"<a href='".$advert['url']."'>":"").$advert['promotion_text'].(trim($advert['url'])!=''?"</a>":"")."</div>";
         }
      }
      return $text_to_return!=''?/*"<div><ul>".*/$text_to_return/*."</ul></div>"*/:"";
 }

  function getAllProductAdv($product){

     $text_to_return='';

     $products_has_promotion=Mage::getResourceSingleton('qixol/banner');
     $product_data=$products_has_promotion->getAllProductTextAdv($product);
      if ($product_data!==false&&count($product_data)){
         //$text_to_return.="";
         //$text_to_return.="";
         foreach($product_data as $advert) {
                          $text_to_return.="<tr><td>".(trim($advert['url'])!=''?"<a href='".$advert['url']."'>":"").$advert['promotion_text'].(trim($advert['url'])!=''?"</a>":"")."</td><td style='width:100px;'>".(($advert['discountpercent']>0||$advert['discountamount']>0)?($advert['discountpercent']>0?$advert['discountpercent']."%":$advert['discountamount'])." Off":"&nbsp;")."</td></tr>";
          }
         //$text_to_return.="";
       }
      return $text_to_return!=''?/*"<div><ul>".*/$text_to_return/*."</ul></div>"*/:"";
  }
}