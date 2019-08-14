<?php
class Holbi_Qixol_Model_Ordertotalpoints extends Mage_Sales_Model_Quote_Address_Total_Abstract {
 
     protected $_code = 'points';
 
    public function collect(Mage_Sales_Model_Quote_Address $address) {
			parent::collect($address);

			$this->_setAmount(0);
			$this->_setBaseAmount(0);

			$items = $this->_getAddressItems($address);
			if (!count($items)) {
				return $this; //this makes only address type shipping to come through
			}

			$quote = $address->getQuote();

      if(true/*$address->getData('address_type') == 'billing'*/){
        $points_convertrate=1;
        if ((int)Mage::getStoreConfig('qixol/issuedpoints/convertrate')>0)
          $points_convertrate=(int)Mage::getStoreConfig('qixol/issuedpoints/convertrate');

        $exist_amount = $quote->getPointsAmount()*$points_convertrate;
        $new_points_amount = Mage::getSingleton('customer/session')->getPointsAmount(); //your discount
        $balance = $new_points_amount - $exist_amount;
 
        $address->setPointsAmount($new_points_amount/$points_convertrate);
        $address->setBasePointsAmount($new_points_amount/$points_convertrate);


        $grandTotal = $address->getGrandTotal();
        $baseGrandTotal = $address->getBaseGrandTotal();
        $quote->setFeeAmount($new_points_amount/$points_convertrate);
          
        $address->setGrandTotal($grandTotal - ($balance/$points_convertrate));
        $address->setBaseGrandTotal($baseGrandTotal - ($balance/$points_convertrate));				
        }                
        return $this;
    }
	
	public function fetch(Mage_Sales_Model_Quote_Address $address)
    {
	    $rewards1 = $address->getPointsAmount();
        if ($rewards1!=0) {
            $address->addTotal(array(
                'code'=>$this->getCode(),
                'title'=>Mage::helper('qixol')->__('Reward Points Discount'),
                'value'=>"-".$rewards1
            ));
			
        }
        return $this;
    }
 
}