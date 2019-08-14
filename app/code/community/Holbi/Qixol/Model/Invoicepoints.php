<?php
class Holbi_Qixol_Model_Invoicepoints extends Mage_Sales_Model_Order_Invoice_Total_Abstract {
 
    public function collect(Mage_Sales_Model_Order_Invoice $invoice) {
        echo "call Holbi_Qixol_Model_Invoicepoints";
die();
        $invoice->setGrandTotal($invoice->getGrandTotal() - $invoice->getPointsAmount());
        $invoice->setBaseGrandTotal($invoice->getBaseGrandTotal() - $invoice->getBasePointsAmount());
				
        /*
        //?????????????????????????
        $invoice->setPointsAmount($invoice->getPointsAmount());
        $invoice->setBasePointsAmount($invoice->getBasePointsAmount());
*/
        //Mage::log('invoice11: ');
        return $this;
		
    }
 
}
