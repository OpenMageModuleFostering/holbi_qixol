<?php 
class Holbi_Qixol_AjaxController extends Mage_Adminhtml_Controller_Action
{
    var $_logFile;

    public function UpdateStatusAction() {
        $_hlp=Mage::helper('qixol');
        $export=Mage::getModel('qixol/sinch');
        $message_output='';
        $finished=1;

        $tmp_data=$export->getExportStatus('customers');
        if ($tmp_data['id']>0){
          $message_output.='<strong>'.$_hlp->__('Customers').'</strong>:'.$_hlp->__($tmp_data['extended_message']!=''?$tmp_data['extended_message']:$tmp_data['last_message'])."<br><br><hr>";
          $finished=$tmp_data['finished'];
        } else {
          $message_output.='<strong>'.$_hlp->__('Customers').'</strong>:Not started<br><br><hr>';
        }

        $tmp_data=$export->getExportStatus('delivery');
        if ($tmp_data['id']>0){
          $message_output.='<strong>'.$_hlp->__('Shippings').'</strong>:'.$_hlp->__($tmp_data['extended_message']!=''?$tmp_data['extended_message']:$tmp_data['last_message'])."<br><br><hr>";
          $finished=$tmp_data['finished'];
        } else {
          $message_output.='<strong>'.$_hlp->__('Shippings').'</strong>:Not started<br><br><hr>';
        }

        $tmp_data=$export->getExportStatus('products');
        if ($tmp_data['id']>0){
          $message_output.='<strong>'.$_hlp->__('Products').'</strong>:'.$_hlp->__($tmp_data['extended_message']!=''?$tmp_data['extended_message']:$tmp_data['last_message'])."<br><br><hr>";
          $finished=$tmp_data['finished'];
        } else {
          $message_output.='<strong>'.$_hlp->__('Products').'</strong>:Not started<br><br><hr>';
        }

        $tmp_data=$export->getExportStatus('currency');
        if ($tmp_data['id']>0){
          $message_output.='<strong>'.$_hlp->__('Currency').'</strong>:'.$_hlp->__($tmp_data['extended_message']!=''?$tmp_data['extended_message']:$tmp_data['last_message'])."<br><br><hr>";
          $finished=$tmp_data['finished'];
        } else {
          $message_output.='<strong>'.$_hlp->__('Currency').'</strong>:Not started<br><br><hr>';
        }

        $tmp_data=$export->getExportStatus('store');
        if ($tmp_data['id']>0){
          $message_output.='<strong>'.$_hlp->__('Stores').'</strong>:'.$_hlp->__($tmp_data['extended_message']!=''?$tmp_data['extended_message']:$tmp_data['last_message'])."<br><br><hr>";
          $finished=$tmp_data['finished'];
        } else {
          $message_output.='<strong>'.$_hlp->__('Stores').'</strong>:Not started<br><br><hr>';
        }

        if ($message_output!='') {
            // JSON
             print '{ "message": "'.$_hlp->__((string)$message_arr['message']).'","extmessage": "'.addslashes(nl2br((string)$message_output)).'", "finished": "'.(int)$finished.'"}';
        }
        else{
            print '{ "message": "", "finished": "0"}';
        }

        return;
    }


    public function UpdateImportStatusAction() {

        $_hlp=Mage::helper('qixol');

        $import=Mage::getModel('qixol/sinch');
        $message_output='';
        $finished=1;

        $tmp_data=$import->getExportStatus('promotions');
        if ($tmp_data['id']>0){
          $message_output.="<strong>".$_hlp->__('Promotions')."</strong>".$_hlp->__(':Imported')."<br><br><hr>";
          $finished=$tmp_data['finished'];
        } else $finished=0;

        if ($message_output!='') {
            // JSON
             print '{ "message": "'.$_hlp->__('Done').'","extmessage": "'.addslashes(nl2br((string)$message_output)).'", "finished": "'.(int)$finished.'"}';
        }
        else{
            print '{ "message": "", "finished": "0"}';
        }

        return;
    }

    public function indexAction(){
        $export=Mage::getModel('qixol/sinch');
        //echo "Start export <br>";
        //$dir = dirname(__FILE__);
        $export->run_export();
        //exec("nohup ".$sinch->php_run_string.$dir."/../sinch_import_start_ajax.php > /dev/null & echo $!");
        //echo "Finish export<br>";

    }

    public function ExportProductAction(){
        $export=Mage::getModel('qixol/sinch');
        //echo "Start export <br>";
        //$dir = dirname(__FILE__);
        $export->run_export();
        //exec("nohup ".$sinch->php_run_string.$dir."/../sinch_import_start_ajax.php > /dev/null & echo $!");
        //echo "Finish export<br>";

    }

    public function ImportPromotionAction(){
        $export=Mage::getModel('qixol/sinch');
        //echo "Start export <br>";
        //$dir = dirname(__FILE__);
        $export->run_import();
        //exec("nohup ".$sinch->php_run_string.$dir."/../sinch_import_start_ajax.php > /dev/null & echo $!");
        //echo "Finish export<br>";

    }

}  
    ?>
