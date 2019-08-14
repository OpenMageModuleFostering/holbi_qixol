<?php

//ini_set('memory_limit','256M');
require_once ('config.php');
class Holbi_Qixol_Model_Sinch extends Mage_Core_Model_Abstract {
    private $process_export_status_table;
    private $process_export_status_id=array();
    private $export_poducts_statistic_table;
    private $export_poducts_statistic_id=array();
    private $export_by = 'MANUAL';

    function __construct(){
        //teble for store current sate
        $this->process_export_status_table=Mage::getSingleton('core/resource')->getTableName('qixol_process_export_status_table');
        //teble for store global state result
        $this->export_poducts_statistic_table=Mage::getSingleton('core/resource')->getTableName('qixol_product_export_stat');
        $this->_logFile=LOG_FILE;  
        $this->pushLog("constructor"); 
    }

    function pushLog($log){

        if(trim($log)!=''){
            Mage::log($log, null, $this->_logFile);
            //store in some log table if required
        }
    }

    function cron_run_export(){//call from cron product export
        $this->pushLog("Start export from cron:".date("Y-m-d H:i:s"));
        $this->export_by='CRON';
        $this->run_export_qixolData(); 
        $this->pushLog("Finish export from cron".date("Y-m-d H:i:s"));   
    }

    function cron_run_import(){//call from cron product export
        $this->pushLog("Start export from cron:".date("Y-m-d H:i:s"));
        $this->export_by='CRON';
         if (Mage::getStoreConfig('holbi/qixol/enabled')>0){
        $this->run_import_Promotions(); 
        $this->run_import_DayPromotions();
        }
        $this->pushLog("Finish export from cron".date("Y-m-d H:i:s"));   
    }

    function getDataOfLatestExport(){
        $_status=array();
        //return only first erroror or all success, or notjhing
        $tmp_data=$this->getExportStatus('customers');
        if ($tmp_data['id']>0&&$tmp_data['message']=='error'){
          $_status=array('last_message'=>'error','export_what'=>'Customers','status_export_message'=>$tmp_data['extended_message']);
          return $_status;
        }
        elseif($tmp_data['id']>0) $_status=array('last_message'=>'success','export_what'=>'Customers');

        $tmp_data=$this->getExportStatus('delivery');
        if ($tmp_data['id']>0&&$tmp_data['message']=='error'){
         $_status=array('last_message'=>'error','export_what'=>'Delivery','status_export_message'=>$tmp_data['extended_message']);
        }
        elseif ($tmp_data['id']>0) $_status=array('last_message'=>'success','export_what'=>'Delivery');

        $tmp_data=$this->getExportStatus('products');
        if ($tmp_data['id']>0&&$tmp_data['message']=='error'){
         $_status=array('last_message'=>'error','export_what'=>'Products','status_export_message'=>$tmp_data['extended_message']);
        }
        elseif ($tmp_data['id']>0) $_status=array('last_message'=>'success','export_what'=>'Customers');

        $tmp_data=$this->getExportStatus('currency');
        if ($tmp_data['id']>0&&$tmp_data['message']=='error'){
         $_status=array('last_message'=>'error','export_what'=>'Currency','status_export_message'=>$tmp_data['extended_message']);
        }
        elseif ($tmp_data['id']>0) $_status=array('last_message'=>'success','export_what'=>'Currency');
        
        $tmp_data=$this->getExportStatus('store');
        if ($tmp_data['id']>0&&$tmp_data['message']=='error'){
         $_status=array('last_message'=>'error','export_what'=>'Store','status_export_message'=>$tmp_data['extended_message']);
        }
        elseif ($tmp_data['id']>0) $_status=array('last_message'=>'success','export_what'=>'Store');
        //disabled ad duplicate now functionality of satat
        /*$query="SELECT 
                   *
            FROM ".$this->export_poducts_statistic_table." 
            ORDER BY export_id DESC LIMIT 1";
          $read_data = Mage::getSingleton('core/resource')->getConnection('core_read');
          // now $write is an instance of Zend_Db_Adapter_Abstract
          $readresult=$read_data->query($query);
          $_status = $readresult->fetch();*/
        return $_status;
    }
    
    function run_export(){ //run from admin area
       $this->run_export_qixolData();
       echo "done";
    }   
    
    function run_import(){
       if (Mage::getStoreConfig('holbi/qixol/enabled')>0){
         //temporary run here but should be by cron only...
         $this->run_import_Promotions();
         //temporary run here but should be by cron only...
         $this->run_import_DayPromotions();
      }
     echo "done";
    }

    function run_processOrder($cart){
        global $_SESSION;
        /* as customer described, there is no need to get validated cart , just set confirmed flag to  ValidateBasket
        $soapclient = new soapclient('http://evaluation.qixolpromo.com/BasketService.svc?singleWsdl', array('trace' => 1));
                  $types_array = $soapclient->__getTypes();
                  $functions_array = $soapclient->__getFunctions();
           if (isset($_SESSION['qixol_quoted_items']['cart_data']['id'])){
                try {
                  $result = $soapclient->__soapCall('RetrieveValidatedBasket', array('RetrieveValidatedBasket' => array('companyKey' => Mage::getStoreConfig('qixol/integraion/companykey'),'basketRef'=>$_SESSION['qixol_quoted_items']['cart_data']['id'])));
                  print_r($result);
                  //$xml_shopping_cart_validated=$result->ValidateBasketResult;
                  //replace backet and store order

                } catch (SoapFault $e) {
                          echo "REQUEST:\n" . $soapclient->__getLastRequestHeaders();
                          echo $soapclient->__getLastRequest() . "\n";

                          echo "RESPONSE:\n" . $soapclient->__getLastResponseHeaders();
                          echo $soapclient->__getLastResponse() . "\n";
              }
           }*/

         return $this->run_ImportCart($cart,true);// set true to confirm cart(process cart)

    }

    function run_ImportCart($cart,$set_confirmed=false){
        global $_SESSION;
        $_SESSION['inside_request']=time();

        $item_id=0;
        $data_products='';
        $coupons_applyed='';
      $customer_groupId = Mage::getSingleton('customer/session')->getCustomerGroupId();               
      if ($customer_groupId>0){
          $customergroup = Mage::getModel('customer/group')->load($customer_groupId);
          $customergroupName=$customergroup->getCode($customergroup);
      }else 
           $customergroupName='NOT LOGGED IN';

                foreach ($cart->getAllVisibleItems() as $item) {
                $item_id++;
//getProduct()->getIsVirtual()
$data_products.='<item id="'.$item->getId()/*$item_id*/.'" ';
if ($item->getProductType()=='configurable'){
    //$item->getOptionByCode('simple_product')->getValue();    
    $product_search_tmp = Mage::getModel('catalog/product')->load($item->getProductId());
    $data_products.='productcode="'.$product_search_tmp->getSku().'" variantcode="'.$item->getSku().'"';
//print_r($item->_getData('product'));
}/*else if ($item->getProductType()=='grouped'){
   //do not apply now
}else if ($item->getProductType()=='bundle'){
   //do not apply now
}*/else { //simple
    $data_products.=' productcode="'.$item->getSku().'" variantcode="" ';
}
$data_products.=' barcode=""  quantity="'.$item->getQty().'" price="'.$item->getPrice().'" ></item>';
//print_r(Zend_Debug::dump($item->debug()));

//print_r(Zend_Debug::dump($item->debug()));
//echo $data_products."<br>\n";

                }
//delivery methid if exists and delivery price....
$shipping_method_exists=$cart->getShippingAddress()->getShippingMethod();
$shipping_price_exists=0;
$product_to_enter_in_cart=array();
if ($shipping_method_exists!=''){
   $shipping_price_exists=$cart->getShippingAddress()->getShippingAmount();
if ($shipping_price_exists==0){//somethimes returns zero
        // sometimes magento returning shipping method but not returning correct price
        //searching price for method
        $address = $cart->getShippingAddress();
        $rates = $address->collectShippingRates()
                        ->getGroupedAllShippingRates();
        foreach ($rates as $carrier) {
            foreach ($carrier as $rate) {
                if ($rate->getCode()==$shipping_method_exists){
                     $shipping_price_exists=$rate->getPrice();
                     break;
                }
            }
        }
   }
}


   if (isset($_SESSION['qixol_quoted_items']['coupons'])&&count($_SESSION['qixol_quoted_items']['coupons'])){
      $coupons_applyed.='<coupons>';
      foreach ($_SESSION['qixol_quoted_items']['coupons'] as $entered_coupons=>$tmp_val){
          if ((bool)$tmp_val['issued']==false)
          $coupons_applyed.='<coupon code="'.trim($entered_coupons).'"/>';
      }
      $coupons_applyed.='</coupons>';
   }

          //get mapping    
          $list_customer_map_names=Mage::getModel('qixol/Customergrouspmap')->getCollection();

          $list_customer_map_names_exists=array();

          foreach ($list_customer_map_names as $list_map){
              $list_customer_map_names_exists[$list_map->getCustomerGroupName()]=$list_map->getCustomerGroupNameMap();
          }
          // end mapping array

          if (isset($list_customer_map_names_exists[$customergroupName])){
            $customergroupName=$list_customer_map_names_exists[$customergroupName];
          }

          //get mapping    
          $list_store_map_names=Mage::getModel('qixol/Storesmap')->getCollection();

          $list_store_map_names_exists=array();

          foreach ($list_store_map_names as $list_map){
              $list_store_map_names_exists[$list_map->getCustomerGroupName()]=$list_map->getCustomerGroupNameMap();
          }
          // end mapping array

          //get mapping    
          $list_shipping_map_names=Mage::getModel('qixol/Shippingmap')->getCollection();

          $list_shipping_map_names_exists=array();

          foreach ($list_shipping_map_names as $list_map){
              $list_shipping_map_names_exists[$list_map->getShippingName()]=$list_map->getShippingNameMap();
          }
          // end mapping array

                if ($data_products!=''){
//echo "call promotions";
                  $promotions='<basket id="'./*Mage::getSingleton("core/session")->getEncryptedSessionId()*/$_SESSION['qixol_quoted_items']['cart_session_id'].'" companykey="'.Mage::getStoreConfig('qixol/integraion/companykey').
                              '" baskettotal="'.$cart->getGrandTotal().
                              '" basketdate="'.date("Y-m-d\TH:i:s",strtotime("+ 1 DAY")).'" channel="'.Mage::getStoreConfig('qixol/syhchronized/channel').
                              '" storegroup="'.Mage::getStoreConfig('qixol/syhchronized/storegroup').'" store="'.(
                                    (isset($list_store_map_names_exists[Mage::app()->getStore()->getName()])&&trim($list_store_map_names_exists[Mage::app()->getStore()->getName()])!='')?
                                                            $list_store_map_names_exists[Mage::app()->getStore()->getName()]:
                                                             Mage::app()->getStore()->getName())/*Mage::getStoreConfig('qixol/syhchronized/channel')*/.

                              ($shipping_method_exists!=''?'" deliverymethod="'.$shipping_method_exists:"").
                              ($shipping_price_exists>0?'" deliveryprice="'.$shipping_price_exists:"").

                              '" customergroup="'.$customergroupName.
                              '" currencycode="'.Mage::app()->getStore()->getCurrentCurrencyCode().
                              ($set_confirmed?'" confirmed="true':"").
                              '" >'.$coupons_applyed.'<items>'.$data_products.'</items></basket>';
//echo $promotions."\n\n\n";
                  $soapclient = new soapclient('http://evaluation.qixolpromo.com/BasketService.svc?singleWsdl', array('trace' => 1));
                  //$types_array = $soapclient->__getTypes();
                  //$functions_array = $soapclient->__getFunctions();

                    try {

                      $result = $soapclient->__soapCall('ValidateBasket', array('ValidateBasket' => array('basketXml' => $promotions)));
                      $xml_shopping_cart_validated=$result->ValidateBasketResult;
//print_r($xml_shopping_cart_validated);
//echo $xml_shopping_cart_validated."\n\n\n";
//temporary....
//$xml_shopping_cart_validated='<response id="2d2pi81tbatge0pm14gkhr7ru5" date="2015-12-22T17:43:06" companykey="2U-6DyxID02m1-rddeplwA" customergroup="NOT LOGGED IN" channel="WEB" store="WEB" storegroup="WEB" manualdiscount="0" basketdiscount="15.0000" linesdiscount="0" totaldiscount="15.0000" baskettotal="78.0000" originalbaskettotal="78" deliverymanualdiscount="0" deliveryprice="0" deliverypromotiondiscount="0" deliverytotaldiscount="0" deliveryoriginalprice="0" totalissuedpoints="7800"><items><item id="46" engineid="1613" productcode="test_5" variantcode="" barcode="" price="33" manualdiscount="0" quantity="1" linepromotiondiscount="0" totaldiscount="0" originalprice="33" originalquantity="1" originalamount="33" appliedpromotioncount="2" isdelivery="false" lineamount="33"><description>test product 5</description><promotions><promotion id="119" discountamount="0" instance="1" forlineid="46" basketlevel="true" /><promotion id="116" discountamount="0" instance="1" forlineid="46" basketlevel="true" /></promotions></item><item id="47" engineid="1503" productcode="Test1_01" variantcode="" barcode="" price="9" manualdiscount="0" quantity="5" linepromotiondiscount="0" totaldiscount="0" originalprice="9" originalquantity="5" originalamount="45" appliedpromotioncount="2" isdelivery="false" lineamount="45"><description>test product 1</description><promotions><promotion id="119" discountamount="0" instance="1" forlineid="47" basketlevel="true" /><promotion id="116" discountamount="0" instance="1" forlineid="47" basketlevel="true" /></promotions></item><item id="48" engineid="0" productcode="123456" price="15.0000" manualdiscount="0" quantity="1" linepromotiondiscount="0" totaldiscount="0" originalprice="15.0000" originalquantity="1" originalamount="15.0000" appliedpromotioncount="1" isdelivery="false" generated="true" lineamount="15.0000"><description>test product 2</description><promotions><promotion id="116" discountamount="15.0000" instance="1" forlineid="48" basketlevel="true" /></promotions></item></items><coupons /><summary result="true" promotionsapplied="2"><promotions><promotion type="FREEPRODUCT" display="Free product" id="116" discountamount="15.0000" instance="1" basketlevel="true"><name>Qixol 7a</name><displaytext>Buy "test product 1 / Test1_01" get "test product 2 / 123456" free (applies ONCE only)</displaytext></promotion><promotion type="ISSUEPOINTS" display="Receive points" id="119" discountamount="0" instance="1" issuedpoints="7800" basketlevel="true"><name>Qixol 9</name><displaytext>Spend more than 50.00 - get points</displaytext></promotion></promotions><messages /></summary></response>';
//$xml_shopping_cart_validated='<response id="508ke06681i38qt23gmfv239n4" date="2015-12-23T14:11:44" companykey="2U-6DyxID02m1-rddeplwA" customergroup="NOT LOGGED IN" channel="WEB" store="WEB" storegroup="WEB" manualdiscount="0" basketdiscount="0" linesdiscount="13.50" totaldiscount="13.50" baskettotal="31.49" originalbaskettotal="44.99" deliverymanualdiscount="0" deliveryprice="0" deliverypromotiondiscount="0" deliverytotaldiscount="0" deliveryoriginalprice="0"><items><item id="62" engineid="1611" productcode="config_1" variantcode="test2_01_01" barcode="" price="31.49" manualdiscount="0" quantity="1" linepromotiondiscount="13.50" totaldiscount="13.50" originalprice="44.99" originalquantity="1" originalamount="44.99" appliedpromotioncount="1" isdelivery="false" lineamount="31.49"><description>Child 1</description><promotions><promotion id="121" discountamount="13.50" instance="1" forlineid="62" /></promotions></item></items><coupons /><summary result="true" promotionsapplied="1"><promotions><promotion type="PRODUCTSREDUCTION" display="Product discount" id="121" discountamount="13.50" instance="1"><name>Qixol 10</name><displaytext>Use an issued coupon, get 30% off any Size = L product</displaytext></promotion></promotions><messages /></summary></response>';
                      $new_cart_structure=array();
//$update_cart=false;
                      if (strlen($xml_shopping_cart_validated)>10){
                        $xml_object = simplexml_load_string($xml_shopping_cart_validated);
//print_r($xml_object);


                            $attributes_cart=$xml_object->attributes();
                            $new_cart_structure['cart_data']=array();
                            $new_cart_structure['cart_data']['id']=(isset($attributes_cart['id'])?(string)$attributes_cart['id']:0);
                            $new_cart_structure['cart_data']['manualdiscount']=(isset($attributes_cart['manualdiscount'])?(float)$attributes_cart['manualdiscount']:0);
                            $new_cart_structure['cart_data']['basketdiscount']=(isset($attributes_cart['basketdiscount'])?(float)$attributes_cart['basketdiscount']:0);
                            $new_cart_structure['cart_data']['linesdiscount']=(isset($attributes_cart['linesdiscount'])?(float)$attributes_cart['linesdiscount']:0);
                            $new_cart_structure['cart_data']['totaldiscount']=(isset($attributes_cart['totaldiscount'])?(float)$attributes_cart['totaldiscount']:0);
                            $new_cart_structure['cart_data']['baskettotal']=(isset($attributes_cart['baskettotal'])?(float)$attributes_cart['baskettotal']:0);
                            $new_cart_structure['cart_data']['originalbaskettotal']=(isset($attributes_cart['originalbaskettotal'])?(float)$attributes_cart['originalbaskettotal']:0);
                            $new_cart_structure['cart_data']['deliverymanualdiscount']=(isset($attributes_cart['deliverymanualdiscount'])?(float)$attributes_cart['deliverymanualdiscount']:0);
                            $new_cart_structure['cart_data']['deliveryprice']=(isset($attributes_cart['deliveryprice'])?(float)$attributes_cart['deliveryprice']:0);
                            $new_cart_structure['cart_data']['deliverypromotiondiscount']=(isset($attributes_cart['deliverypromotiondiscount'])?(float)$attributes_cart['deliverypromotiondiscount']:0);
                            $new_cart_structure['cart_data']['deliverytotaldiscount']=(isset($attributes_cart['deliverytotaldiscount'])?(float)$attributes_cart['deliverytotaldiscount']:0);
                            $new_cart_structure['cart_data']['deliveryoriginalprice']=(isset($attributes_cart['deliveryoriginalprice'])?(float)$attributes_cart['deliveryoriginalprice']:0);


                          if ($xml_object instanceof SimpleXMLElement) {
                                foreach ($xml_object as $xml_root_key=>$xml_object_sub){
                                if ($xml_root_key=='items'){
                                    $new_cart_structure['items']=array();
                                  foreach ($xml_object_sub as $item_key=>$xml_items_sub){
                                    $item_attributes=$xml_items_sub->attributes();

                                    $new_cart_structure['items'][(int)$item_attributes['id']]=array();

                                      $new_cart_structure['items'][(int)$item_attributes['id']]['data']['quoteid']=(int)$item_attributes['id'];
                                      $new_cart_structure['items'][(int)$item_attributes['id']]['data']['productcode']=(isset($item_attributes['productcode'])?(string)$item_attributes['productcode']:'');
                                      $new_cart_structure['items'][(int)$item_attributes['id']]['data']['variantcode']=(isset($item_attributes['variantcode'])?(string)$item_attributes['variantcode']:'');
                                      $new_cart_structure['items'][(int)$item_attributes['id']]['data']['price']=(isset($item_attributes['price'])?(float)$item_attributes['price']:0.0);
                                      $new_cart_structure['items'][(int)$item_attributes['id']]['data']['manualdiscount']=(isset($item_attributes['manualdiscount'])?(float)$item_attributes['manualdiscount']:0);
                                      $new_cart_structure['items'][(int)$item_attributes['id']]['data']['quantity']=(isset($item_attributes['quantity'])?(float)$item_attributes['quantity']:0);
                                      $new_cart_structure['items'][(int)$item_attributes['id']]['data']['linepromotiondiscount']=(isset($item_attributes['linepromotiondiscount'])?(float)$item_attributes['linepromotiondiscount']:0);
                                      $new_cart_structure['items'][(int)$item_attributes['id']]['data']['totaldiscount']=(isset($item_attributes['totaldiscount'])?(float)$item_attributes['totaldiscount']:0);
                                      $new_cart_structure['items'][(int)$item_attributes['id']]['data']['originalprice']=(isset($item_attributes['originalprice'])?(float)$item_attributes['originalprice']:0);
                                      $new_cart_structure['items'][(int)$item_attributes['id']]['data']['originalquantity']=(isset($item_attributes['originalquantity'])?(float)$item_attributes['originalquantity']:0);
                                      $new_cart_structure['items'][(int)$item_attributes['id']]['data']['originalamount']=(isset($item_attributes['originalamount'])?(float)$item_attributes['originalamount']:0);
                                      $new_cart_structure['items'][(int)$item_attributes['id']]['data']['appliedpromotioncount']=(isset($item_attributes['appliedpromotioncount'])?(float)$item_attributes['appliedpromotioncount']:0);
                                      $new_cart_structure['items'][(int)$item_attributes['id']]['data']['isdelivery']=(isset($item_attributes['isdelivery'])&&strtolower((string)$item_attributes['isdelivery'])=='true'?true:false);
                                      $new_cart_structure['items'][(int)$item_attributes['id']]['data']['totalissuedpoints']=(isset($item_attributes['totalissuedpoints'])?(int)$item_attributes['totalissuedpoints']:0);
                                      $new_cart_structure['items'][(int)$item_attributes['id']]['data']['splitfromlineid']=(isset($item_attributes['splitfromlineid'])?(int)$item_attributes['splitfromlineid']:0);
                                      $new_cart_structure['items'][(int)$item_attributes['id']]['data']['alwaysexcluded']=(isset($item_attributes['alwaysexcluded'])?(int)$item_attributes['alwaysexcluded']:0);
                                      $new_cart_structure['items'][(int)$item_attributes['id']]['data']['generated']=(isset($item_attributes['generated'])&&strtolower($item_attributes['generated'])=='true'?(true):false);
                                      $new_cart_structure['items'][(int)$item_attributes['id']]['data']['appliedpromotioncount']=(isset($item_attributes['appliedpromotioncount'])?(float)$item_attributes['appliedpromotioncount']:0);
                                      $new_cart_structure['items'][(int)$item_attributes['id']]['description']=(isset($xml_items_sub->description)?(string)$xml_items_sub->description:'');
                                      $new_cart_structure['items'][(int)$item_attributes['id']]['data']['lineamount']=(isset($item_attributes['lineamount'])?(float)$item_attributes['lineamount']:0);


                              //get cart item by cart item iD returned from validation

                            $item_not_found=true;
                            foreach ($cart->getAllVisibleItems() as $item) {
                                          if ($item->getId()==(int)$item_attributes['id']){
                                          $item_not_found=false;
                                          $cart_item=$item;
                                                unset($product_search_tmp_sku);
                                                if ($cart_item->getProductType()=='configurable'){
                                                  $product_search_tmp = Mage::getModel('catalog/product')->load($cart_item->getProductId());
                                                  $product_search_tmp_sku=$product_search_tmp->getSku();
                                                }
                                                    $new_cart_structure['items'][(int)$item_attributes['id']]['updated_qty']=false;
                                                    $new_cart_structure['items'][(int)$item_attributes['id']]['updated_price']=false;
                                                    $product_updated=false;
                                                //echo "/".$cart_item->getProductType()."||".(string)$item_attributes['productcode']."==".$product_search_tmp_sku."+".(string)$item_attributes['variantcode']."==".$cart_item->getSku()."||";
                                                if ((!$cart_item->isDeleted() && !$cart_item->getParentItemId()/*check is visible*/)&&($cart_item->getProductType()=='configurable'&&(string)$item_attributes['productcode']==$product_search_tmp_sku&&(string)$item_attributes['variantcode']==$cart_item->getSku()) 
                                                        || ((string)$item_attributes['variantcode']==''&&(string)$item_attributes['productcode']==$cart_item->getSku())){
                                                  if ($item_attributes['quantity']!=$cart_item->getQty()){
                                                      $new_cart_structure['items'][(int)$item_attributes['id']]['updated_qty']=true;
                                                    /* $cart_item->setQty($item_attributes['quantity']);
                                                      $product_updated=true;
                                                      */
                                                  }
                                                  if ((float)$item_attributes['lineamount']!=($cart_item->getPrice()*$cart_item->getQty())){
                                                        /*  echo "discount:".((float)$item_attributes['lineamount']/(float)$item_attributes['quantity'])."\n<br>\n";
                                                          $cart_item->setCustomPrice((float)$item_attributes['lineamount']/(float)$item_attributes['quantity']);
                                                          $cart_item->setOriginalCustomPrice((float)$item_attributes['lineamount']/(float)$item_attributes['quantity']);
                                                          $cart_item->getProduct()->setIsSuperMode(true);*/
                                                      $new_cart_structure['items'][(int)$item_attributes['id']]['updated_price']=true;
                                                      //$product_updated=true;
                                                  }

                                                    /*if ($product_updated){
                                                      $update_cart=true;
                                                      $cart_item->save();
                                                    }
                                                    echo $cart_item->getProductId().",,,,";*/

                                                } else {
                                                  //echo "different<br>";
                                                  if ((int)Mage::getStoreConfig('qixol/advanced/separateitem')>0){
                                                     $new_cart_structure['items'][(int)$item_attributes['id']]['new']=true;
                                                  } else {
                                                      $new_cart_structure['items'][(int)$item_attributes['id']]['updated_qty']=true;
                                                  }
                                                  //$product_to_enter_in_cart[]=$item_attributes;
                                                }
                                          }
                          }
                                  $is_splitted_line=false;
                                  if ($item_not_found){
                                    if (!isset($item_attributes['splitfromlineid'])||(int)$item_attributes['splitfromlineid']==0){
                                    //$product_to_enter_in_cart[]=$item_attributes;
                                    if ((int)Mage::getStoreConfig('qixol/advanced/separateitem')>0){
                                      $new_cart_structure['items'][(int)$item_attributes['id']]['new']=true;
                                    }else {
                                       $check_exists_in_cart=false; 
                                       foreach ($new_cart_structure['items'] as $current_item_cart_position => $cart_item_to_check){
                                                if ($current_item_cart_position == (int)$item_attributes['id']) continue ;
                                                if ($cart_item_to_check['data']['productcode']==(string)$item_attributes['productcode']&&$cart_item_to_check['data']['variantcode']==(string)$item_attributes['variantcode']){
                                                       $new_cart_structure['items'][$current_item_cart_position]['updated_qty']=true;
                                                       $new_cart_structure['items'][$current_item_cart_position]['free_added']=(isset($item_attributes['quantity'])?(float)$item_attributes['quantity']:0);
                                                       //$new_cart_structure['items'][$current_item_cart_position]['updated_price']=true;
                                                       //$new_cart_structure['items'][$current_item_cart_position]['data']['price']+=(isset($item_attributes['price'])?(float)$item_attributes['price']:0.0);
                                                       $new_cart_structure['items'][$current_item_cart_position]['data']['quantity']+=(isset($item_attributes['quantity'])?(float)$item_attributes['quantity']:0);
                                                       //$new_cart_structure['items'][$current_item_cart_position]['data']['originalamount']+=(isset($item_attributes['originalamount'])?(float)$item_attributes['originalamount']:0.0);
                                                       //$new_cart_structure['items'][$current_item_cart_position]['data']['totaldiscount']=(isset($item_attributes['originalamount'])?(float)$item_attributes['originalamount']:0.0);
                                                       $check_exists_in_cart=true;
                                                   }
                                       }
                                       if (!$check_exists_in_cart){
                                           $new_cart_structure['items'][(int)$item_attributes['id']]['new']=true;
                                           if ($new_cart_structure['items'][(int)$item_attributes['id']]['data']['lineamount']==0)
                                                      $new_cart_structure['items'][$current_item_cart_position]['free_added']=(isset($item_attributes['quantity'])?(float)$item_attributes['quantity']:0);
                                       }

                                    }
                                    } elseif((int)$item_attributes['splitfromlineid']>0) {
                                        //$new_cart_structure['items'][(int)$item_attributes['splitfromlineid']]['data']['quantity']+=(isset($item_attributes['quantity'])?(float)$item_attributes['quantity']:0);
                                        //remove updated quantity for splitted 
                                        $new_cart_structure['items'][(int)$item_attributes['splitfromlineid']]['updated_qty']=0;
                                        //for splitted there is possible different discount, should be recalcualted in main produc tdiscount
                                        $new_cart_structure['items'][(int)$item_attributes['splitfromlineid']]['data']['totaldiscount']+=(float)$item_attributes['totaldiscount'];
                                        $calcualted_discount=$new_cart_structure['items'][(int)$item_attributes['splitfromlineid']]['data']['totaldiscount']/$new_cart_structure['items'][(int)$item_attributes['splitfromlineid']]['data']['originalquantity'];
                                        $new_cart_structure['items'][(int)$item_attributes['splitfromlineid']]['data']['price']=$new_cart_structure['items'][(int)$item_attributes['splitfromlineid']]['data']['originalprice']-$calcualted_discount;
                                        $new_cart_structure['items'][(int)$item_attributes['splitfromlineid']]['data']['lineamount']=($new_cart_structure['items'][(int)$item_attributes['splitfromlineid']]['data']['price']*$new_cart_structure['items'][(int)$item_attributes['splitfromlineid']]['data']['originalquantity']);
                                        $is_splitted_line=true;
                                    }
                                    //echo "new_item";
                                  }


                                        foreach ($xml_items_sub as $item_tag_key=>$xml_item_sub){
                                        if($item_tag_key=='promotions'){
                                            $new_cart_structure['items'][(int)$item_attributes['id']]['promotions']=array();
                                            foreach ($xml_item_sub as $promotion_id=>$promotion){
                                                  $promotion_attributes=$promotion->attributes();
                                                  $new_cart_structure['items'][(int)$item_attributes['id']]['promotions'][(int)$promotion_attributes['id']]=array();
                                                  //print_r($promotion_attributes);
                                                  $new_cart_structure['items'][(int)$item_attributes['id']]['promotions'][(int)$promotion_attributes['id']]['id']=(int)$promotion_attributes['id'];

                                                  //$new_cart_structure['items'][(int)$item_attributes['id']]['promotions'][(int)$promotion_attributes['id']]['id']=(isset($promotion_attributes['discountamount'])?(float)$promotion_attributes['discountamount']:0);
                                                  $new_cart_structure['items'][(int)$item_attributes['id']]['promotions'][(int)$promotion_attributes['id']]['instance']=(isset($promotion_attributes['instance'])?(int)$promotion_attributes['instance']:0);
                                                  $new_cart_structure['items'][(int)$item_attributes['id']]['promotions'][(int)$promotion_attributes['id']]['basketlevel']=(isset($promotion_attributes['basketlevel'])&&strtolower($promotion_attributes['basketlevel'])=='true'?(true):false);
                                                  $new_cart_structure['items'][(int)$item_attributes['id']]['promotions'][(int)$promotion_attributes['id']]['discountamount']=(isset($promotion_attributes['discountamount'])?(float)$promotion_attributes['discountamount']:0);
                                                  if ($is_splitted_line){ //check is promotion exists in main linea
                                                        if (!isset($new_cart_structure['items'][(int)$item_attributes['splitfromlineid']]['promotions'][(int)$promotion_attributes['id']])){
                                                           $new_cart_structure['items'][(int)$item_attributes['splitfromlineid']]['promotions'][(int)$promotion_attributes['id']]=$new_cart_structure['items'][(int)$item_attributes['id']]['promotions'][(int)$promotion_attributes['id']];        
                                                        }
                                                  }
                                                  //the text and promotion data will be parsed from summary
                                                  /*if ((bool)$promotion_attributes['basketlevel']!=true){
                                                    $promotion_data=Mage::getModel('qixol/promotions')->load((int)$promotion_attributes['id']);
                                                    //print_r($promotion_data->getPromotionType());
                                                    //print_r($promotion_data->getPromotionText());
                                                  }*/
                                            }

                                          }
                                       }
                                  }
                                } elseif($xml_root_key=='coupons') {
                                  $new_cart_structure['coupons']=array();
//print_r($xml_object_sub);
                                  foreach ($xml_object_sub as $item_key=>$xml_items_sub){
                                   if ($item_key=='coupon'){
                                        $coupon_attributes=$xml_items_sub->attributes();
                                        if (!(strtolower((string)$coupon_attributes['issued'])=='true')){
                                        if(isset($coupon_attributes['code'])&&(string)$coupon_attributes['code']!=''){
                                           $new_cart_structure['coupons'][(string)$coupon_attributes['code']]['issued']=false;
                                           $new_cart_structure['coupons'][(string)$coupon_attributes['code']]['code']=(string)$coupon_attributes['code'];
                                           $new_cart_structure['coupons'][(string)$coupon_attributes['code']]['description']=(isset($xml_items_sub->couponname)&&(string)$xml_items_sub->couponname!=''?(string)$xml_items_sub->couponname:(string)$coupon_attributes['code']);
                                        } else {
                                           unset($_SESSION['qixol_quoted_items']['coupons'][(string)$coupon_attributes['code']]);
                                        }
                                        }else {


                                        //!!!!!!!!!!!!!!! get valid to for coupon
                                                  $validtill='0000-00-00 00:00:00';
                                                  $soapclient_coupon = new soapclient('http://evaluation.qixolpromo.com/BasketService.svc?singleWsdl', array('trace' => 1));                                           
                                                    try {
                                                        $update_item=false;
                                                        $result_coupon = $soapclient_coupon->__soapCall('ValidateCouponCode', array('ValidateCouponCode' => array('companyKey' => Mage::getStoreConfig('qixol/integraion/companykey'),'couponCode' => (string)$coupon_attributes['code'])));
                                                        $xml_coupon_code_validated=$result_coupon->ValidateCouponCodeResult;
                                                        if (strlen($xml_coupon_code_validated)>10){
                                                              $xml_coupon_object = simplexml_load_string($xml_coupon_code_validated);
                                                                    foreach ($xml_coupon_object as $xml_coupon_object_root_key=>$xml_coupon_object_object_sub){
                                                                      if ($xml_coupon_object_root_key=='coupon'){
                                                                                foreach ($xml_coupon_object_object_sub as $xml_coupon_object_coupon_key=>$xml_coupon_object_object_coupon){
                                                                                  if ($xml_coupon_object_coupon_key=='codes'){
                                                                                      foreach ($xml_coupon_object_object_coupon as $xml_coupon_object_object_coupon_obj){
                                                                                                $xml_coupon_object_object_coupon_attributes=$xml_coupon_object_object_coupon_obj->attributes();
                                                                                                $validtill=date("Y-m-d H:i:s",strtotime((string)$xml_coupon_object_object_coupon_attributes['validto']));
                                                                                            }                                            
                                                                                      }
                                                                                  }
                                                                              }
                                                                          }
                                                                      }

                                                    } catch (SoapFault $e) {
                                        ;
                                                    }
                                        //!!!!!!!!!!!!!!! end valid to for coupon
                                           $new_cart_structure['coupons'][(string)$coupon_attributes['code']]['description']=(string)$xml_items_sub->couponname;//(string)$coupon_attributes['reportingcode']
                                           $new_cart_structure['coupons'][(string)$coupon_attributes['code']]['validtill']=($validtill=='1970-01-01 00:00:00'?"0000-00-00 00:00:00":$validtill);//(string)$coupon_attributes['code']
                                           $new_cart_structure['coupons'][(string)$coupon_attributes['code']]['issued']=true;

                                        }
                                   }
                                  }
                                 //parce in future
                                } elseif($xml_root_key=='summary') {
                                  $new_cart_structure['summary']=array();
                                  foreach ($xml_object_sub as $item_key=>$xml_items_sub){
                                    if ($item_key=='promotions'){
                                      foreach ($xml_items_sub as $item_1_key=>$xml_item_promotion){

                                        $promotion_attributes=$xml_item_promotion->attributes();
                                        $new_cart_structure['summary'][(int)$promotion_attributes['id']]['data']['type']=(string)$promotion_attributes['type'];
                                        $new_cart_structure['summary'][(int)$promotion_attributes['id']]['data']['display']=(string)$promotion_attributes['display'];
                                        $new_cart_structure['summary'][(int)$promotion_attributes['id']]['data']['display_text']=(isset($xml_item_promotion->displaytext)?(string)$xml_item_promotion->displaytext:'');
                                        $new_cart_structure['summary'][(int)$promotion_attributes['id']]['data']['discountamount']=(isset($promotion_attributes['discountamount'])?(float)$promotion_attributes['discountamount']:0);
                                        $new_cart_structure['summary'][(int)$promotion_attributes['id']]['data']['basketlevel']=(isset($promotion_attributes['basketlevel'])&&strtolower($promotion_attributes['basketlevel'])=='true'?(true):false);
                                        $new_cart_structure['summary'][(int)$promotion_attributes['id']]['data']['deliverylevel']=(isset($promotion_attributes['deliverylevel'])&&strtolower($promotion_attributes['deliverylevel'])=='true'?(true):false);
                                        $new_cart_structure['summary'][(int)$promotion_attributes['id']]['data']['issuedpoints']=(isset($promotion_attributes['issuedpoints'])?((int)$promotion_attributes['issuedpoints']):0);
                                        $new_cart_structure['summary'][(int)$promotion_attributes['id']]['data']['issuedcoupon']=(isset($promotion_attributes['issuedcoupon'])&&strtolower($promotion_attributes['issuedcoupon'])=='true'?(true):false);
                                        $new_cart_structure['summary'][(int)$promotion_attributes['id']]['data']['unpublished']=(isset($promotion_attributes['unpublished'])&&strtolower($promotion_attributes['unpublished'])=='true'?(true):false);
                                        $new_cart_structure['summary'][(int)$promotion_attributes['id']]['data']['issuedproduct']=(isset($promotion_attributes['issuedproduct'])&&strtolower($promotion_attributes['issuedproduct'])=='true'?(true):false);
                                        $new_cart_structure['summary'][(int)$promotion_attributes['id']]['data']['description']=(isset($xml_item_promotion->description)?(string)$xml_item_promotion->description:'');
                                        $new_cart_structure['summary'][(int)$promotion_attributes['id']]['data']['name']=(isset($xml_item_promotion->name)?(string)$xml_item_promotion->name:'');
                                     }
                                  }elseif($item_key=='messages'){
                                    //messages will be here;
                                     $new_cart_structure['messages']=array();
                                  }
                                }

                                }
                             }
                          }
                      }

/* NO UPDATE CART HERE AS FOR NOW, WILL UPDETE BEFORE ORDER CREATION
              if ($product_to_enter_in_cart){
                  print_r($product_to_enter_in_cart);
              //add free product in cart
                $product_added=false;
                foreach ($product_to_enter_in_cart as $p_t_e){
                $product_got_id = Mage::getModel('catalog/product')->getIdBySku((string)$p_t_e['productcode']);
                if ($product_got_id>0){
                $product_to_add = Mage::getModel('catalog/product')
                ->setStoreId(Mage::app()->getStore()->getId())
                ->load($product_got_id);
                $stockItem = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product_to_add);
                $qty = $stockItem->getQty();                 
                // to do, check if product inventory is managed otherwise this can become a minus qty
                  if($product_to_add->isSaleable()&&$qty >= 0) {
                    $cart->addProduct($product_to_add);
                    $product_added=true;
                  }else {
                    echo "error stock";
                    //$cart->addNotificationMessage($cart,'notice',(string)$product_to_enter_in_cart['productcode']. ' cannot be added to the cart!');
                  }
                } else {
                    echo "error code";
                    //$cart->addNotificationMessage($cart,'notice',(string)$product_to_enter_in_cart['productcode']. ' '. $this->__(' cannot be added to the cart!'));
                }
               }
              }

              if ($product_added||$update_cart){
                         echo "cart update";
                        $cart->save();
              }
*/


                  //set own price
                 //$item->setCustomPrice($price);
                 //$item->setOriginalCustomPrice($price);
                      /*$promotions_xml=$result->RetrievePromotionsForProductsResult;
                      //print_r($result->RetrievePromotionsForProductsResult);*/

                      //store in database for promotions here
                      //$result->RetrievePromotionsForProductsResult


                    } catch (SoapFault $e) {
  
                      echo "REQUEST:\n" . $soapclient->__getLastRequestHeaders();
                      echo $soapclient->__getLastRequest() . "\n";

                      echo "RESPONSE:\n" . $soapclient->__getLastResponseHeaders();
                      echo $soapclient->__getLastResponse() . "\n";

                     // print_r($e->faultstring);


                    }
                 }
//print_r($new_cart_structure);
//die();
      if (isset($new_cart_structure)&&count($new_cart_structure)>0)
          return $new_cart_structure;

    }

    function parseDayPromotions($promotion_new_xml=''){
          $xml_object = simplexml_load_string($promotion_new_xml);

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

    function parsePromotions($promotion_new_xml=''){
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


    function run_import_Promotions(){
      //process get all product promotions cause qixol do not provede all promotions for a day without requesting per product,
      //maybe need to run once a 10 minutes
          $this->addExportStatus("process", 'promotions' ,'',0);

                  $soapclient = new soapclient('http://evaluation.qixolpromo.com/ExportService.svc?singleWsdl', array('trace' => 1));
                  //$types_array = $soapclient->__getTypes();

                  //$functions_array = $soapclient->__getFunctions();

                 $products_list = Mage::getModel('catalog/product')->getCollection()
                                  ->addAttributeToSelect('*')->addAttributeToFilter('visibility', array('neq'=>1))
                                  ->addAttributeToSort('entity_id', 'desc'); 

                foreach ($products_list as $product) {
                $data .= '<product productcode="'.$product->getSku().'" variantcode="" barcode="" price="'.$product->getPrice().'"><description>'.$this->CDT($product->getName()).'</description><imageurl>'.$this->CDT($product->getImage() != 'no_selection' ? $product->getImageUrl() : '').'</imageurl>';
                    $products_data.='<product productcode="'.$product->getSku().'" variantcode="" />';
                    if ($product->isConfigurable()){ //with variations
                            //$associatedAttributes = $product->getTypeInstance()->getConfigurableAttributesAsArray($product);    
                            $childs_products_list=$product->getTypeInstance()->getUsedProducts();
                            foreach ($childs_products_list as $childProduct_tmp) {    
                              $childProduct = Mage::getModel('catalog/product')->load($childProduct_tmp->getId());
                              $products_data.='<product productcode="'.$product->getSku().'" variantcode="'.$childProduct->getSku().'" />';
                            }
                   }

                }
                if ($products_data!=''){

                  $promotions='<request companykey="'.Mage::getStoreConfig('qixol/integraion/companykey').
                              '" validationdate="'.date("Y-m-d",strtotime("+ 1 DAY")).'T00:00:00" channel="'.Mage::getStoreConfig('qixol/syhchronized/channel').
                              '" storegroup="'.Mage::getStoreConfig('qixol/syhchronized/storegroup').'" store="'.Mage::getStoreConfig('qixol/syhchronized/channel').
                              '" validatefortime="false"><products>'.$products_data.'</products></request>';

                  $soapclient = new soapclient('http://evaluation.qixolpromo.com/ExportService.svc?singleWsdl', array('trace' => 1));
                  //$types_array = $soapclient->__getTypes();
                  //$functions_array = $soapclient->__getFunctions();

                    try {
                      $result = $soapclient->__soapCall('RetrievePromotionsForProducts', array('RetrievePromotionsForProducts' => array('xmlExportRequest' => $promotions)));
                      $promotions_xml=$result->RetrievePromotionsForProductsResult;
                      //print_r($result->RetrievePromotionsForProductsResult);
                       if ($promotions_xml!=''){
                         $this->parsePromotions($promotions_xml);
                         $this->addExportStatus("success", 'promotions' , 'imported' ,1);
                       }
                      //store in database for promotions here
                      //$result->RetrievePromotionsForProductsResult


                    } catch (SoapFault $e) {
                      print_r($e->faultstring);
                     $this->addExportStatus("error", 'promotions' , addslashes($e->faultstring) ,1);
                      $this->pushLog("Finish import promotions error ".$e->faultstring);
                    }
                 }



      return ;
    }

    function run_import_DayPromotions(){
               //retrieve promotion for a day 

                $this->addExportStatus("process", 'backetpromotions' ,'',0);
                  $promotions='<request companykey="'.Mage::getStoreConfig('qixol/integraion/companykey').
                              '" validationdate="'.date("Y-m-d",strtotime("+ 1 DAY")).'T00:00:00" channel="'.Mage::getStoreConfig('qixol/syhchronized/channel').
                              '" storegroup="'.Mage::getStoreConfig('qixol/syhchronized/storegroup').'" store="'.Mage::getStoreConfig('qixol/syhchronized/channel').
                              '" validatefortime="false"></request>';

                  $soapclient = new soapclient('http://evaluation.qixolpromo.com/ExportService.svc?singleWsdl', array('trace' => 1));
                  //$types_array = $soapclient->__getTypes();
                  //$functions_array = $soapclient->__getFunctions();

                    try {
                      $result = $soapclient->__soapCall('RetrievePromotionsForBaskets', array('RetrievePromotionsForBaskets' => array('xmlExportRequest' => $promotions)));
                      $promotions_xml=$result->RetrievePromotionsForBasketsResult;

                       if ($promotions_xml!=''){
                         $this->parseDayPromotions($promotions_xml);
                         $this->addExportStatus("success", 'backetpromotions' , 'imported' ,1);
                       }


                    } catch (SoapFault $e) {
                      print_r($e->faultstring);
                     $this->addExportStatus("error", 'backetpromotions' , addslashes($e->faultstring) ,1);
                      $this->pushLog("Finish import promotions error ".$e->faultstring);
                    }
    }


    function run_export_qixolData(){

       if (Mage::getStoreConfig('holbi/qixol/enabled')>0){
        //customers groups attribute
        //{{
        if (Mage::getStoreConfig('qixol/syhchronized/synchcustomer')>0){
         $curent_state=$this->getExportStatus('customers');
        //do not run again if in process
        if ($curent_state['id']==0||$curent_state['finished']==1||strtotime($curent_state['last_updated'])<strtotime("-1 hour")){


          //get mapping    
          $list_map_names=Mage::getModel('qixol/Customergrouspmap')->getCollection();

          $list_map_names_exists=array();

          foreach ($list_map_names as $list_map){
              $list_map_names_exists[$list_map->getCustomerGroupName()]=$list_map->getCustomerGroupNameMap();
          }
          // end mapping array

            $this->addExportStatus("process", 'customers' ,'',0);
            $customerGroupModel = new Mage_Customer_Model_Group();
            $customerGroups = array();
            $allCustomerGroups  = $customerGroupModel->getCollection()->toOptionHash();
            $selectedgroups=Mage::getStoreConfig('qixol/customers/list');
            if(trim($selectedgroups)!='')
              $selectedgroups_array=explode(",",$selectedgroups);

            $group_to_send='';
            foreach($allCustomerGroups as $key => $group){          
              if (trim($selectedgroups)==''||in_array($key,$selectedgroups_array)){
                $group_to_send.='<item display="'.((isset($list_map_names_exists[$group])&&trim($list_map_names_exists[$group])!='')?$list_map_names_exists[$group]:$group).'">'.$group.'</item>';  
              }
            }
            if ($group_to_send!='')
            {//entity="basket" 
              $data='<import companykey="'.Mage::getStoreConfig('qixol/integraion/companykey').'" attributetoken="customergroup"><items>'.$group_to_send.'</items></import>';
              $soapclient = new soapclient('http://evaluation.qixolpromo.com/ImportService.svc?singleWsdl', array('trace' => 1));
              $types_array = $soapclient->__getTypes();
              $functions_array = $soapclient->__getFunctions();

                try {
                  $result = $soapclient->__soapCall('ImportEntityAttributeValues', array('ImportEntityAttributeValues' => array('xmlToImport' => $data)));
                  $this->addExportStatus("success", 'customers' ,addslashes($result->ImportEntityAttributeValuesResult),1);
                } catch (SoapFault $e) {
                  $this->addExportStatus("error", 'customers' , addslashes($e->faultstring) ,1);
                }
            }
        }
       }
       //}}end customer groups send


        //shippings attributes
        //{{
        if (Mage::getStoreConfig('qixol/syhchronized/synchship')>0){
         $curent_state=$this->getExportStatus('delivery');
        //do not run again if in process
        if ($curent_state['id']==0||$curent_state['finished']==1||strtotime($curent_state['last_updated'])<strtotime("-1 hour")){
          $this->addExportStatus("process", 'delivery' ,'',0);
       
          //get mapping    
          $list_map_names=Mage::getModel('qixol/Shippingmap')->getCollection();

          $list_map_names_exists=array();

          foreach ($list_map_names as $list_map){
              $list_map_names_exists[$list_map->getShippingName()]=$list_map->getShippingNameMap();
          }
          // end mapping array

            //returns only active list
            $only_active=Mage::getStoreConfig('qixol/shippings/onlyactive');
            //!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
          // $only_active=1;
            if ($only_active>0)
              $methods = Mage::getSingleton('shipping/config')->getActiveCarriers();
            else 
              $methods = Mage::getSingleton('shipping/config')->getAllCarriers();

            $selectedgroups=Mage::getStoreConfig('qixol/shippings/list');

          unset($selectedgroups_array);
          if(trim($selectedgroups)!='')
            $selectedgroups_array=explode(",",$selectedgroups);
          $shipping_to_send='';
            foreach($methods as $_ccode => $_carrier)
            {
                $_methodOptions = array();
            try{ //some methods not allowed getAllowedMethods
                if($_methods = $_carrier->getAllowedMethods())
                {
                    foreach($_methods as $_mcode => $_method)
                    {
                        $_code = $_ccode . '_' . $_mcode;
                        if (trim($selectedgroups)==''||in_array($_code,$selectedgroups_array)){
                          $shipping_to_send.='<item display="'.((isset($list_map_names_exists[$_code])&&trim($list_map_names_exists[$_code])!='')?$list_map_names_exists[$_code]:(trim($_method)==''?$_code:$_method)).'">'.$_code.'</item>';  
                        }
                    }
                }
              }
              catch(Exception $e) {
              continue;
              }
            }

          if ($shipping_to_send!='')
          {//entity="basket" 
            $data='<import companykey="'.Mage::getStoreConfig('qixol/integraion/companykey').'" attributetoken="deliverymethod"><items>'.$shipping_to_send.'</items></import>';
            $soapclient = new soapclient('http://evaluation.qixolpromo.com/ImportService.svc?singleWsdl', array('trace' => 1));
            $types_array = $soapclient->__getTypes();
            $functions_array = $soapclient->__getFunctions();
              try {
                $result = $soapclient->__soapCall('ImportEntityAttributeValues', array('ImportEntityAttributeValues' => array('xmlToImport' => $data)));
                $this->addExportStatus("success", 'delivery' ,addslashes($result->ImportEntityAttributeValuesResult),1);
              } catch (SoapFault $e) {
                $this->addExportStatus("error", 'delivery', addslashes($e->faultstring) ,1);
              }
          }
      }
      }
    //}}

      //{{ currencies
        if (Mage::getStoreConfig('qixol/syhchronized/synchcurrency')>0){
         $curent_state=$this->getExportStatus('currency');
        //do not run again if in process
        if ($curent_state['id']==0||$curent_state['finished']==1||strtotime($curent_state['last_updated'])<strtotime("-1 hour")){
          $this->addExportStatus("process", 'currency' ,'',0);
           $currency_to_send='';
           $only_active_currency=Mage::getStoreConfig('currency/options/allow');
           $currencies_array = explode(',',$only_active_currency);
            foreach($currencies_array as $code_curr)
            {
                          $currency_to_send.='<item display="'.$code_curr.'">'. Mage::app()->getLocale()->currency( $code_curr )->getName().'</item>';  
            }

          if ($currency_to_send!='');
          {//entity="basket" 
            $data='<import companykey="'.Mage::getStoreConfig('qixol/integraion/companykey').'" attributetoken="currencycode"><items>'.$currency_to_send.'</items></import>';
            $soapclient = new soapclient('http://evaluation.qixolpromo.com/ImportService.svc?singleWsdl', array('trace' => 1));
            $types_array = $soapclient->__getTypes();
            $functions_array = $soapclient->__getFunctions();
              try {
                $result = $soapclient->__soapCall('ImportEntityAttributeValues', array('ImportEntityAttributeValues' => array('xmlToImport' => $data)));
                $this->addExportStatus("success", 'currency' ,addslashes($result->ImportEntityAttributeValuesResult),1);
              } catch (SoapFault $e) {
                $this->addExportStatus("error", 'currency', addslashes($e->faultstring) ,1);
              }
//temporary before fixed !!!!!!!!!!! (now not returns result)
                $this->addExportStatus("success", 'currency' ,addslashes($result->ImportEntityAttributeValuesResult),1);
//000000000000000
          }
      }
     }
      //}} currencies

      //{{ stores
        if (true/*Mage::getStoreConfig('qixol/syhchronized/synchstore')>0*/){

         $curent_state=$this->getExportStatus('store');
        //do not run again if in process
        if ($curent_state['id']==0||$curent_state['finished']==1||strtotime($curent_state['last_updated'])<strtotime("-1 hour")){
          $this->addExportStatus("process", 'store' ,'',0);
           $store_to_send='';

          //get mapping    
          $list_store_map_names=Mage::getModel('qixol/Storesmap')->getCollection();

          $list_store_map_names_exists=array();

          foreach ($list_store_map_names as $list_map){
              $list_store_map_names_exists[$list_map->getCustomerGroupName()]=$list_map->getCustomerGroupNameMap();
          }
          // end mapping array

            foreach (Mage::app()->getWebsites() as $website) {
                foreach ($website->getGroups() as $group) {
                    $stores = $group->getStores();
                    foreach ($stores as $store) {
                        $store_to_send.='<item display="'.((isset($list_store_map_names_exists[$store->getName()])&&trim($list_store_map_names_exists[$store->getName()])!='')?$list_store_map_names_exists[$store->getName()]:$store->getName()).'">'. $store->getName().'</item>';  
                        //$store is a store object
                    }
                }
            }

          if ($store_to_send!='');
          {//entity="basket" 
            $data='<import companykey="'.Mage::getStoreConfig('qixol/integraion/companykey').'" attributetoken="store"><items>'.$store_to_send.'</items></import>';
            $soapclient = new soapclient('http://evaluation.qixolpromo.com/ImportService.svc?singleWsdl', array('trace' => 1));
            $types_array = $soapclient->__getTypes();
            $functions_array = $soapclient->__getFunctions();
              try {
                $result = $soapclient->__soapCall('ImportEntityAttributeValues', array('ImportEntityAttributeValues' => array('xmlToImport' => $data)));
                $this->addExportStatus("success", 'store' ,addslashes($result->ImportEntityAttributeValuesResult),1);
              } catch (SoapFault $e) {
                $this->addExportStatus("error", 'store', addslashes($e->faultstring) ,1);
              }
//temporary before fixed !!!!!!!!!!! (now not returns result)
                $this->addExportStatus("success", 'store' ,addslashes($result->ImportEntityAttributeValuesResult),1);
//000000000000000
          }
      }



       }
      //}} stores



      //products
      if (Mage::getStoreConfig('qixol/syhchronized/synchproducts')>0){
       $curent_state=$this->getExportStatus('products');
        //prevent double run script
      if ($curent_state['id']==0||$curent_state['finished']==1||strtotime($curent_state['last_updated'])<strtotime("-1 hour")){

       $write_data = Mage::getSingleton('core/resource')->getConnection('core_write');
       $write_data->query("
         delete from ".$this->process_export_status_table." where export_what='products'
       ");

        //clear old data
       $write_data = Mage::getSingleton('core/resource')->getConnection('core_write');
       $write_data->query("delete from ".$this->export_poducts_statistic_table." where start_export<(now() - interval 1 month)");


        //process products here
        $number_products_exported=0;
        $this->addExportStatus("process", 'products' ,'',0);


        $products_list = Mage::getModel('catalog/product')->getCollection()->addAttributeToSelect('*')->addAttributeToFilter('visibility', array('neq'=>1))->addAttributeToSort('entity_id', 'desc'); 
        
        $products_deleted = Mage::getModel('qixol/Deletedproduct')->getCollection();
        $remove_deleted=array();

        if (count($products_list)||count($products_deleted)){
        $data = '<import companykey="'.Mage::getStoreConfig('qixol/integraion/companykey').'"><products>';
            $attributes=Mage::getStoreConfig('qixol/productattrib/attributes');

            //assign deleted product first
           if (count($products_deleted))
            foreach ($products_deleted as $products_deleted_data) {
                      $data .= '<product productcode="'.$products_deleted_data->getData('product_sku').'" variantcode="'.$products_deleted_data->getData('child_sku').'" barcode="" deleted="true"></product>';
                      $remove_deleted[]=$products_deleted_data->getData('entity_id');                
            }

           if (count($products_list))
            foreach ($products_list as $product) {
                $send_poduct=true;
                //for simple product checks is it as a child for configurable and do not send it if parent active 
                if (!$product->isConfigurable()){
                             $parentId = Mage::getResourceSingleton('catalog/product_type_configurable')->getParentIdsByChild($product->getId());
                             //sometimes could be grouped if such module installed
                              /*try {
                                    if((!$parentId)||(!is_array($parentId)))
                                    $parentId = Mage::getResourceSingleton('catalog/product_type_grouped')->getParentIdsByChild($product->getId());
                                }
                                catch(Exception $e) {
                                       ; //if object not supported appears exception                                
                                }*/
                            if (is_array($parentId)&&count($parentId))
                            foreach ($parentId as  $parent_product_id){
                                $prod_parent_obj = Mage::getModel('catalog/product')->load($parent_product_id);
                                      //do not send if this product exists as child in any active parent product
                                  if (strtolower($prod_parent_obj->getAttributeText('status'))=='enabled'&&$prod_parent_obj->getVisibility()>1){
                                    $send_poduct=false;
                                    break;
                                  }
                             }

                }
                if ($send_poduct){
                    $data .= '<product productcode="'.$product->getSku().'" variantcode="" barcode="" price="'.$product->getPrice().'"><description>'.$this->CDT($product->getName()).'</description><imageurl>'.$this->CDT($product->getImage() != 'no_selection' ? $product->getImageUrl() : '').'</imageurl>';
                    $attributes_arr=explode(",",$attributes);
                    if (count($attributes_arr)){
                        $data .= '<attributes>';
                        foreach ($attributes_arr as $attribute_id){
                            $is_attribute_text_value=$product->getResource()->getAttribute($attribute_id)->getFrontend()->getValue($product);
                            $data .= '<attribute><name>'.$attribute_id.'</name><value>'.$this->CDT($is_attribute_text_value!=''?$is_attribute_text_value:$product->getData($attribute_id)).'</value></attribute>';
                        }
                     if (Mage::getStoreConfig('qixol/syhchronized/synchcatproducts')>0){
                        foreach ($product->getCategoryIds() as $product_category_id){
                            if ($product_category_id==0) continue;
                            $current_ctaegory_id=$product_category_id;                     
                                $category_name_push='';
                                while($current_ctaegory_id != 0){
                                  $category = Mage::getModel('catalog/category')->load($current_ctaegory_id);
                                  $current_ctaegory_id=$category->getParentId();
                                  if (strtolower($category->getName())!='root catalog')
                                  $category_name_push=$category->getName().($category_name_push!=''?" / ".$category_name_push:"");
                                }
                                $data .= '<attribute><name>categorycode</name><value>'.$this->CDT($category_name_push).'</value></attribute>';
                        }
                      }

                        $data .= '</attributes>';
                    }
                    $data .= '</product>';

                    if ($product->isConfigurable()){ //with variations
                            //$associatedAttributes = $product->getTypeInstance()->getConfigurableAttributesAsArray($product);    
                            $childs_products_list=$product->getTypeInstance()->getUsedProducts();
                            foreach ($childs_products_list as $childProduct_tmp) {                           
                                $childProduct = Mage::getModel('catalog/product')->load($childProduct_tmp->getId());
                                $data .= '<product productcode="'.$product->getSku().'" variantcode="'.$childProduct->getSku().'" barcode="" price="'.$childProduct->getPrice().'"><description>'.$this->CDT($childProduct->getName()).'</description><imageurl>'.($childProduct->getImage() != 'no_selection' ? $childProduct->getImageUrl() : '').'</imageurl>';
                                  $attributes_arr=explode(",",$attributes);
                                  if (count($attributes_arr)){
                                      $data .= '<attributes>';
                                      foreach ($attributes_arr as $attribute_id){
                                          $is_attribute_text_value=$childProduct->getResource()->getAttribute($attribute_id)->getFrontend()->getValue($childProduct);
                                          $data .= '<attribute><name>'.$attribute_id.'</name><value>'.$this->CDT($is_attribute_text_value!=''?$is_attribute_text_value:$childProduct->getData($attribute_id)).'</value></attribute>';
                                      }
                                      $data .= '</attributes>';
                                  }
                                  $data .= '</product>';

                                $number_products_exported++;//clacualte also childs as in qixol it is separate products
                            }
                    }
                  $number_products_exported++;
               }
            }

        $data .= '</products></import>';
        if ($data!=''){
          $soapclient = new soapclient('http://evaluation.qixolpromo.com/ImportService.svc?singleWsdl', array('trace' => 1));
          $types_array = $soapclient->__getTypes();
          $functions_array = $soapclient->__getFunctions();
            try {
              $result = $soapclient->__soapCall('ImportProducts', array('ImportProducts' => array('xmlToImport' => $data)));
               if (is_array($remove_deleted)&&count($remove_deleted)>0){
                  $write_data = Mage::getSingleton('core/resource')->getConnection('core_write');
                  $write_data->query("delete from qixol_product_to_delete where entity_id in (".join(",",$remove_deleted).")");
               }
              $this->addExportStatus("success", 'products' ,addslashes($result->ImportProductsResult),1);
            } catch (SoapFault $e) {
              print_r($e->faultstring);
              $this->addExportStatus("error", 'products' ,addslashes($e->faultstring),1);
            }
         }
        }
        }
        } 
        //end product sennd block

        } //end config enabled
return ;
    }

    function CDT($in_str)
    {
      return "<![CDATA[".$in_str."]]>";
    }   

   function getExportStatus($for='products'){
            //should get ststus here
            //id ->database log, message=>last message in log, error - on error =1; finished->on script finished =1
        $query="SELECT 
                   *
            FROM ".$this->process_export_status_table." 
            where 
                export_what ='".$for."'
            ORDER BY id DESC LIMIT 1";
              //             where export_what='".$for."'   //for future should be spleted by each export type
          $read_data = Mage::getSingleton('core/resource')->getConnection('core_read');

          if ($readresult=$read_data->query($query)){
              $_status = $readresult->fetch();
          }else {
               $_status=array();
          }
          if($_status['id']>0){
            //if finished<0 - error appeared
            $messages=array('id'=>$_status['id'],'message'=>$_status['last_message'],'extended_message'=>$_status['extended_message'], 'error'=>((int)$_status['is_finished']<0?1:0), 'finished'=>((int)$_status['is_finished']>0?1:0),'last_updated'=>$_status['exports_last_updated']);
          } else {
            $messages=array('id'=>'0','message'=>'inactive', 'error'=>0, 'finished'=>0);
          }


        return $messages;

   }

    function addExportStatus($message, $for ,$extended_message='',$finished=0){
        if (!isset($this->process_export_status_id[$for])||$this->process_export_status_id[$for]==0){
            $query="INSERT INTO ".$this->process_export_status_table." 
                (last_message, export_what,exports_start,exports_last_updated,is_finished,extended_message) 
                VALUES('".$message."','".$for."',now(),now(), '".(int)$finished."','".$extended_message."')";    

            $write_data = Mage::getSingleton('core/resource')->getConnection('core_write');
            $write_data->query($query);
            $this->process_export_status_id[$for]=$write_data->lastInsertId();
        } else {
            $query="update ".$this->process_export_status_table." 
                 set last_message='".$message."', exports_last_updated=now(), is_finished='".(int)$finished."', extended_message='".$extended_message."'
                 where id=".(int)$this->process_export_status_id[$for]."";  
            $write_data = Mage::getSingleton('core/resource')->getConnection('core_write');
            $write_data->query($query);
        }

        //extended latst transactions stat table disabled as now it dublicate main status table function
       /* if ($this->export_poducts_statistic_id==0){
          $write_data = Mage::getSingleton('core/resource')->getConnection('core_write');
          $write_data->query("
            insert into ".$this->export_poducts_statistic_table."
              (start_export,number_of_items,status_export,export_by,status_export_message)
            values(now(),0,'process','".$this->export_by."','export start')
          ");
            $this->export_poducts_statistic_id=$write_data->lastInsertId();
        }
        if($finished==0){
                 $query = "UPDATE ".$this->export_poducts_statistic_table." 
                          SET status_export='".$message."' ,
                              status_export_message='".addslashes($extended_message)."'
                          WHERE export_id=".$this->export_poducts_statistic_id;
            $write_data = Mage::getSingleton('core/resource')->getConnection('core_write');
            $write_data->query($query);
        } else {
                  $query = "UPDATE ".$this->export_poducts_statistic_table." 
                          SET 
                            status_export='success', 
                            status_export_message='".addslashes($extended_message)."'
                            finish_export=now()  
                          WHERE 
                                export_id=".$this->export_poducts_statistic_id;
            $write_data = Mage::getSingleton('core/resource')->getConnection('core_write');
            $write_data->query($query);
        }*/
   return ;
    }
}
