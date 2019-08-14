<?php 
class Holbi_Qixol_IndexController extends Mage_Adminhtml_Controller_Action
{
    /*var $_logFile;

    public function indexAction(){

        echo "Start export <br>";

        $export=Mage::getModel('qixol/sinch');
        
        $export->run_export();
  
        echo "Finish export<br>";

    }*/
    public function indexAction() {
        $this->loadLayout();
        $this->renderLayout();
    }

} 