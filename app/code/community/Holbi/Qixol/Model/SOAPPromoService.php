<?php

require_once ('config.php');
require_once('PromoService.php');

class SOAPPromoService extends PromoService implements iPromoService
{

    private function importServiceUrl() {
        $evaluationImportServicesUrl = 'http://evaluation.qixolpromo.com/ImportService.svc';
        $liveImportServicesUrl = 'http://evaluation.qixolpromo.com/ImportService.svc';

        switch (Mage::getStoreConfig('qixol/integraion/services')) {
              case 'evaluation':
                $importServiceUrl = $evaluationImportServicesUrl;
                  break;
              case 'live':
                $importServiceUrl = $liveImportServicesUrl;
                  break;
              case 'custom':
                  $importServiceUrl = Mage::getStoreConfig('qixol/integraion/importManagerServiceAddress');
                  break;
              default:
                $importServiceUrl = $evaluationImportServicesUrl;
                break;
          }
        return $importServiceUrl;
    }
    
    private function exportServiceUrl() {
        $evaluationExportServicesUrl = 'http://evaluation.qixolpromo.com/ExportService.svc';
        $liveExportServicesUrl = 'http://evaluation.qixolpromo.com/ExportService.svc';

        switch (Mage::getStoreConfig('qixol/integraion/services')) {
              case 'evaluation':
                $exportServiceUrl = $evaluationExportServicesUrl;
                  break;
              case 'live':
                $exportServiceUrl = $liveExportServicesUrl;
                  break;
              case 'custom':
                  $exportServiceUrl = Mage::getStoreConfig('qixol/integraion/exportManagerServiceAddress');
                  break;
              default:
                $exportServiceUrl = $evaluationExportServicesUrl;
                break;
          }
        return $exportServiceUrl;
    }

    private function basketServiceUrl() {

        $evaluationBasketServicesUrl = 'http://evaluation.qixolpromo.com/BasketService.svc';
        $liveBasketServicesUrl = 'http://evaluation.qixolpromo.com/BaskettService.svc';

        switch (Mage::getStoreConfig('qixol/integraion/services')) {
          case 'evaluation':
            $basketServiceUrl = $evaluationBasketServicesUrl;
              break;
          case 'live':
            $basketServiceUrl = $liveBasketServicesUrl;
              break;
          case 'custom':
              $basketServiceUrl = Mage::getStoreConfig('qixol/integraion/basketManagerServiceAddress');
              break;
          default:
            $basketServiceUrl = $evaluationBasketServicesUrl;
            break;
      }
        return $basketServiceUrl;
    }
    
    function CustomerGroupExport($data)
    {
        $soapclient = new soapclient($this->importServiceUrl().'?singleWsdl', array(  'trace'     => 1,
                                                                                      'location'  => $this->importServiceUrl()));
        $types_array = $soapclient->__getTypes();
        $functions_array = $soapclient->__getFunctions();

        try {
            $result = $soapclient->__soapCall('ImportEntityAttributeValues', array('ImportEntityAttributeValues' => array('xmlToImport' => $data)));
            $returnValue->message = $result->ImportEntityAttributeValuesResult;
            $returnValue->success = true;
            return $returnValue;
        } catch (SoapFault $e) {
            $returnValue->message = $e->faultstring;
            $returnValue->success = false;
            return $returnValue;
        }
    }
    
    function ShippingMethodsExport($data)
    {
        $soapclient = new soapclient($this->importServiceUrl().'?singleWsdl', array(  'trace'     => 1,
                                                                                      'location'  => $this->importServiceUrl()));
        $types_array = $soapclient->__getTypes();
        $functions_array = $soapclient->__getFunctions();
        try {
          $result = $soapclient->__soapCall('ImportEntityAttributeValues', array('ImportEntityAttributeValues' => array('xmlToImport' => $data)));
            $returnValue->message = $result->ImportEntityAttributeValuesResult;
            $returnValue->success = true;
            return $returnValue;
        } catch (SoapFault $e) {
            $returnValue->message = $e->faultstring;
            $returnValue->success = false;
            return $returnValue;
        }
    }

    function CurrenciesExport($data)
    {
        $soapclient = new soapclient($this->importServiceUrl().'?singleWsdl', array(  'trace'     => 1,
                                                                                      'location'  => $this->importServiceUrl()));
        $types_array = $soapclient->__getTypes();
        $functions_array = $soapclient->__getFunctions();
        try {
            $result = $soapclient->__soapCall('ImportEntityAttributeValues', array('ImportEntityAttributeValues' => array('xmlToImport' => $data)));
            $returnValue->message = $result->ImportEntityAttributeValuesResult;
            $returnValue->success = true;
            return $returnValue;
        } catch (SoapFault $e) {
            $returnValue->message = $e->faultstring;
            $returnValue->success = false;
            return $returnValue;
        }
    }

    function StoresExport($data)
    {
        $soapclient = new soapclient($this->importServiceUrl().'?singleWsdl', array(  'trace'     => 1,
                                                                                      'location'  => $this->importServiceUrl()));
        $types_array = $soapclient->__getTypes();
        $functions_array = $soapclient->__getFunctions();
        try {
            $result = $soapclient->__soapCall('ImportEntityAttributeValues', array('ImportEntityAttributeValues' => array('xmlToImport' => $data)));
            $returnValue->message = $result->ImportEntityAttributeValuesResult;
            $returnValue->success = true;
            return $returnValue;
        } catch (SoapFault $e) {
            $returnValue->message = $e->faultstring;
            $returnValue->success = false;
            return $returnValue;
        }

    }
    
    function ProductsExport($data)
    {
        $soapclient = new soapclient($this->importServiceUrl().'?singleWsdl', array(  'trace'     => 1,
                                                                                            'location'  => $this->importServiceUrl()));
        $types_array = $soapclient->__getTypes();
        $functions_array = $soapclient->__getFunctions();
            try {
            $result = $soapclient->__soapCall('ImportProducts', array('ImportProducts' => array('xmlToImport' => $data)));
            $returnValue->message = $result->ImportProductsResult;
            $returnValue->success = true;
            return $returnValue;
        } catch (SoapFault $e) {
            $returnValue->message = $e->faultstring;
            $returnValue->success = false;
            return $returnValue;
        }
    }
    
    public function PromotionsForProducts($data)
    {
        $soapclient = new soapclient($this->exportServiceUrl().'?singleWsdl', array(    'trace'     => 1,
                                                                                        'location'  => $this->exportServiceUrl()));
        try {
            $result = $soapclient->__soapCall('RetrievePromotionsForProducts', array('RetrievePromotionsForProducts' => array('xmlExportRequest' => $data)));
            $returnValue->message = $result->RetrievePromotionsForProductsResult;
            $returnValue->success = true;
            return $returnValue;
        } catch (SoapFault $e) {
            $returnValue->message = $e->faultstring;
            $returnValue->success = false;
            return $returnValue;
        }
    }

    public function PromotionsForBaskets($data)
    {
        $soapclient = new soapclient($this->exportServiceUrl().'?singleWsdl', array(  'trace'     => 1,
                                                                              'location'  => $this->exportServiceUrl()));

        try
        {
            $result = $soapclient->__soapCall('RetrievePromotionsForBaskets', array('RetrievePromotionsForBaskets' => array('xmlExportRequest' => $data)));
            $returnValue->message = $result->RetrievePromotionsForBasketsResult;
            $returnValue->success = true;
            return $returnValue;
        }
        catch (SoapFault $e)
        {
            $returnValue->message = $e->faultstring;
            $returnValue->success = false;
            return $returnValue;
        }
    }
    
    public function CouponCodesImport()
    {
        $returnValue->message = 'NOT IMPLEMENTED';
        $returnValue->success = false;
        return $returnValue;
    }
    
    public function BasketValidate($data)
    {
        $soapclient = new soapclient($this->basketServiceUrl().'?singleWsdl', array(    'trace' => 1,
                                                                          'location' => $this->basketServiceUrl()));

        try
        {
            $result = $soapclient->__soapCall('ValidateBasket', array('ValidateBasket' => array('basketXml' => $data)));
            $returnValue->message = $result->ValidateBasketResult;
            $returnValue->success = true;
            return $returnValue;
        } catch (SoapFault $e) {
            $message = $e->faultstring . '\n';
            $message .= "REQUEST:\n" . $soapclient->__getLastRequestHeaders();
            $message .= $soapclient->__getLastRequest() . "\n";
            $message .= "RESPONSE:\n" . $soapclient->__getLastResponseHeaders();
            $message .= $soapclient->__getLastResponse() . "\n";

            $returnValue->message = $message;
            $returnValue->success = false;
            return $returnValue;
        }
    }

    public function CouponCodeValidate($couponCode)
    {
        $soapclient_coupon = new soapclient($this->basketServiceUrl().'?singleWsdl', array( 'trace'     => 1,
                                                                                'location'  => $this->basketServiceUrl()));
        
        try
        {
            $soapclient_coupon->__soapCall('ValidateCouponCode', array('ValidateCouponCode' =>
                                                                array('companyKey' =>
                                                                    Mage::getStoreConfig('qixol/integraion/companykey'),
                                                                'couponCode' => $couponCode)));
            $returnValue->message = $result->ValidateCouponCodeResult;
            $returnValue->success = true;
            return $returnValue;
        } catch (SoapFault $e) {
            //$message = $e->faultstring . '\n';
            //$message .= "REQUEST:\n" . $soapclient->__getLastRequestHeaders();
            //$message .= $soapclient->__getLastRequest() . "\n";
            //$message .= "RESPONSE:\n" . $soapclient->__getLastResponseHeaders();
            //$message .= $soapclient->__getLastResponse() . "\n";

            $returnValue->message = $message;
            $returnValue->success = false;
            return $returnValue;
        }
        
    }
    
    public function RetrieveValidatedBasket($basketRef)
    {
        $returnValue->message = 'NOT IMPLEMENTED';
        $returnValue->success = false;
        return $returnValue;
    }
    
    public function BasketCheck()
    {
        $returnValue->message = 'NOT IMPLEMENTED';
        $returnValue->success = false;
        return $returnValue;
    }
}
