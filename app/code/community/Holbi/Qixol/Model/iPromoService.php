<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 *
 * @author ken
 */
interface iPromoService
{
    function CustomerGroupExport($data);
    function ShippingMethodsExport($data);
    function CurrenciesExport($data);
    function StoresExport($data);
    function ProductsExport($data);
    
    function PromotionsForProducts($data);
    function PromotionsForBaskets($data);
    function CouponCodesImport();
    
    function BasketValidate($data);
    function CouponCodeValidate($couponCode);
    function RetrieveValidatedBasket($basketRef);
    function BasketCheck();
}
