<?php

//ini_set('memory_limit','256M');
require_once ('config.php');
require_once('RESTPromoService.php');
require_once('SOAPPromoService.php');

class Holbi_Qixol_Model_Sinch extends Mage_Core_Model_Abstract
{
    private $process_export_status_table;
    private $process_export_status_id=array();
    private $export_poducts_statistic_table;
    private $export_poducts_statistic_id=array();
    private $export_by = 'MANUAL';
    private $promoService;

    function __construct()
    {
        $this->process_export_status_table=Mage::getSingleton('core/resource')->getTableName('qixol_process_export_status_table');
        $this->_logFile=LOG_FILE;  
        $this->pushLog("constructor");

        if (Mage::getStoreConfig('qixol/integraion/serviceProtocol') == 'REST') {
            $this->promoService = new RESTPromoService();
        } else {
            $this->promoService = new SOAPPromoService();
        }
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
        $this->run_import_promotionsForProducts(); 
        $this->run_import_promotionsForBaskets();
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
    
    function run_export(){
       $this->run_export_qixolData();
       echo 'done';
    }   
    
    function run_import(){
        if (Mage::getStoreConfig('holbi/qixol/enabled') == 0){
            return;
        }
        $this->run_import_promotionsForProducts();
        $this->run_import_promotionsForBaskets();
        echo 'done';
    }

    function run_processOrder($cart){
        global $_SESSION;
        return $this->run_ImportCart($cart,true);// set true to confirm cart(process cart)
    }

    function run_ImportCart($cart,$set_confirmed=false){
        global $_SESSION;
        $_SESSION['inside_request']=time();

        $item_id=0;
        $data_products='';
        $coupons_applyed='';
        $customer_groupId = Mage::getSingleton('customer/session')->getCustomerGroupId();               
        if ($customer_groupId > 0)
        {
            $customergroup = Mage::getModel('customer/group')->load($customer_groupId);
            $customergroupName=$customergroup->getCode($customergroup);
        }
        else
        {
            $customergroupName='NOT LOGGED IN';
        }

        foreach ($cart->getAllVisibleItems() as $item)
        {
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
        if ($shipping_method_exists!='')
        {
            $shipping_price_exists=$cart->getShippingAddress()->getShippingAmount();
            if ($shipping_price_exists==0)
            {//somethimes returns zero
                // sometimes magento returning shipping method but not returning correct price
                //searching price for method
                $address = $cart->getShippingAddress();
                $rates = $address->collectShippingRates()
                                ->getGroupedAllShippingRates();
                foreach ($rates as $carrier)
                {
                    foreach ($carrier as $rate)
                    {
                        if ($rate->getCode()==$shipping_method_exists)
                        {
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
        // TODO: this conversion from data to array happens in lots of places - move it to the model?
        $list_customer_map_names=Mage::getModel('qixol/Customergrouspmap')->getCollection();
        $list_customer_integration_codes=array();
        foreach ($list_customer_map_names as $list_map)
        {
            $list_customer_integration_codes[$list_map->getCustomerGroupName()]=$list_map->getIntegrationCode();
        }
        // end mapping array

        if (isset($list_customer_integration_codes[$customergroupName]))
        {
            $customergroupName=$list_customer_integration_codes[$customergroupName];
        }

        //get mapping    
        $list_store_integration_codes=Mage::getModel('qixol/Storesmap')->getCollection();

        $list_store_integration_codes_exists=array();

        foreach ($list_store_integration_codes as $list_map)
        {
            $list_store_integration_codes_exists[$list_map->getWebsite()][$list_map->getStoreGroup()][$list_map->getStoreName()]=$list_map->getIntegrationCode();
        }
        // end mapping array

        //get mapping    
        $list_shipping_map_names=Mage::getModel('qixol/Shippingmap')->getCollection();

        $list_shipping_integration_codes=array();

        foreach ($list_shipping_map_names as $list_map)
        {
            $list_shipping_integration_codes[$list_map->getCarrier()][$list_map->getStoreGroup()][$list_map->getShippingName()]=$list_map->getIntegrationCode();
        }
        // end mapping array

        $store_integration_code = $list_store_integration_codes_exists[Mage::app()->getStore()->getWebsite()->getName()][Mage::app()->getStore()->getGroup()->getName()][Mage::app()->getStore()->getName()];
        
        if ($data_products!='')
        {
            //echo "call promotions";
            $basketTotal = $cart->getSubtotal();
            if ($shipping_price_exists > 0)
            {
                $basketTotal += (float)$shipping_price_exists;
            }
            $basket = '<basket id="';
            //$basket .= /*Mage::getSingleton("core/session")->getEncryptedSessionId();*/
            $basket .= $_SESSION['qixol_quoted_items']['cart_session_id'];
            $basket .= '" companykey="';
            $basket .= Mage::getStoreConfig('qixol/integraion/companykey');
            $basket .= '" baskettotal="';
            $basket .= $basketTotal;
            $basket .= '" basketdate="';
            $basket .= date("Y-m-d\TH:i:s",strtotime("+ 1 DAY"));
            $basket .= '" channel="';
            $basket .= Mage::app()->getStore()->getWebsite()->getName();
            $basket .= '" storegroup="';
            $basket .= Mage::app()->getStore()->getGroup()->getName();
            $basket .= '" store="';
            if (!empty($store_integration_code))
            {
                $basket .= $store_integration_code;
            }
            else
            {
                $basket .= Mage::app()->getStore()->getName();
            }
            $basket .= '"';
            
            // /*Mage::getStoreConfig('qixol/syhchronized/channel')*/.
            if ($shipping_method_exists != '')
            {
                $basket .= ' deliverymethod="';
                $basket .= $shipping_method_exists;
                $basket .= '"';
            }

            if ($shipping_price_exists > 0)
            {
                $basket .= ' deliveryprice="';
                $basket .= $shipping_price_exists;
                $basket .= '"';
            }

            $basket .= ' customergroup="';
            $basket .= $customergroupName;
            $basket .= '"';
            
            $basket .= ' currencycode="';
            $basket .= Mage::app()->getStore()->getCurrentCurrencyCode();
            $basket .= '"';
            
            if ($set_confirmed)
            {
                $basket .= ' confirmed="true"';
            }
            
            $basket .= '>';

            $basket .= $coupons_applyed;
            
            $basket .= '<items>';
            $basket .= $data_products;
            $basket .= '</items></basket>';

            error_log($basket);
            $result = $this->promoService->BasketValidate($basket);
            error_log($result->message);
            if ($result->success)
            {
                $xml_shopping_cart_validated = $result->message;
                $new_cart_structure=array();
                if (strlen($xml_shopping_cart_validated)>10)
                {
                    $xml_object = simplexml_load_string($xml_shopping_cart_validated);

                    $attributes_cart=$xml_object->attributes();
                    $new_cart_structure['cart_data']=array();
                    $new_cart_structure['cart_data']['id']=(isset($attributes_cart['id'])?(string)$attributes_cart['id']:0);
                    $new_cart_structure['cart_data']['manualdiscount']=(isset($attributes_cart['manualdiscount'])?(float)$attributes_cart['manualdiscount']:0);
                    $new_cart_structure['cart_data']['basketdiscount']=(isset($attributes_cart['basketdiscount'])?(float)$attributes_cart['basketdiscount']:0);
                    $new_cart_structure['cart_data']['linestotaldiscount']=(isset($attributes_cart['linestotaldiscount'])?(float)$attributes_cart['linestotaldiscount']:0);
                    $new_cart_structure['cart_data']['totaldiscount']=(isset($attributes_cart['totaldiscount'])?(float)$attributes_cart['totaldiscount']:0);
                    $new_cart_structure['cart_data']['baskettotal']=(isset($attributes_cart['baskettotal'])?(float)$attributes_cart['baskettotal']:0);
                    $new_cart_structure['cart_data']['originalbaskettotal']=(isset($attributes_cart['originalbaskettotal'])?(float)$attributes_cart['originalbaskettotal']:0);
                    $new_cart_structure['cart_data']['deliverymanualdiscount']=(isset($attributes_cart['deliverymanualdiscount'])?(float)$attributes_cart['deliverymanualdiscount']:0);
                    $new_cart_structure['cart_data']['deliveryprice']=(isset($attributes_cart['deliveryprice'])?(float)$attributes_cart['deliveryprice']:0);
                    $new_cart_structure['cart_data']['deliverypromotiondiscount']=(isset($attributes_cart['deliverypromotiondiscount'])?(float)$attributes_cart['deliverypromotiondiscount']:0);
                    $new_cart_structure['cart_data']['deliverytotaldiscount']=(isset($attributes_cart['deliverytotaldiscount'])?(float)$attributes_cart['deliverytotaldiscount']:0);
                    $new_cart_structure['cart_data']['deliveryoriginalprice']=(isset($attributes_cart['deliveryoriginalprice'])?(float)$attributes_cart['deliveryoriginalprice']:0);


                    if ($xml_object instanceof SimpleXMLElement)
                    {
                        foreach ($xml_object as $xml_root_key=>$xml_object_sub)
                        {
                            if ($xml_root_key=='items')
                            {
                                $new_cart_structure['items']=array();
                                foreach ($xml_object_sub as $item_key=>$xml_items_sub)
                                {
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
                                    foreach ($cart->getAllVisibleItems() as $item)
                                    {
                                        if ($item->getId()==(int)$item_attributes['id'])
                                        {
                                            $item_not_found=false;
                                            $cart_item=$item;
                                            unset($product_search_tmp_sku);
                                            if ($cart_item->getProductType()=='configurable')
                                            {
                                                $product_search_tmp = Mage::getModel('catalog/product')->load($cart_item->getProductId());
                                                $product_search_tmp_sku=$product_search_tmp->getSku();
                                            }
                                            $new_cart_structure['items'][(int)$item_attributes['id']]['updated_qty']=false;
                                            $new_cart_structure['items'][(int)$item_attributes['id']]['updated_price']=false;
                                            $product_updated=false;
                                            //echo "/".$cart_item->getProductType()."||".(string)$item_attributes['productcode']."==".$product_search_tmp_sku."+".(string)$item_attributes['variantcode']."==".$cart_item->getSku()."||";
                                            if ((!$cart_item->isDeleted() && !$cart_item->getParentItemId()/*check is visible*/)&&($cart_item->getProductType()=='configurable'&&(string)$item_attributes['productcode']==$product_search_tmp_sku&&(string)$item_attributes['variantcode']==$cart_item->getSku()) 
                                                        || ((string)$item_attributes['variantcode']==''&&(string)$item_attributes['productcode']==$cart_item->getSku()))
                                            {
                                                if ($item_attributes['quantity']!=$cart_item->getQty())
                                                {
                                                    $new_cart_structure['items'][(int)$item_attributes['id']]['updated_qty']=true;
                                                    /* $cart_item->setQty($item_attributes['quantity']);
                                                        $product_updated=true;
                                                    */
                                                }
                                                if ((float)$item_attributes['lineamount']!=($cart_item->getPrice()*$cart_item->getQty()))
                                                {
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

                                            }
                                            else
                                            {
                                                //echo "different<br>";
                                                if ((int)Mage::getStoreConfig('qixol/advanced/separateitem')>0)
                                                {
                                                    $new_cart_structure['items'][(int)$item_attributes['id']]['new']=true;
                                                }
                                                else
                                                {
                                                    $new_cart_structure['items'][(int)$item_attributes['id']]['updated_qty']=true;
                                                }
                                                //$product_to_enter_in_cart[]=$item_attributes;
                                            }
                                        }
                                    }
                                    $is_splitted_line=false;
                                    if ($item_not_found)
                                    {
                                        if (!isset($item_attributes['splitfromlineid'])||(int)$item_attributes['splitfromlineid']==0)
                                        {
                                        //$product_to_enter_in_cart[]=$item_attributes;
                                        if ((int)Mage::getStoreConfig('qixol/advanced/separateitem')>0)
                                        {
                                            $new_cart_structure['items'][(int)$item_attributes['id']]['new']=true;
                                        }
                                        else
                                        {
                                        $check_exists_in_cart=false; 
                                        foreach ($new_cart_structure['items'] as $current_item_cart_position => $cart_item_to_check)
                                        {
                                            if ($current_item_cart_position == (int)$item_attributes['id'])
                                            {
                                                continue;
                                            }
                                                
                                            if ($cart_item_to_check['data']['productcode']==(string)$item_attributes['productcode']&&$cart_item_to_check['data']['variantcode']==(string)$item_attributes['variantcode'])
                                            {
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
                                        if (!$check_exists_in_cart)
                                        {
                                            $new_cart_structure['items'][(int)$item_attributes['id']]['new']=true;
                                            if ($new_cart_structure['items'][(int)$item_attributes['id']]['data']['lineamount']==0)
                                            {
                                                $new_cart_structure['items'][$current_item_cart_position]['free_added']=(isset($item_attributes['quantity'])?(float)$item_attributes['quantity']:0);
                                            }
                                        }

                                    }
                                }
                                elseif((int)$item_attributes['splitfromlineid']>0)
                                {
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
                            foreach ($xml_items_sub as $item_tag_key=>$xml_item_sub)
                            {
                                if($item_tag_key=='promotions')
                                {
                                    $new_cart_structure['items'][(int)$item_attributes['id']]['promotions']=array();
                                    foreach ($xml_item_sub as $promotion_id=>$promotion)
                                    {
                                        $promotion_attributes=$promotion->attributes();
                                        $new_cart_structure['items'][(int)$item_attributes['id']]['promotions'][(int)$promotion_attributes['id']]=array();
                                        //print_r($promotion_attributes);
                                        $new_cart_structure['items'][(int)$item_attributes['id']]['promotions'][(int)$promotion_attributes['id']]['id']=(int)$promotion_attributes['id'];

                                        //$new_cart_structure['items'][(int)$item_attributes['id']]['promotions'][(int)$promotion_attributes['id']]['id']=(isset($promotion_attributes['discountamount'])?(float)$promotion_attributes['discountamount']:0);
                                        $new_cart_structure['items'][(int)$item_attributes['id']]['promotions'][(int)$promotion_attributes['id']]['instance']=(isset($promotion_attributes['instance'])?(int)$promotion_attributes['instance']:0);
                                        $new_cart_structure['items'][(int)$item_attributes['id']]['promotions'][(int)$promotion_attributes['id']]['basketlevel']=(isset($promotion_attributes['basketlevel'])&&strtolower($promotion_attributes['basketlevel'])=='true'?(true):false);
                                        $new_cart_structure['items'][(int)$item_attributes['id']]['promotions'][(int)$promotion_attributes['id']]['discountamount']=(isset($promotion_attributes['discountamount'])?(float)$promotion_attributes['discountamount']:0);
                                        if ($is_splitted_line)
                                        { //check is promotion exists in main linea
                                            if (!isset($new_cart_structure['items'][(int)$item_attributes['splitfromlineid']]['promotions'][(int)$promotion_attributes['id']]))
                                            {
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
                    }
                    elseif($xml_root_key=='coupons')
                    {
                        $new_cart_structure['coupons']=array();
                        //print_r($xml_object_sub);
                        foreach ($xml_object_sub as $item_key=>$xml_items_sub)
                        {
                            if ($item_key=='coupon')
                            {
                                $coupon_attributes=$xml_items_sub->attributes();
                                if (!(strtolower((string)$coupon_attributes['issued'])=='true'))
                                {
                                    if(isset($coupon_attributes['code'])&&(string)$coupon_attributes['code']!='')
                                    {
                                        $new_cart_structure['coupons'][(string)$coupon_attributes['code']]['issued']=false;
                                        $new_cart_structure['coupons'][(string)$coupon_attributes['code']]['code']=(string)$coupon_attributes['code'];
                                        $new_cart_structure['coupons'][(string)$coupon_attributes['code']]['description']=(isset($xml_items_sub->couponname)&&(string)$xml_items_sub->couponname!=''?(string)$xml_items_sub->couponname:(string)$coupon_attributes['code']);
                                    }
                                    else
                                    {
                                        unset($_SESSION['qixol_quoted_items']['coupons'][(string)$coupon_attributes['code']]);
                                    }
                                }
                                else
                                {
                                    //!!!!!!!!!!!!!!! get valid to for coupon
                                    $validtill='0000-00-00 00:00:00';

                                    $result = $this->promoService->CouponCodeValidate((string)$coupon_attributes['code']);
                                    if ($result->success)
                                    {
                                        $update_item=false;
                                        $xml_coupon_code_validated = $result->message;
                                        if (strlen($xml_coupon_code_validated)>10)
                                        {
                                            $xml_coupon_object = simplexml_load_string($xml_coupon_code_validated);
                                            foreach ($xml_coupon_object as $xml_coupon_object_root_key=>$xml_coupon_object_object_sub)
                                            {
                                                if ($xml_coupon_object_root_key=='coupon')
                                                {
                                                    foreach ($xml_coupon_object_object_sub as $xml_coupon_object_coupon_key=>$xml_coupon_object_object_coupon)
                                                    {
                                                        if ($xml_coupon_object_coupon_key=='codes')
                                                        {
                                                            foreach ($xml_coupon_object_object_coupon as $xml_coupon_object_object_coupon_obj)
                                                            {
                                                                $xml_coupon_object_object_coupon_attributes=$xml_coupon_object_object_coupon_obj->attributes();
                                                                $validtill=date("Y-m-d H:i:s",strtotime((string)$xml_coupon_object_object_coupon_attributes['validto']));
                                                            }                                            
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }
                                    //!!!!!!!!!!!!!!! end valid to for coupon
                                    $new_cart_structure['coupons'][(string)$coupon_attributes['code']]['description']=(string)$xml_items_sub->couponname;//(string)$coupon_attributes['reportingcode']
                                    $new_cart_structure['coupons'][(string)$coupon_attributes['code']]['validtill']=($validtill=='1970-01-01 00:00:00'?"0000-00-00 00:00:00":$validtill);//(string)$coupon_attributes['code']
                                    $new_cart_structure['coupons'][(string)$coupon_attributes['code']]['issued']=true;

                                }
                            }
                        }
                        //parce in future
                    }
                    elseif($xml_root_key=='summary')
                    {
                        $new_cart_structure['summary']=array();
                        foreach ($xml_object_sub as $item_key=>$xml_items_sub)
                        {
                            if ($item_key=='promotions')
                            {
                                foreach ($xml_items_sub as $item_1_key=>$xml_item_promotion)
                                {

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
                            }
                            elseif($item_key=='messages')
                            {
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


            }
        }
        //print_r($new_cart_structure);
        //die();
        if (isset($new_cart_structure)&&count($new_cart_structure)>0)
        {
            return $new_cart_structure;
        }
    }

    function run_import_promotionsForProducts(){
        
        if (Mage::getStoreConfig('holbi/qixol/enabled') == 0){
            return;
        }
        
        $this->addExportStatus("process", 'promotions', '', 0);

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

            $result = $this->promoService->PromotionsForProducts($promotions);

            if ($result->success) {                  
                if ($result->message != ''){
                    $this->promoService->parsePromotionsForProducts($result->message);
                    $this->addExportStatus("success", 'promotions', 'imported', 1);
                } else {
                    $this->addExportStatus("success", 'promotions', 'imported - no promotions', 1);
                }
            } else {
                $this->addExportStatus("error", 'promotions', addslashes($result->message), 1);
                $this->pushLog("Finish import promotions error ".$result->message);
            }
        } else {
            $this->addExportStatus("process", 'promotions', 'no products found for promotion retrieval', 1);
        }
        return;
    }

    function run_import_promotionsForBaskets(){

        $this->addExportStatus("process", 'basketpromotions' ,'',0);
        $promotions='<request companykey="'.Mage::getStoreConfig('qixol/integraion/companykey').
                    '" validationdate="'.date("Y-m-d",strtotime("+ 1 DAY")).'T00:00:00" channel="'.Mage::getStoreConfig('qixol/syhchronized/channel').
                    '" storegroup="'.Mage::getStoreConfig('qixol/syhchronized/storegroup').'" store="'.Mage::getStoreConfig('qixol/syhchronized/channel').
                    '" validatefortime="false"></request>';

        $result = $this->promoService->PromotionsForBaskets($promotions);

        if ($result->success)
        {
            $this->promoService->parsePromotionsForBaskets($result->message);
            $this->addExportStatus("success", 'basketpromotions', 'imported', 1);
        } else {
            $this->addExportStatus("error", 'basketpromotions', addslashes($result->message), 1);
            $this->pushLog("Finish import promotions error ".$result->message);
        }
        return;
    }

    function run_export_customerGroups() {

        if (Mage::getStoreConfig('holbi/qixol/enabled') == 0){
            return;
        }

        if (Mage::getStoreConfig('qixol/syhchronized/synchcustomer') == 0){
            return;
        }
        
        if ($this->isRunning('customers'))
        {
            return;
        }

        $this->addExportStatus("process", 'customers' ,'',0);

        $list_map_names = Mage::getModel('qixol/Customergrouspmap')->getCollection();

        $group_to_send='';
        foreach ($list_map_names as $list_map){
            $group_to_send .= '<item display="';
            $group_to_send .= $list_map->getCustomerGroupName();
            $group_to_send .= '">';
            $group_to_send .= $list_map->getIntegrationCode();
            $group_to_send .= '</item>';  
        }

        if ($group_to_send != '')
        {
            $data = '<import companykey="';
            $data .= Mage::getStoreConfig('qixol/integraion/companykey');
            $data .= '" attributetoken="customergroup"><items>';
            $data .= $group_to_send;
            $data .= '</items></import>';
          
            $result = $this->promoService->CustomerGroupExport($data);
            if ($result->success)
            {
                $this->addExportStatus("success", 'customers', addslashes($result->message),1);
            }
            else
            {
                $this->addExportStatus("error", 'customers', addslashes($result->message),1);
            }
        }
    }

    function run_export_shippingMethods() {

        if (Mage::getStoreConfig('holbi/qixol/enabled') == 0)
        {
            return;
        }

        if (Mage::getStoreConfig('qixol/syhchronized/synchship') == 0)
        {
            return;
        }
        
        if ($this->isRunning('delivery'))
        {
            return;
        }
        
        $this->addExportStatus("process", 'delivery' ,'',0);

        $list_map_names = Mage::getModel('qixol/Shippingmap')->getCollection();

        $list_map_names_exists=array();

        foreach ($list_map_names as $list_map){
            $list_map_names_exists[$list_map->getShippingName()] = $list_map->getShippingNameMap();
        }

        //$methods = Mage::getSingleton('shipping/config')->getActiveCarriers();
        $methods = Mage::getSingleton('shipping/config')->getAllCarriers();
            
        $selectedgroups = Mage::getStoreConfig('qixol/shippings/list');

        unset($selectedgroups_array);
        
        if(trim($selectedgroups)!='')
        {
            $selectedgroups_array=explode(",",$selectedgroups);
        }

        foreach($list_map_names as $list_map)
        {
            $shipping_to_send .= '<item display="';
            $shipping_to_send .= $list_map->getCarrierTitle() . ': ' . $list_map->getCarrierMethod();
            $shipping_to_send .= '">';
            $shipping_to_send .= $list_map->getIntegrationCode();
            $shipping_to_send .= '</item>';  
        }

        if ($shipping_to_send!='')
        {
            $data='<import companykey="'.Mage::getStoreConfig('qixol/integraion/companykey').'" attributetoken="deliverymethod"><items>'.$shipping_to_send.'</items></import>';
            $result = $this->promoService->ShippingMethodsExport($data);
            if ($result->success)
            {
                $this->addExportStatus("success", 'delivery' ,addslashes($result->message),1);
            }
            else
            {
              $this->addExportStatus("error", 'delivery' ,addslashes($result->message),1);
            }
        }
        else
        {
            $this->addExportStatus("success", 'delivery', 'There are no shipping methods to send', 1);
        }
    }    

    function run_export_currencies() {

        if (Mage::getStoreConfig('holbi/qixol/enabled') == 0){
            return;
        }

        if (Mage::getStoreConfig('qixol/syhchronized/synchcurrency') == 0){
            return;
        }
        
        if ($this->isRunning('currency'))
        {
            return;
        }

        $this->addExportStatus("process", 'currency' ,'',0);
           $currency_to_send='';
           $only_active_currency=Mage::getStoreConfig('currency/options/allow');
           $currencies_array = explode(',',$only_active_currency);
            foreach($currencies_array as $code_curr)
            {
                $currency_to_send .= '<item display="';
                $currency_to_send .= Mage::app()->getLocale()->currency( $code_curr )->getName();
                $currency_to_send .= '">';
                $currency_to_send .= $code_curr;
                $currency_to_send .= '</item>';  
            }

          if ($currency_to_send != '')
          {
            $data = '<import companykey="';
            $data .= Mage::getStoreConfig('qixol/integraion/companykey');
            $data .= '" attributetoken="currencycode"><items>';
            $data .= $currency_to_send;
            $data .= '</items></import>';
            
            $result = $this->promoService->CurrenciesExport($data);
            if ($result->success)
            {
              $this->addExportStatus("success", 'currency', addslashes($result->message), 1);
            }
            else {
              $this->addExportStatus("error", 'currency', addslashes($result->message), 1);
            }
          } else {
              $this->addExportStatus("success", 'currency', 'No currencies to send', 1);
          }
    }

    function run_export_stores() {

        if (Mage::getStoreConfig('holbi/qixol/enabled') == 0){
            return;
        }

        if (Mage::getStoreConfig('qixol/syhchronized/synchstores') == 0){
            return;
        }

        if ($this->isRunning('store'))
        {
            return;
        }

        $this->addExportStatus("process", 'store' ,'',0);

        $list_store_integration_codes = Mage::getModel('qixol/Storesmap')->getCollection();

        foreach ($list_store_integration_codes as $list_map)
        {
            $list_store_integration_codes_exists[$list_map->getWebsite()][$list_map->getStoreGroup()][$list_map->getStoreName()]=$list_map->getIntegrationCode();
        }

        $xmlToSend = '';
        
        foreach (Mage::app()->getWebsites() as $website)
        {
            $xmlChannel = '<channel display="';
            $xmlChannel .= $website->getName();
            $xmlChannel .= '" value="';
            $xmlChannel .= $website->getName();
            $xmlChannel .= '">';
            
            $hasStoreGroups = false;
            foreach ($website->getGroups() as $group) {
                $xmlStoreGroup = '<storegroup display="';
                $xmlStoreGroup .= $group->getName();
                $xmlStoreGroup .= '" value="';
                $xmlStoreGroup .= $group->getName();
                $xmlStoreGroup .= '">';
                
                $stores = $group->getStores();

                $hasStores = false;
                foreach ($stores as $store) {
                    $integrationCode = $list_store_integration_codes_exists[$website->getName()][$group->getName()][$store->getName()];
                    if (!empty($integrationCode))
                    {
                        $hasStores = true;
                        $xmlStore .= '<store display="';
                        $xmlStore .= $store->getName();
                        $xmlStore .= '" value="';
                        $xmlStore .= $integrationCode;
                        $xmlStore .= '" />';  
                    }
                }
                if ($hasStores)
                {
                    $hasStoreGroups = true;
                    $xmlStoreGroup .= $xmlStore;
                }
                
                $xmlStoreGroup .= '</storegroup>';
            }
            
            if ($hasStoreGroups)
            {
                $xmlChannel .= $xmlStoreGroup;
                $xmlChannel .= '</channel>';
                
                $xmlToSend .= $xmlChannel;
            }
        }
    

        if ($xmlToSend != '')
        {
            $data = '<import companykey="';
            $data .= Mage::getStoreConfig('qixol/integraion/companykey');
            $data .= '" hierarchytoken="store">';
            $data .= $xmlToSend;
            $data .= '</import>';
            
            $result =$this->promoService->StoresExport($data);
            if ($result->success)
            {
                $this->addExportStatus("success", 'store', addslashes($result->message), 1);
            }
            else
            {
                $this->addExportStatus("error", 'store', addslashes($result->message), 1);
            }
        }
        else
        {
            $this->addExportStatus("success", 'store', 'There are no stores to send', 1);
        }
    }

    function run_export_products() {

        if (Mage::getStoreConfig('holbi/qixol/enabled')==0) {
            return;
        }
      
        if (Mage::getStoreConfig('qixol/syhchronized/synchproducts')==0) {
            return;
        }
      
        if ($this->isRunning('products'))
        {
            return;
        }
      
        $write_data = Mage::getSingleton('core/resource')->getConnection('core_write');
        $write_data->query("
            delete from ".$this->process_export_status_table." where export_what='products'
        ");

        //process products here
        $number_products_exported=0;
        $this->addExportStatus("process", 'products' ,'',0);

        $products_list = Mage::getModel('catalog/product')->getCollection()->addAttributeToSelect('*')->addAttributeToFilter('visibility', array('neq'=>1))->addAttributeToSort('entity_id', 'desc'); 
        
        $products_deleted = Mage::getModel('qixol/Deletedproduct')->getCollection();
        $remove_deleted=array();

        if (count($products_list)||count($products_deleted)){
            $data = '<import companykey="'.Mage::getStoreConfig('qixol/integraion/companykey').'"><products>';

            //assign deleted product first
            if (count($products_deleted))
                foreach ($products_deleted as $products_deleted_data) {
                    $data .= '<product productcode="'.$products_deleted_data->getData('product_sku').'" variantcode="'.$products_deleted_data->getData('child_sku').'" barcode="" deleted="true"></product>';
                    $remove_deleted[]=$products_deleted_data->getData('entity_id');                
                }

                if (count($products_list))
                foreach ($products_list as $product) {
                    $send_product=true;
                    $imageUrl = $this->CDT($product->getImage() != 'no_selection' ? $product->getImageUrl() : '');
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
                        {
                            foreach ($parentId as  $parent_product_id){
                                $prod_parent_obj = Mage::getModel('catalog/product')->load($parent_product_id);
                                //do not send if this product exists as child in any active parent product
                                if (strtolower($prod_parent_obj->getAttributeText('status'))=='enabled'&&$prod_parent_obj->getVisibility()>1){
                                    $send_product=false;
                                    break;
                                }
                            }
                            $parentProduct = Mage::getModel('catalog/product')->load($parentId[0]);
                            if ($imageUrl == '') {
                            $imageUrl = $parentProduct->getImageUrl();
                            }
                        }
                    }
                    if  ($product->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_BUNDLE) {
                        $send_product = false;
                    }
                    if ($product->getPrice() == '') {
                        $send_product = false;
                    }
                    if ($send_product){
                        $productCategories = $product->getCategoryIds();
                        if (!$product->isConfigurable()) {
                            $productCode = $product->getSku();
                            $variantCode = '';
                            $price = $product->getPrice();
                            $description = $this->CDT($product->getName());

                            $data .= $this->productXmlElement($product, $productCode, $variantCode, $price, $description, $imageUrl, $productCategories);
                            $number_products_exported++;

                        } else {
                            //$associatedAttributes = $product->getTypeInstance()->getConfigurableAttributesAsArray($product);    
                            $childs_products_list=$product->getTypeInstance()->getUsedProducts();
                            if (empty($childs_products_list) || (count($childs_products_list) == 0)) {
                                $productCode = $product->getSku();
                                $variantCode = '';
                                $price = $product->getPrice();
                                $description = $this->CDT($product->getName());

                                $data .= $this->productXmlElement($product, $productCode, $variantCode, $price, $description, $imageUrl, $productCategories);
                                $number_products_exported++;
                            } else {
                                foreach ($childs_products_list as $childProduct_tmp) {                           
                                    $childProduct = Mage::getModel('catalog/product')->load($childProduct_tmp->getId());
                                    $productCode = $product->getSku();
                                    $variantCode = $childProduct->getSku();
                                    $price = $childProduct->getPrice();
                                    $description = $product->getName(); // $this->CDT($childProduct->getName());
                                    $image = $childProduct->getImage();
                                    if ($image == NULL) {
                                        $image = 'no_selection';
                                    }
                                    $imageUrl = ($image != 'no_selection' ? $childProduct->getImageUrl() : '');

                                    $data .= $this->productXmlElement($product, $productCode, $variantCode, $price, $description, $imageUrl, $productCategories);

                                    $number_products_exported++;
                                }
                            }
                        }
                    }
                }

            $data .= '</products></import>';
            if ($data!=''){
                $result = $this->promoService->ProductsExport($data);
                
                if ($result->success)
                {
                    $this->addExportStatus("success", 'products', addslashes($result->message), 1);
                    if (is_array($remove_deleted)&&count($remove_deleted)>0)
                    {
                        $write_data = Mage::getSingleton('core/resource')->getConnection('core_write');
                        $write_data->query("delete from qixol_product_to_delete where entity_id in (".join(",",$remove_deleted).")");
                    }
                }
                else {
                    $this->addExportStatus("error", 'products', addslashes($result->message), 1);
                }
            }
            else
            {
                $this->addExportStatus("success", 'products', 'no products to send', 1);
            }
        }
    }
    
    function attributeXmlElement($product, $productCategories) {
        $attributes=Mage::getStoreConfig('qixol/productattrib/attributes');
        $attributeXmlElement = '';
        $attributes_arr=explode(",",$attributes);
        if (count($attributes_arr)){
            foreach ($attributes_arr as $attribute_id){
                $attribute = $product->getResource()->getAttribute($attribute_id);
                if ($attribute != NULL) {
                    $is_attribute_text_value = $attribute->getFrontend()->getValue($product);
                    $attributeXmlElement .= '<attribute>';
                    $attributeXmlElement .= '<name>'.$attribute_id.'</name>';
                    $attributeXmlElement .= '<value>'.$this->CDT($is_attribute_text_value!=''?$is_attribute_text_value:$product->getData($attribute_id)).'</value>';
                    $attributeXmlElement .= '</attribute>';
                }
            }
        }
        if (Mage::getStoreConfig('qixol/syhchronized/synchcatproducts')>0){
            foreach ($productCategories as $product_category_id){
                if ($product_category_id==0) continue;
                $current_ctaegory_id=$product_category_id;                     
                $category_name_push='';
                while($current_ctaegory_id != 0){
                    $category = Mage::getModel('catalog/category')->load($current_ctaegory_id);
                    $current_ctaegory_id=$category->getParentId();
                    if (strtolower($category->getName())!='root catalog') {
                        $category_name_push=$category->getName().($category_name_push!=''?" / ".$category_name_push:"");
                    }
                }
                $attributeXmlElement .= '<attribute>';
                $attributeXmlElement .= '<name>categorycode</name>';
                $attributeXmlElement .= '<value>'.$this->CDT($category_name_push).'</value>';
                $attributeXmlElement .= '</attribute>';
            }
        }                

        if (!empty($attributeXmlElement)) {
            $attributeXmlElement = '<attributes>'.$attributeXmlElement.'</attributes>';
        }
        return $attributeXmlElement;
    }
    
    function productXmlElement($product, $productCode, $variantCode, $price, $description, $imageUrl, $productCategories) {
        $productXmlElement = '<product productcode="'.$productCode.'" variantcode="'.$variantCode.'"';
        $productXmlElement .= ' barcode="" price="'.$price.'"><description>'.$description.'</description>';
        $productXmlElement .= '<imageurl>'.$imageUrl.'</imageurl>';
        $productXmlElement .= $this->attributeXmlElement($product, $productCategories);
        $productXmlElement .= '</product>';
        
        return $productXmlElement;
    }
    
    function run_export_qixolData(){

        if (Mage::getStoreConfig('holbi/qixol/enabled') == 0){
            return;
        }

        $this->run_export_customerGroups();
        $this->run_export_shippingMethods();
        $this->run_export_currencies();
        $this->run_export_stores();
        $this->run_export_products();
    }

    function CDT($in_str)
    {
      return "<![CDATA[".$in_str."]]>";
    }   

    public function getExportStatus($for='products'){
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

    public function addExportStatus($message, $for ,$extended_message='',$finished=0){
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

    function isRunning($exportType)
    {
        $current_state=$this->getExportStatus($exportType);
        
        if ($current_state['id']==0) return false;
        if ($current_state['finished'] == 1) return false;
        if (strtotime($current_state['last_updated'])<strtotime("-1 hour")) return false;

        return true;
    }
}
