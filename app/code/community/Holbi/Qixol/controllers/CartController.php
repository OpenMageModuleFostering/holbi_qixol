<?php
require_once 'Mage/Checkout/controllers/CartController.php';
class Holbi_Qixol_CartController extends Mage_Checkout_CartController {

    public function applypointsAction() {
       global $_SESSION;
       $_Cart=Mage::getModel('checkout/cart');
       $session = Mage::getSingleton('checkout/session');
        if (!$_Cart->getQuote()->getItemsCount()) {
            $session->addError($this->__('Emopty shopping cart!.'));
            $this->_redirect('checkout/cart');
            return;
        }
        if ($this->getRequest()->getParam('removepoints') == 1) {
           $points_amount='';
           Mage::getSingleton('customer/session')->setPointsAmount($points_amount);
           $session->addSuccess($this->__('Points was canceled.'));
        }
       else {
            $points_amount = (string) $this->getRequest()->getParam('reward_points');
            if ((int)Mage::getStoreConfig('qixol/issuedpoints/convertrate')>0)
            $points_amount_money=$points_amount/(int)Mage::getStoreConfig('qixol/issuedpoints/convertrate');
            $customerId = Mage::getSingleton('customer/session')->getCustomerId();
            if ($customerId==0) {
                $session->addError($this->__('Login First.'));
            } else {

              $validtotal = $_Cart->getQuote()->getSubtotal();
              $bonuspoints =  Mage::getModel('qixol/bonuspoints')->load($customerId);
              $leftpoints = $bonuspoints->getCurrentPoints();

              if (isset($_SESSION['qixol_quoted_items']['cart_data']['deliverytotaldiscount'])&&$_SESSION['qixol_quoted_items']['cart_data']['deliverytotaldiscount']>0){
                            $validtotal-=$_SESSION['qixol_quoted_items']['cart_data']['deliverytotaldiscount'];
              }
              if (isset($_SESSION['qixol_quoted_items']['cart_data']['totaldiscount'])&&$_SESSION['qixol_quoted_items']['cart_data']['totaldiscount']>0){
                            $validtotal-=$_SESSION['qixol_quoted_items']['cart_data']['totaldiscount'];
              }
              if ((double)$leftpoints>=(double)$points_amount_money&&(double)$points_amount_money<$validtotal){
                    Mage::getSingleton('customer/session')->setPointsAmount($points_amount);
                    $session->addSuccess(
                        $session->addSuccess('Reward Points "'.Mage::helper('core')->escapeHtml($points_amount).'" was applied.')
                    );


              }elseif((int)$leftpoints<(int)$points_amount) {
                  $session->addError($this->__('Not enouhgt points!'));
              }else{
                    $session->addError('reward Points "'.Mage::helper('core')->escapeHtml($points_amount).'" is not valid.');
              }

           }
          }
            $this->_redirect('checkout/cart');
            return;
    }

}