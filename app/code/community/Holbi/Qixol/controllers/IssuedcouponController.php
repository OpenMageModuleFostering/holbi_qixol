<?php

class Holbi_Qixol_IssuedcouponController extends Mage_Core_Controller_Front_Action {

    public function indexAction() {
        if (!Mage::getSingleton('customer/session')->isLoggedIn()) {
            $this->_redirect('customer/account/login');
            return;
        }
        $this->loadLayout();
        $this->_initLayoutMessages('customer/session');
        $this->_initLayoutMessages('catalog/session');

        $this->renderLayout();
    }

}
