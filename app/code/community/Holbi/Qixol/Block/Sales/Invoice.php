<?php
//????????????????
class Holbi_Qixol_Block_Adminhtml_Sales_Invoice extends Mage_Sales_Block_Order_Invoice_Totals
{
 protected function _initTotals() {
        parent::_initTotals();
        $amt = $this->getSource()->getPointsAmount();
        $baseAmt = $this->getSource()->getBasePointsAmount();
        if ($amt != 0) {
            $this->addTotal(new Varien_Object(array(
                        'code' => 'points',
                        'value' => $amt,
                        'base_value' => $baseAmt,
                        'label' => 'Reward Points Discount',
                    )), 'points');
        }
        return $this;
    }			
	
}