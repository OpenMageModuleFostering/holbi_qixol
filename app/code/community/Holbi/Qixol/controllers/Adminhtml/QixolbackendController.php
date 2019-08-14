<?php
class Holbi_Qixol_Adminhtml_QixolbackendController extends Mage_Adminhtml_Controller_Action
{
	public function indexAction()
    {
     $this->loadLayout();
     $this->_setActiveMenu('qixol/items');
	   $this->_title($this->__("Qixol"));
	   $this->renderLayout();
    }
}