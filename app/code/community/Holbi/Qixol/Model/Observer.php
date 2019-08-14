<?php
class Holbi_Qixol_Model_Observer 
{
      protected $structure_to_store_in_order=array();

      public function updateCartAjaxUpdate(Varien_Event_Observer $observer){
        global $_SESSION;
        $controller = $observer->getEvent()->getControllerAction();
        $id_cart = $controller->getRequest()->getParam('id');
        $qty_cart = $controller->getRequest()->getParam('qty');
        $result = array();
        if ($id_cart>0){
        $is_updated=false;

              if ($qty_cart>0&&
                $_SESSION['qixol_quoted_items']['items'][$id_cart]['data']['quantity']>$_SESSION['qixol_quoted_items']['items'][$id_cart]['data']['originalquantity']){

                 $difference=$_SESSION['qixol_quoted_items']['items'][$id_cart]['data']['quantity']-$_SESSION['qixol_quoted_items']['items'][$id_cart]['data']['originalquantity'];
                 if ($qty_cart<=$difference) $qty_cart=0;
                 $qty_cart-=$difference;
                 $is_updated=true;
              }elseif($qty_cart>0
                 &&$_SESSION['qixol_quoted_items']['items'][$id_cart]['data']['generated']>0
                 &&$_SESSION['qixol_quoted_items']['items'][$id_cart]['data']['quantity']==$_SESSION['qixol_quoted_items']['items'][$id_cart]['data']['originalquantity']
                  &&$qty_cart==$_SESSION['qixol_quoted_items']['items'][$id_cart]['data']['quantity']){
                 //only free items - do nothing
                    $controller->getResponse()->setHeader('Content-type', 'application/json');
                    $result['success'] = 1;
                    $result['message'] = Mage::helper('qixol')->__('Item was updated successfully.');
                    $controller->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
                    $controller->setFlag('', Mage_Core_Controller_Varien_Action::FLAG_NO_DISPATCH, true);
                 return ;
              }elseif($qty_cart>0
                 &&$_SESSION['qixol_quoted_items']['items'][$id_cart]['data']['generated']>0
                 &&$_SESSION['qixol_quoted_items']['items'][$id_cart]['data']['quantity']==$_SESSION['qixol_quoted_items']['items'][$id_cart]['data']['originalquantity']
                  &&$qty_cart>$_SESSION['qixol_quoted_items']['items'][$id_cart]['data']['quantity']){
                 $qty_cart-=$_SESSION['qixol_quoted_items']['items'][$id_cart]['data']['originalquantity'];
                 if ($qty_cart<0) $qty_cart=0;
                 $is_updated=true;
              }


              $session = Mage::getSingleton('checkout/session');              
              $cart=Mage::getSingleton('checkout/cart');
              $quote = $session->getQuote();
              $exists_in_cart=array();
              foreach ($quote->getAllVisibleItems() as $item) {
                  $exists_in_cart[$item->getItemId()]=$item->getQty();
              }

              //in cart but difference=0, delete it
              if (isset($exists_in_cart[$id_cart])&&$qty_cart==0){
                  // The action controller's constructor expects request and response objects;
                  $cart_controllerInstance = Mage::getControllerInstance(
                    'Mage_Checkout_CartController',
                    $controller->getRequest(), // you can replace this with the actual request
                    new Mage_Core_Controller_Response_Http()
                  );
                  $cart_controllerInstance->ajaxDeleteAction();
                      $controller->setFlag('', Mage_Core_Controller_Varien_Action::FLAG_NO_DISPATCH, true);
                   return ;
              //exists but qty possible chaged nad it pozitive
              }elseif(isset($exists_in_cart[$id_cart])&&$qty_cart>0){
                  // The action controller's constructor expects request and response objects;
                  $the_request=$controller->getRequest();
                  $the_request->setParam('qty',$qty_cart);
                  $cart_controllerInstance = Mage::getControllerInstance(
                    'Mage_Checkout_CartController',
                     $the_request, // you can replace this with the actual request
                    new Mage_Core_Controller_Response_Http()
                  );
                  $cart_controllerInstance->ajaxUpdateAction();
                  $controller->setFlag('', Mage_Core_Controller_Varien_Action::FLAG_NO_DISPATCH, true);
                    $result['success'] = 1;
                    $result['message'] = Mage::helper('qixol')->__('Item was updated successfully.');
                    $controller->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
                   return ;
              }elseif((!isset($exists_in_cart[$id_cart]))&&$qty_cart>0){
                 //add new item



                  if (isset($_SESSION['qixol_quoted_items']['items'][$id_cart]['data'])){
                    $qxol_item=$_SESSION['qixol_quoted_items']['items'][$id_cart]['data'];
                    $add_params_array=array();
                    if ((string)$qxol_item['variantcode']!=''){//configurable
                      $simple_product_id=Mage::getModel('catalog/product')->getIdBySku((string)$qxol_item['variantcode']);
                      $parent_product_id=Mage::getModel('catalog/product')->getIdBySku((string)$qxol_item['productcode']);
                      $parent_product=Mage::getModel('catalog/product')                
                                      ->setStoreId(Mage::app()->getStore()->getId())
                                      ->load($parent_product_id);
                      $simple_product=Mage::getModel('catalog/product')                
                                      ->setStoreId(Mage::app()->getStore()->getId())
                                      ->load($simple_product_id);
                      $add_params_array['product']=$parent_product_id;
                      $attributes = $parent_product->getTypeInstance(true)->getConfigurableAttributesAsArray($parent_product); 
                      foreach ($attributes as $attribute){
                          foreach ($attribute['values'] as $value){
                              $childValue = $simple_product->getData($attribute['attribute_code']);
                              if ($value['value_index'] == $childValue){
                                            $add_params_array['super_attribute'][$attribute['attribute_id']]=$value['value_index'];
                              }
                          }
                      }

                    }else {
                      $parent_product_id=Mage::getModel('catalog/product')->getIdBySku((string)$qxol_item['productcode']);
                      $parent_product=Mage::getModel('catalog/product')                
                                      ->setStoreId(Mage::app()->getStore()->getId())
                                      ->load($parent_product_id);
                      $add_params_array['product']=$parent_product_id;
                    }
                    $filter = new Zend_Filter_LocalizedToNormalized(
                        array('locale' => Mage::app()->getLocale()->getLocaleCode())
                    );
                    $add_params_array['qty'] = $filter->filter($qty_cart);
                    $add_params_array['related_product']='';
                    $controller->getRequest()->setParam('qty',$qty_cart);

                    $cart->addProduct($parent_product, $add_params_array);

 
                  }
                   $result['success'] = 1;
                    $result['message'] = Mage::helper('qixol')->__('Item was updated successfully.');

                    $controller->getResponse()->setHeader('Content-type', 'application/json');
                    $controller->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
                   $controller->setFlag('', Mage_Core_Controller_Varien_Action::FLAG_NO_DISPATCH, true);
                    $result['success'] = 1;
                    $result['message'] = Mage::helper('qixol')->__('Item was updated successfully.');
                    $controller->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
                 return ;

              }

      }else {
        $controller->getResponse()->setHeader('Content-type', 'application/json');
        $result['success'] = 1;
        $result['message'] = Mage::helper('qixol')->__('Item was updated successfully.');
        $controller->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
            $controller->setFlag('', Mage_Core_Controller_Varien_Action::FLAG_NO_DISPATCH, true);
                 return ;
      }
      }

      public function updateCartAjaxDelete(Varien_Event_Observer $observer){
        global $_SESSION;
        $controller = $observer->getEvent()->getControllerAction();
        $id = (int) $controller->getRequest()->getParam('id');

        if ($id==0||(isset($_SESSION['qixol_quoted_items']['items'][$id]) && 
                   $_SESSION['qixol_quoted_items']['items'][$id]['new']>0 && 
                   $_SESSION['qixol_quoted_items']['items'][$id]['data']['generated']>0)){
                    $result['success'] = 0;
                    $result['error'] = Mage::helper('qixol')->__('Can not remove the Free item.');
                    $controller->getResponse()->setHeader('Content-type', 'application/json');
                    $controller->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
                    $controller->setFlag('', Mage_Core_Controller_Varien_Action::FLAG_NO_DISPATCH, true);
                   return;
        }
        // The action controller's constructor expects request and response objects;
        $cart_controllerInstance = Mage::getControllerInstance(
          'Mage_Checkout_CartController',
          $controller->getRequest(), // you can replace this with the actual request
          new Mage_Core_Controller_Response_Http()
        );
        $cart_controllerInstance->ajaxDeleteAction();
        //$controller->setFlag('', Mage_Core_Controller_Varien_Action::FLAG_NO_DISPATCH, true);
      }

      public function updateCartPostAction(Varien_Event_Observer $observer){
        global $_SESSION;
        $controller = $observer->getEvent()->getControllerAction();
        $post_cart = $controller->getRequest()->getParam('cart');
        $post_cart_action = $controller->getRequest()->getParam('update_cart_action');
        if ($post_cart_action=='update_qty'){
           $is_updated=false;
           foreach ($post_cart as $cart_idx=>$cart_value){
              if ($cart_value['qty']>0&&
                $_SESSION['qixol_quoted_items']['items'][$cart_idx]['data']['quantity']>$_SESSION['qixol_quoted_items']['items'][$cart_idx]['data']['originalquantity']){
                 $difference=$_SESSION['qixol_quoted_items']['items'][$cart_idx]['data']['quantity']-$_SESSION['qixol_quoted_items']['items'][$cart_idx]['data']['originalquantity'];
                 if ($cart_value['qty']<=$difference) $cart_value['qty']=0;
                  $cart_value['qty']-=$difference;
                 //cart add item
                 $post_cart[$cart_idx]=$cart_value;
                 $is_updated=true;
              }elseif($cart_value['qty']>0
                 &&$_SESSION['qixol_quoted_items']['items'][$cart_idx]['data']['generated']>0
                 &&$_SESSION['qixol_quoted_items']['items'][$cart_idx]['data']['quantity']==$_SESSION['qixol_quoted_items']['items'][$cart_idx]['data']['originalquantity']
                  &&$cart_value['qty']==$_SESSION['qixol_quoted_items']['items'][$cart_idx]['data']['quantity']){
                 //only free items - remove from cart
                 unset($post_cart[$cart_idx]);
                 $is_updated=true;
              }elseif($cart_value['qty']>0
                 &&$_SESSION['qixol_quoted_items']['items'][$cart_idx]['data']['generated']>0
                 &&$_SESSION['qixol_quoted_items']['items'][$cart_idx]['data']['quantity']==$_SESSION['qixol_quoted_items']['items'][$cart_idx]['data']['originalquantity']
                  &&$cart_value['qty']>$_SESSION['qixol_quoted_items']['items'][$cart_idx]['data']['quantity']){
                 $cart_value['qty']-=$_SESSION['qixol_quoted_items']['items'][$cart_idx]['data']['originalquantity'];
                 if ($cart_value['qty']<0) $cart_value['qty']=0;
                 //cart add item
                 $post_cart[$cart_idx]=$cart_value;
                 $is_updated=true;
              }
           }

          $session = Mage::getSingleton('checkout/session');
          $cart=Mage::getSingleton('checkout/cart');
          $quote = $session->getQuote();
          $exists_in_cart=array();
          foreach ($quote->getAllVisibleItems() as $item) {
              $exists_in_cart[$item->getItemId()]=$item->getQty();
          }
          foreach($post_cart as $cart_idx=>$cart_value){
            if ((!isset($exists_in_cart[$cart_idx]))){
                  if (isset($_SESSION['qixol_quoted_items']['items'][$cart_idx]['data'])){
                    $qxol_item=$_SESSION['qixol_quoted_items']['items'][$cart_idx]['data'];
                    $add_params_array=array();
                    if ((string)$qxol_item['variantcode']!=''){//configurable
                      $simple_product_id=Mage::getModel('catalog/product')->getIdBySku((string)$qxol_item['variantcode']);
                      $parent_product_id=Mage::getModel('catalog/product')->getIdBySku((string)$qxol_item['productcode']);
                      $parent_product=Mage::getModel('catalog/product')                
                                      ->setStoreId(Mage::app()->getStore()->getId())
                                      ->load($parent_product_id);
                      $simple_product=Mage::getModel('catalog/product')                
                                      ->setStoreId(Mage::app()->getStore()->getId())
                                      ->load($simple_product_id);
                      $add_params_array['product']=$parent_product_id;
                      $attributes = $parent_product->getTypeInstance(true)->getConfigurableAttributesAsArray($parent_product); 
                      foreach ($attributes as $attribute){
                          foreach ($attribute['values'] as $value){
                              $childValue = $simple_product->getData($attribute['attribute_code']);
                              if ($value['value_index'] == $childValue){
                                            $add_params_array['super_attribute'][$attribute['attribute_id']]=$value['value_index'];
                              }
                          }
                      }

                    }else {
                      $parent_product_id=Mage::getModel('catalog/product')->getIdBySku((string)$qxol_item['productcode']);
                      $parent_product=Mage::getModel('catalog/product')                
                                      ->setStoreId(Mage::app()->getStore()->getId())
                                      ->load($parent_product_id);
                      $add_params_array['product']=$parent_product_id;
                    }
                    $filter = new Zend_Filter_LocalizedToNormalized(
                        array('locale' => Mage::app()->getLocale()->getLocaleCode())
                    );
                    $add_params_array['qty'] = $filter->filter($cart_value['qty']);
                    $add_params_array['related_product']='';
                    $controller->getRequest()->setParam('qty',$cart_value['qty']);
                    $cart->addProduct($parent_product, $add_params_array);

 
                  }
                  //add new item in cart

            }
          }


           if ($is_updated){
                $controller->getRequest()->setParam('cart',$post_cart);
           }
              // The action controller's constructor expects request and response objects;
              $cart_controllerInstance = Mage::getControllerInstance(
                'Mage_Checkout_CartController',
                $controller->getRequest(), // you can replace this with the actual request
                new Mage_Core_Controller_Response_Http()
              );
              $cart_controllerInstance->updatePostAction();
              //$controller->setFlag('', Mage_Core_Controller_Varien_Action::FLAG_NO_DISPATCH, true);
        }else {
        // The action controller's constructor expects request and response objects;
        $cart_controllerInstance = Mage::getControllerInstance(
          'Mage_Checkout_CartController',
          $controller->getRequest(), // you can replace this with the actual request
          new Mage_Core_Controller_Response_Http()
        );
        $cart_controllerInstance->updatePostAction();
        //$controller->setFlag('', Mage_Core_Controller_Varien_Action::FLAG_NO_DISPATCH, true);
        }
      }

      public function deleteCartAction(Varien_Event_Observer $observer){
        global $_SESSION;
        $controller = $observer->getEvent()->getControllerAction();
        $id = (int) $controller->getRequest()->getParam('id');

        if ($id==0||(isset($_SESSION['qixol_quoted_items']['items'][$id]) && 
                   $_SESSION['qixol_quoted_items']['items'][$id]['new']>0 && 
                   $_SESSION['qixol_quoted_items']['items'][$id]['data']['generated']>0)){
                   $session = Mage::getSingleton('checkout/session');
                   $session->addError('Free item couldn\'t be deleted');
                   unset($_SESSION['tmp_set']);
                   $controller->getResponse()->setRedirect(Mage::getUrl('checkout/cart'));           
                   return;
        }
        // The action controller's constructor expects request and response objects;
        $cart_controllerInstance = Mage::getControllerInstance(
          'Mage_Checkout_CartController',
          $controller->getRequest(), // you can replace this with the actual request
          new Mage_Core_Controller_Response_Http()
        );
        $cart_controllerInstance->deleteAction();
        $controller->setFlag('', Mage_Core_Controller_Varien_Action::FLAG_NO_DISPATCH, true);
        $controller->getResponse()->setRedirect(Mage::getUrl('checkout/cart'));           
      }

      public function checkQixolCoupon(Varien_Event_Observer $observer){
            global $_SESSION;
            /* @var Mage_Core_Controller_Front_Action $controller */
            $controller = $observer->getEvent()->getControllerAction();
           if (trim($controller->getRequest()->getParam('coupon_code')) !='') {
              if ((!isset($_SESSION['qixol_quoted_items']['cart_session_id']))||$_SESSION['qixol_quoted_items']['cart_session_id']=='') $_SESSION['qixol_quoted_items']['cart_session_id']=md5(time());
              unset($_SESSION['qixol_quoted_items']['coupons']);
              $_SESSION['qixol_quoted_items']['coupons'][trim($controller->getRequest()->getParam('coupon_code'))]['code']=trim($controller->getRequest()->getParam('coupon_code'));
              //$this->__('Coupon code "%s" was applied.', Mage::helper('core')->escapeHtml($couponCode))
             $session = Mage::getSingleton('checkout/session');
             $quote = $session->getQuote();
              $getSinch=Mage::getModel('qixol/sinch');
              if ($qixol_quoted_items_new=$getSinch->run_ImportCart($quote)){
                  $qixol_quoted_items=$qixol_quoted_items_new; //if returned new structure
                  $qixol_quoted_items['cart_session_id']=$_SESSION['qixol_quoted_items']['cart_session_id'];
              }
              $qixol_quoted_items['time_checked']=time();
              $_SESSION['qixol_quoted_items']=$qixol_quoted_items;

            if ($_SESSION['qixol_quoted_items']['coupons'][trim($controller->getRequest()->getParam('coupon_code'))]['code']!=''){
               $coupon_text='';
               foreach ($_SESSION['qixol_quoted_items']['coupons'] as $coupon_stored_data){
                  if ((bool)$coupon_stored_data['issued']==false)
                   $coupon_text.=$coupon_stored_data['description'].", ";
               }
               $session->addSuccess(Mage::helper('qixol')->__("Qixol coupon '%s' was applied to your order.", $coupon_text ));
               }
            else 
               $session->addError(Mage::helper('qixol')->__("Qixol coupon  '%s' not applicable.", trim($controller->getRequest()->getParam('coupon_code')) ));
            //compleatly /*intercept*/
            $controller->setFlag('', Mage_Core_Controller_Varien_Action::FLAG_NO_DISPATCH, true);
            $controller->getResponse()->setRedirect(Mage::getUrl('checkout/cart'));           }
      }

			public function markDletedProduct(Varien_Event_Observer $observer)
			{
        $product = $observer->getEvent()->getProduct();
        //temporary until we rebuild
        /*$deleted = Mage::getModel('qixol/Deletedproduct');
        $deleted->setId($product->getId());
        $deleted->setDeletedTime(date("Y-m-d H:i:s"));
        $query=$deleted->save();*/
        //temporary , should be redeveloped to work us abowe
        $parentId = Mage::getResourceSingleton('catalog/product_type_configurable')->getParentIdsByChild($product->getId());

        if (is_array($parentId)&&count($parentId)>0){
           foreach ($parentId as  $parent_product_id){
            $parent_product=Mage::getModel('catalog/product')->load($parent_product_id);
            break; //we think only one parent for a child
           }
            //store child deleted
            $store_deleted='replace into qixol_product_to_delete(entity_id,product_sku,child_sku,deleted_time) values('.(int)$product->getId().',"'.$parent_product->getSku().'","'.$product->getSku().'",now())';
        } else {
            //store parent deleted
            $store_deleted='replace into qixol_product_to_delete(entity_id,product_sku,child_sku,deleted_time) values('.(int)$product->getId().',"'.$product->getSku().'","",now())';
        }
        $write_db = Mage::getSingleton('core/resource')->getConnection('core_write');
        $write_db->query($store_deleted);
			}


      public function processApplyCartPromo(Varien_Event_Observer $observer)
      {
            global $_SESSION;
            if (isset($_SESSION['inside_request'])&&(time()-$_SESSION['inside_request'])<2){//3 second prevent duplicate confirmation request
                  //echo "@".(time()-$_SESSION['inside_request'])."@";
                  return ;
            }

            $_SESSION['inside_request']=time();
            $quote = $observer->getQuote();
                
            $promotions_data_hash=array();
            
            $getSinch=Mage::getModel('qixol/sinch');
            $unlink_coupon_codes=array();
            foreach ($_SESSION['qixol_quoted_items']['coupons'] as $coupon_id=>$coupon_stored_data){
                if ((bool)$coupon_stored_data['issued']==true)
                   $unlink_coupon_codes[]=$coupon_id;
                
            }
            foreach ($unlink_coupon_codes as $coupon_id){
                //$_Cart=Mage::getModel('checkout/cart');
                unset($_SESSION['qixol_quoted_items']['coupons'][$coupon_id]);
            }
            unset($unlink_coupon_codes);

            $qixol_quoted_items=$getSinch->run_processOrder($quote);
            $_SESSION['qixol_quoted_items']=$qixol_quoted_items;

            $delivery_discount_amount=0;
            $recalcualte_totals=false;
            $totaldiscountAmount=0;
            $this->structure_to_store_in_order=array();
            $this->structure_to_store_in_order['items']=array();
            $this->structure_to_store_in_order['promotion_summary']=$_SESSION['qixol_quoted_items']['summary'];
            if (is_array($_SESSION['qixol_quoted_items'])){
                  foreach ($quote->getAllItems() as $item) {
                   //calculate total discount first
                    if (isset($_SESSION['qixol_quoted_items']['items'][$item->getId()]))
                           if ($_SESSION['qixol_quoted_items']['items'][$item->getId()]['updated_price']){
                                              $row_total_before_discount=$item->getRowTotal();
                                              $dicount_value=$row_total_before_discount-(float)$_SESSION['qixol_quoted_items']['items'][$item->getId()]['data']['lineamount'];
                                              
                                              $this->structure_to_store_in_order['items'][$item->getId()]['discount']=$dicount_value;
                                              $this->structure_to_store_in_order['items'][$item->getId()]['originalquantity']=$_SESSION['qixol_quoted_items']['items'][$item->getId()]['data']['originalquantity'];
                                              $this->structure_to_store_in_order['items'][$item->getId()]['quantity']=$_SESSION['qixol_quoted_items']['items'][$item->getId()]['data']['quantity'];
                                              $this->structure_to_store_in_order['items'][$item->getId()]['promotions']=$_SESSION['qixol_quoted_items']['items'][$item->getId()]['promotions'];
                                              if (is_array($promotions_data_hash))
                                              foreach ($this->structure_to_store_in_order['items'][$item->getId()]['promotions'] as $promotion_id=>$promotion_arr){
                                                 if (isset($promotions_data_hash[$promotion_id])){
                                                     $this->structure_to_store_in_order['items'][$item->getId()]['promotions'][$promotion_id]['description']=$promotions_data_hash[$promotion_id];
                                                 }else {
                                                      $cart_promotion_data=Mage::getModel('qixol/promotions')->load($promotion_id);
                                                      $promotions_data_hash[$promotion_id]=$cart_promotion_data->getPromotionText();
                                                      $this->structure_to_store_in_order['items'][$item->getId()]['promotions'][$promotion_id]['description']=$promotions_data_hash[$promotion_id];
                                                 }
                                              }

                                              if ($_SESSION['qixol_quoted_items']['items'][$item->getId()]['free_added']>0){
                                                 $this->structure_to_store_in_order['items'][$item->getId()]['free_added']=$_SESSION['qixol_quoted_items']['items'][$item->getId()]['free_added'];
                                              }
/*
                                              $item->setCustomPrice((float)$_SESSION['qixol_quoted_items']['items'][$item->getId()]['data']['price']);
                                              $item->setOriginalCustomPrice((float)$_SESSION['qixol_quoted_items']['items'][$item->getId()]['data']['price']);
                                              $item->setDiscountAmount($dicount_value);
                                              $item->setBaseDiscountAmount($dicount_value);
                                              $item->setRowTotalWithDiscount($row_total_before_discount - $dicount_value);
*/
                                              //$item->setRowTotal($row_total_before_discount-$dicount_value);
                                              //$item->setBaseRowTotal($row_total_before_discount-$dicount_value);
/*
                                              $item->getProduct()->setIsSuperMode(true);
                                              $item->save();
**/
                                              $totaldiscountAmount+=$dicount_value;
                                              /*$quote->setGrandTotal($quote->getGrandTotal()-$dicount_value);
                                              $quote->setBaseGrandTotal($quote->getBaseSubtotal()-$dicount_value);
                                              $quote->setSubtotal($quote->getBaseSubtotal()-$dicount_value);
                                              $quote->setBaseSubtotal($quote->getBaseSubtotal()-$dicount_value);
                                              $quote->setSubtotalWithDiscount($quote->getBaseSubtotal()-$dicount_value);
                                              $quote->setBaseSubtotalWithDiscount($quote->getBaseSubtotal()-$dicount_value);*/
                                              $recalcualte_totals=true;
                            }
                           /*elseif($_SESSION['qixol_quoted_items']['items'][$item->getId()]['data']['isdelivery']){
                                  foreach ($_SESSION['qixol_quoted_items']['items'][$item->getId()]['promotions'] as $delivery_promotion){
                                    if ($delivery_promotion['discountamount']>0){
                                        $delivery_discount_amount+=$delivery_promotion['discountamount'];
                                         $recalcualte_totals=true;
                                    }
                                  }
                           }*/
                            elseif(is_array($_SESSION['qixol_quoted_items']['items'][$item->getId()]['promotions'])){ //sometimes no linediscount,discount or other need to llok in ['items'][xxx]['promotions'][xxx][discountamount]
                                 foreach ($_SESSION['qixol_quoted_items']['items'][$item->getId()]['promotions'] as $promotion_id=>$check_promotion){
                                   if ($check_promotion['discountamount']>0&&$check_promotion['basketlevel']>0){
                                          $dicount_value+=$check_promotion['discountamount'];
                                          $this->structure_to_store_in_order['items'][$item->getId()]['promotions'][$promotion_id]=$_SESSION['qixol_quoted_items']['items'][$item->getId()]['promotions'][$promotion_id];
                                         if (is_array($promotions_data_hash))
                                              if (isset($promotions_data_hash[$promotion_id])){
                                                  $this->structure_to_store_in_order['items'][$item->getId()]['promotions'][$promotion_id]['description']=$promotions_data_hash[$promotion_id];
                                              }else {
                                                  $cart_promotion_data=Mage::getModel('qixol/promotions')->load($promotion_id);
                                                  $promotions_data_hash[$promotion_id]=$cart_promotion_data->getPromotionText();
                                                  $this->structure_to_store_in_order['items'][$item->getId()]['promotions'][$promotion_id]['description']=$promotions_data_hash[$promotion_id];
                                              }
                                      $totaldiscountAmount+=$dicount_value;
                                   }
                                 }
                            }/*elseif($_SESSION['qixol_quoted_items']['items'][$item->getId()]['data']['isdelivery']){
                                  foreach ($_SESSION['qixol_quoted_items']['items'][$item->getId()]['promotions'] as $delivery_promotion){
                                    if ($delivery_promotion['discountamount']>0){
                                        $delivery_discount_amount+=$delivery_promotion['discountamount'];
                                         $recalcualte_totals=true;
                                    }
                                  }
                           }*/
                  }
                  //as customer described
                  if (isset($_SESSION['qixol_quoted_items']['cart_data']['deliverytotaldiscount'])&&$_SESSION['qixol_quoted_items']['cart_data']['deliverytotaldiscount']>0){
                                $delivery_discount_amount=$_SESSION['qixol_quoted_items']['cart_data']['deliverytotaldiscount'];
                                $recalcualte_totals=true;
                  }               
            }

            if ($recalcualte_totals){

                $total=$quote->getBaseSubtotal();
                $quote->setSubtotal(0);
                $quote->setBaseSubtotal(0);

                $quote->setSubtotalWithDiscount(0);
                $quote->setBaseSubtotalWithDiscount(0);

                $quote->setGrandTotal(0);
                $quote->setBaseGrandTotal(0);


                $canAddItems = $quote->isVirtual()? ('billing') : ('shipping'); 
                foreach ($quote->getAllAddresses() as $address) {

                  
                          $address->setSubtotal(0);
                          $address->setBaseSubtotal(0);

                          $address->setGrandTotal(0);
                          $address->setBaseGrandTotal(0);

                          $address->collectTotals();

                          $quote->setSubtotal((float) $quote->getSubtotal() + $address->getSubtotal());
                          $quote->setBaseSubtotal((float) $quote->getBaseSubtotal() + $address->getBaseSubtotal());

                          $quote->setSubtotalWithDiscount(
                              (float) $quote->getSubtotalWithDiscount() + $address->getSubtotalWithDiscount()
                          );
                          $quote->setBaseSubtotalWithDiscount(
                              (float) $quote->getBaseSubtotalWithDiscount() + $address->getBaseSubtotalWithDiscount()
                          );

                          $quote->setGrandTotal((float) $quote->getGrandTotal() + $address->getGrandTotal());
                          $quote->setBaseGrandTotal((float) $quote->getBaseGrandTotal() + $address->getBaseGrandTotal());
              
                           $quote ->save(); 
              
                    $quote->setGrandTotal($quote->getBaseSubtotal()-$totaldiscountAmount-$delivery_discount_amount)
                    ->setBaseGrandTotal($quote->getBaseSubtotal()-$totaldiscountAmount-$delivery_discount_amount)
                    ->setSubtotalWithDiscount($quote->getBaseSubtotal()-$totaldiscountAmount-$delivery_discount_amount)
                    ->setBaseSubtotalWithDiscount($quote->getBaseSubtotal()-$totaldiscountAmount-$delivery_discount_amount)
                    ->save(); 
                    
                  
                  if($address->getAddressType()==$canAddItems) {

                  //echo $address->setDiscountAmount; exit;
                  $address->setSubtotalWithDiscount((float) $address->getSubtotalWithDiscount()-$totaldiscountAmount-$delivery_discount_amount);
                  $address->setGrandTotal((float) $address->getGrandTotal()-$totaldiscountAmount-$delivery_discount_amount);
                  $address->setBaseSubtotalWithDiscount((float) $address->getBaseSubtotalWithDiscount()-$totaldiscountAmount-$delivery_discount_amount);
                  $address->setBaseGrandTotal((float) $address->getBaseGrandTotal()-$totaldiscountAmount-$delivery_discount_amount);

                      if($address->getDiscountDescription()){

                          $address->setDiscountAmount(-($address->getDiscountAmount()+$totaldiscountAmount+$delivery_discount_amount));
                          $address->setDiscountDescription($address->getDiscountDescription().($totaldiscountAmount>0?', Qixol Discount':"").($delivery_discount_amount>0?", Delivery Discount":""));
                          $address->setBaseDiscountAmount(-($address->getBaseDiscountAmount()+$totaldiscountAmount+$delivery_discount_amount));
                      }else {

                        $address->setDiscountAmount(-($totaldiscountAmount+$delivery_discount_amount));
                        $address->setDiscountDescription(($totaldiscountAmount>0?' Qixol Discount':"").($delivery_discount_amount>0?($totaldiscountAmount>0?",":"")." Delivery Discount":""));
                        $address->setBaseDiscountAmount(-($totaldiscountAmount+$delivery_discount_amount));
                      }

                      $address->save();
                  }
                } 
//add free items

            if (is_array($_SESSION['qixol_quoted_items'])){
                  foreach ($quote->getAllItems() as $item) {
                    if (isset($_SESSION['qixol_quoted_items']['items'][$item->getId()]))
                           if ($_SESSION['qixol_quoted_items']['items'][$item->getId()]['updated_price']){
                                              $row_total_before_discount=$item->getRowTotal();
                                              //$item->setCustomPrice((float)$_SESSION['qixol_quoted_items']['items'][$item->getId()]['data']['price']);
                                              //$item->setOriginalCustomPrice((float)$_SESSION['qixol_quoted_items']['items'][$item->getId()]['data']['price']);
                                              $dicount_value=$row_total_before_discount-(float)$_SESSION['qixol_quoted_items']['items'][$item->getId()]['data']['lineamount'];
                                              $item->setDiscountAmount($dicount_value);
                                              $item->setBaseDiscountAmount($dicount_value);
                                              $item->setRowTotalWithDiscount($row_total_before_discount-$dicount_value);
                                              //$item->setRowTotal($row_total_before_discount-$dicount_value);
                                              //$item->setBaseRowTotal($row_total_before_discount-$dicount_value);
                                              $item->getProduct()->setIsSuperMode(true);
                                              $item->save();
                            }
                            if ($_SESSION['qixol_quoted_items']['items'][$item->getId()]['free_added']>0){
                                              $item->setQty($item->getQty()+$_SESSION['qixol_quoted_items']['items'][$item->getId()]['free_added']);
                                              $item->setDiscountAmount($item->getDiscountAmount()+($_SESSION['qixol_quoted_items']['items'][$item->getId()]['data']['originalprice']*$_SESSION['qixol_quoted_items']['items'][$item->getId()]['free_added']));
                                              $item->setBaseDiscountAmount($item->getBaseDiscountAmount()+($_SESSION['qixol_quoted_items']['items'][$item->getId()]['data']['originalprice']*$_SESSION['qixol_quoted_items']['items'][$item->getId()]['free_added']));
                                              $dicount_value=$dicount_value+($_SESSION['qixol_quoted_items']['items'][$item->getId()]['data']['originalprice']*$_SESSION['qixol_quoted_items']['items'][$item->getId()]['free_added']);
                                              $item->getProduct()->setIsSuperMode(true);
                                              $item->save();
                            }
                  }

                //add free prroducts
               foreach ($_SESSION['qixol_quoted_items']['items'] as $qixol_cart_items){
                      if ($qixol_cart_items['new']&&$qixol_cart_items['data']['isdelivery']==0){
                          if ($productId=Mage::getModel('catalog/product')->getIdBySku((string)$qixol_cart_items['data']['variantcode']!=''?(string)$qixol_cart_items['data']['variantcode']:(string)$qixol_cart_items['data']['productcode'])){
                            $productObj = Mage::getModel('catalog/product')
                            ->setStoreId(Mage::app()->getStore()->getId())
                            ->load($productId);
                            $item=$quote->addProduct($productObj,(int)$qixol_cart_items['data']['quantity']);
                            $item->setCustomPrice(0);
                            $item->save();
                          }
                      }
               }
            }

            }

            if (isset($_SESSION['qixol_quoted_items']['coupons'])&&count($_SESSION['qixol_quoted_items']['coupons'])){
                $coupons_issued='';
               $customerData_to_work_with = Mage::getSingleton('customer/session')->getCustomer();
                foreach ($_SESSION['qixol_quoted_items']['coupons'] as $_coupons_code=>$tmp_val){
                    if ((bool)$tmp_val['issued']==true){
                    $coupons_issued.="(".$_coupons_code.") - ".$tmp_val['description'];
                    //save issued coupon
                    $issued_coupon_model = Mage::getModel('qixol/issuedcoupon');
                    $issued_coupon_model->setCustomerId($customerData_to_work_with->getId());
                    $issued_coupon_model->setCouponCodeValue($_coupons_code);
                    $issued_coupon_model->setCouponValidTill($tmp_val['validtill']);
                    $issued_coupon_model->setCouponDescription($tmp_val['description']);
                    $issued_coupon_model->setCreatedTime(now());
                    $issued_coupon_model->save();
                    }
                }
                if (trim($coupons_issued)!=''){
                  $order = $observer->getOrder();
                  $order->addStatusHistoryComment(Mage::helper('qixol')->__("You have obtined coupon(s):".$coupons_issued), $order->getStatus())
                  ->setIsVisibleOnFront(true)
                  ->setIsCustomerNotified(true);
                }
            }
     
        $order = $observer->getOrder();            
        $order->setPromotionDataApplied(serialize($this->structure_to_store_in_order));
        //save issued points to customer
        foreach ($_SESSION['qixol_quoted_items']['summary'] as $summary_data_process){
            if($summary_data_process['data']['issuedpoints']>0){
               if (!isset($customerData_to_work_with))
                 $customerData_to_work_with = Mage::getSingleton('customer/session')->getCustomer();
               $this->recordPoints($summary_data_process['data']['issuedpoints'], $customerData_to_work_with->getId());
            }
        }


        $discounted_points = Mage::getSingleton('customer/session')->getPointsAmount();
        //subtract points for this order
        if ($discounted_points>0){
               if (!isset($customerData_to_work_with))
                 $customerData_to_work_with = Mage::getSingleton('customer/session')->getCustomer();
          $this->usePoints($discounted_points, $customerData_to_work_with->getId());
        }


        /*foreach ($order->getAllItems() as $item) {
                    if (isset($_SESSION['qixol_quoted_items']['items'][$item->getQuoteId()])){
                            if ($_SESSION['qixol_quoted_items']['items'][$item->getId()]['free_added']>0){
                                              $item->setQtyOrdered($item->getQtyOrdered()+$_SESSION['qixol_quoted_items']['items'][$item->getId()]['free_added']);
                                              $item->save();
                            }
                    }

        }*/
        //$order->save();
       //unset old shopping cart
//print_r($_SESSION['qixol_quoted_items']);
       unset($_SESSION['qixol_quoted_items']);
       unset($_SESSION['inside_request']);
       $_SESSION['structure_to_store_in_order']=$this->structure_to_store_in_order;

      }


      public function processSavePromotionsToOrder($observer){
         global $_SESSION;
         $order = $observer->getOrder();  
         $order->setPromotionDataApplied(serialize($_SESSION['structure_to_store_in_order']));
         $order->save();
         $this->structure_to_store_in_order=array();
         unset($_SESSION['structure_to_store_in_order']);
      }

      public function processShippingUpdated(Varien_Event_Observer $observer)
      {
            global $_SESSION;
            if (isset($_SESSION['inside_request'])&&(time()-$_SESSION['inside_request'])<1){//7 second prevent duplicate request
                  //echo "@".(time()-$_SESSION['inside_request'])."@";
                  return ;
            }


            $_SESSION['inside_request']=time();
            
            $getSinch=Mage::getModel('qixol/sinch');
            $cart=$observer->getEvent()->getQuote();
            if ($qixol_quoted_items_new=$getSinch->run_ImportCart($cart)){
                $qixol_quoted_items=$qixol_quoted_items_new; //if returned new structure
            }
            $qixol_quoted_items['time_checked']=time();
            $qixol_quoted_items['short_data']=$_SESSION['qixol_quoted_items']['short_data'];
            $qixol_quoted_items['cart_session_id']=$_SESSION['qixol_quoted_items']['cart_session_id'];
            $_SESSION['qixol_quoted_items']=$qixol_quoted_items;

            unset($_SESSION['inside_request']);
      }

      public function processGetCartPromo(Varien_Event_Observer $observer)
      {
            global $_SESSION;
            if (isset($_SESSION['inside_request'])&&(time()-$_SESSION['inside_request'])<4){//7 second prevent duplicate request
                  //echo "@".(time()-$_SESSION['inside_request'])."@";
                  return ;
            }

            $_SESSION['inside_request']=time();
            if ((!isset($_SESSION['qixol_quoted_items']['cart_session_id']))||$_SESSION['qixol_quoted_items']['cart_session_id']=='') $_SESSION['qixol_quoted_items']['cart_session_id']=md5(time());
            $qixol_quoted_items=array();
            $make_request_again=false;
            if (isset($_SESSION['qixol_quoted_items']))
              $qixol_quoted_items=$_SESSION['qixol_quoted_items'];
            else $make_request_again=true;

            //check difference between cards in productid and it quantity
            $cart = $observer->getEvent()->getCart()->getQuote();
            foreach ($cart->getAllItems() as $item) {
                 $product_search_tmp = Mage::getModel('catalog/product')->load($item->getProductId());
//$product_search_tmp->getSku().$item->getProduct()->getSku() use this structure to have unique combinations for simple and configured products
                 $short_data[$product_search_tmp->getSku().$item->getProduct()->getSku()]['qty']=$item->getQty();
            }

 
            if (!$make_request_again)
            if (!is_array($_SESSION['qixol_quoted_items']['short_data'])){
               $make_request_again=true;
            } elseif(count($_SESSION['qixol_quoted_items']['short_data'])!=count($short_data)) {
               $make_request_again=true;
            } else {
               $short_data_old=$_SESSION['qixol_quoted_items']['short_data'];
            }

            if ((!isset($_SESSION['qixol_quoted_items']['time_checked']))||(($_SESSION['qixol_quoted_items']['time_checked']+1200)<time())){ // rerequest in 10 minutes
               $make_request_again=true;
            }


            if (!$make_request_again){
               //now the cart have the same dimension, so if any product diffenrence it vill be visible simply
              foreach($short_data as $current_id=>$current_prod_obj){
                if ((!isset($short_data_old[$current_id]))||$short_data_old[$current_id]['qty']!=$current_prod_obj['qty']){
                     $make_request_again=true;
                     break;
                }
              }
            }

            if ((!$make_request_again)){
                   $shipping_price_exists=$cart->getShippingAddress()->getShippingAmount();
                   if ($shipping_price_exists!=(float)$_SESSION['qixol_quoted_items']['cart_data']['deliveryoriginalprice']){
                     $make_request_again=true;
                   }
            }
            if (count($cart->getAllItems())==0){
                   unset($_SESSION['qixol_quoted_items']['items']);
                   unset($_SESSION['qixol_quoted_items']['cart_data']);
                   unset($_SESSION['qixol_quoted_items']['short_data']);
                   unset($_SESSION['qixol_quoted_items']['promotions']);
                   unset($_SESSION['qixol_quoted_items']['messages']);
                   unset($_SESSION['qixol_quoted_items']['summary']);
                   $unlink_coupon=array();
                   foreach ($_SESSION['qixol_quoted_items']['coupons'] as $key_coupon_id => $coupon_data_tmp){
                      if ((bool)$coupon_data_tmp['issued']==true)
                         $unlink_coupon[$key_coupon_id]=$key_coupon_id;
                   }
                   if (count($unlink_coupon)>0)
                   foreach ($unlink_coupon as $key_coupon_id){
                        unset($_SESSION['qixol_quoted_items']['coupons'][$key_coupon_id]);
                   }
                   unset($qixol_quoted_items);
                   unset($short_data);
//print_r($_SESSION['qixol_quoted_items']);
//die();
            }

            //!!!!!!!!!!!!!!!!!!!!!!!            
            //$make_request_again=true;
            //!!!!!!!!!!!!!!!!!!!!!!!
            if ($make_request_again){
//echo "make_new_request<br>";
                $getSinch=Mage::getModel('qixol/sinch');
                if ($qixol_quoted_items_new=$getSinch->run_ImportCart($cart)){
                    $qixol_quoted_items=$qixol_quoted_items_new; //if returned new structure
                }
                $qixol_quoted_items['time_checked']=time();
                $qixol_quoted_items['short_data']=$short_data;
                $qixol_quoted_items['cart_session_id']=$_SESSION['qixol_quoted_items']['cart_session_id'];
                $_SESSION['qixol_quoted_items']=$qixol_quoted_items;
//print_r($_SESSION['qixol_quoted_items']);
//die();
            }
/*echo "<pre>";
print_r($_SESSION['qixol_quoted_items']);
echo "</pre>";*/
          unset($_SESSION['inside_request']);
      }

    public function recordPoints($pointsInt, $customerId) {
      $points = Mage::getModel('qixol/bonuspoints')->load($customerId);
      $points->addPoints($pointsInt, $customerId);
      $points->save();
      
    }


    public function usePoints($discounted, $customerId) {
      $pointsAmt = $discounted;
      $points = Mage::getModel('qixol/bonuspoints')->load($customerId);
      $points->subtractPoints($pointsAmt, $customerId);
      $points->save();
    }
}
