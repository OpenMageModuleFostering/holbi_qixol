<?php
class Holbi_Qixol_Model_Cron{	
	public function runExportSynch(){
		   $export=Mage::getModel('qixol/sinch');
      //process export here
       //export to qixol
       $export->cron_run_export();
	} 

  public function runImportProductPromotionSynch(){
       $export=Mage::getModel('qixol/sinch');
      //process export here
       //export products
       $export->cron_run_import_promo();
  } 
}