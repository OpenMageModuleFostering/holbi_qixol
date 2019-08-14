<?php
class Holbi_Qixol_Block_Startimportbutton extends Mage_Adminhtml_Block_System_Config_Form_Field
{

    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $this->setElement($element);
        $_hlp = Mage::helper('qixol');
        $html = $this->AddJs();

        $html .= '<div id="qixolimport_status_template" name="qixolimport_status_template" style="display:none">';//none
        $html .= $this->getStatusTemplateHtml();
        $html .= '</div>';

        $start_import_button = $this->getLayout()->createBlock('adminhtml/widget_button')
            ->setType('button')
            ->setClass('scalable')
            ->setLabel('Import now')
            ->setOnClick("start_promotion_import()")
            ->toHtml();
        $safe_mode_set = ini_get('safe_mode');
        if($safe_mode_set){
            $html .="<p class='sinch-error'><b>You can't start import (safe_mode is 'On'. set safe_mode = Off in php.ini )<b></p>";
        } else {
            $html .= $start_import_button;    
        }

        $import=Mage::getModel('qixol/sinch');
        $last_import=$import->getDataOfLatestExport();
        $last_exp_status=$last_import['last_message'];
        if($last_exp_status=='error'){
            $html.='<div id="import_current_status_message" name="import_current_status_message" style="display:true"><br><br><hr/><p class="sinch-error">The import has failed.<br> Error reporting "'.$last_import['import_what'].'": "'.$last_import['status_import_message'].'"</p></div>';
        }elseif($last_imp_status=='success'){
            $html.='<div id="import_current_status_message" name="import_current_status_message" style="display:true"><br><br><hr/><p class="sinch-success">Data imported succesfully!</p></div>';
        }elseif($last_imp_status=='process'){
            $html.='<div id="import_current_status_message" name="import_current_status_message" style="display:true"><br><br><hr/><p>Export is running now</p></div>';
        }else{
            $html.='<div id="import_current_status_message" name="import_current_status_message" style="display:true"></div>';
        }

        return $html;        
    }

    protected function getStatusTemplateHtml()
    {
        $_hlp = Mage::helper('qixol');
        $run_pic=Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_SKIN)."adminhtml/default/default/images/_run.gif";
        $html="
           <ul> 
            <li>
               Start import
               &nbsp
               <span id='qixolimport_process'> 
                <img src='".$run_pic."'
                 alt='".$_hlp->__('promotion import run')."' /> 
               </span> 
            </li>   
              <!--li>
               Import finished   
               &nbsp
               <span id='qixolimport_import_done'>  
                <img src='".$run_pic."'
                 alt='".$_hlp->__('Import finished')."' /> 
               </span> 
            </li-->   

           </ul>
        ";
        return $html;
    }

    protected function AddJs()
    {
        $post_url=$this->getUrl('qixol_admin/ajax/ImportPromotion');
        $post_url_upd=$this->getUrl('qixol_admin/ajax/UpdateImportStatus');
        $html = "
        <script>
            function start_promotion_import(){
                      set_run_icon();
                    status_data=document.getElementById('qixolimport_status_template');   
                    curr_status_data=document.getElementById('import_current_status_message'); 
                    curr_status_data.style.display='none';
                    status_data.style.display='';
//                    status_data.innerHTML='';
                    importpromo = new Import_promo('$post_url','$post_url_upd');
                    importpromo.startPromotionImport();

                    //
            }
      function set_run_icon(){
          run_pic='<img src=\"".Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_SKIN)."adminhtml/default/default/images/_run.gif\""."/>'; 
                document.getElementById('qixolimport_process').innerHTML=run_pic;
                //document.getElementById('qixolimport_import_done').innerHTML=run_pic;
    
    

      } 

 var Import_promo = Class.create();
 Import_promo.prototype = {

initialize: function(postUrl, postUrlUpd) {
                this.postUrl = postUrl; 
                this.postUrlUpd = postUrlUpd;
                this.failureUrl = document.URL;
                // unique user session ID
                this.SID = null;
                // object with event message data
                this.objectMsg = null;
                this.prevMsg = '';
                // interval object
                this.updateTimer = null;
                // default shipping code. Display on errors

                 elem = 'checkoutSteps';
                 clickableEntity = '.head';

                // overwrite Accordion class method
                var headers = $$('#' + elem + ' .section ' + clickableEntity);
                headers.each(function(header) {
                        Event.observe(header,'click',this.sectionClicked.bindAsEventListener(this));
                        }.bind(this));
            },
startPromotionImport: function () {
                 _this = this;
                 new Ajax.Request(this.postUrl,
                         {
          method:'post',
          parameters: '',
          requestTimeout: 10,
          /*
          onLoading:function(){
            alert('onLoading');
            },
            onLoaded:function(){
            alert('onLoaded');
            },
          */
onSuccess: function(transport) {
    var response = transport.responseText || null;
    _this.SID = response;
    if (_this.SID) {
    _this.updateTimer = setInterval(function(){_this.updateEvent();},2000);
    $('session_id').value = _this.SID;
    } else {
    alert('Can not get your session ID. Please reload the page!');
    }
},
onTimeout: function() { alert('Can not get your session ID. Timeout!'); },
onFailure: function() { alert('Something went wrong...') }
    });

},

updateEvent: function () {
                 _this = this;
                 new Ajax.Request(this.postUrlUpd,
                         {
method: 'post',
parameters: {session_id: this.SID},
onSuccess: function(transport) {
_this.objectMsg = transport.responseText.evalJSON();
_this.prevMsg = _this.objectMsg.message;
if(_this.prevMsg!=''){
   _this.updateStatusHtml();
}

if (_this.objectMsg.error == 1) {
// Do something on error
_this.clearUpdateInterval();
}

if (_this.objectMsg.finished == 1) {
 _this.objectMsg.message='Import finished';
 _this.updateStatusHtml();
_this.clearUpdateInterval();

}

},
onFailure: this.ajaxFailure.bind(),
    });
},

updateStatusHtml: function(){
    message=this.objectMsg.message.toLowerCase();
    extendedmessage=this.objectMsg.extmessage.toLowerCase();
    mess_id='qixolimport_'+message.replace(/\s+/g, '_');    
    if(!document.getElementById(mess_id)){
    //     alert(mess_id+' - not exist');
    }     
    else{
        //alert (mess_id+' - exist');
        $(mess_id).innerHTML='<img src=\"".Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_SKIN)."adminhtml/default/default/images/_yes.gif"."\"/>'
        if (mess_id=='qixolimport_import_done'){//if processed quicker
             $('qixolimport_process').innerHTML='<img src=\"".Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_SKIN)."adminhtml/default/default/images/_yes.gif"."\"/>'
        }
    }     
    if (extendedmessage!='')
    $('qixolimport_status_template').innerHTML=extendedmessage;
    //$('qixolimport_status_template').innerHTML=htm+'<br>'+this.objectMsg.message;
},

ajaxFailure: function(){
                     this.clearUpdateInterval();     
                     location.href = this.failureUrl;
},

clearUpdateInterval: function () {
                             clearInterval(this.updateTimer);
},


 }
        </script>
        ";
        return $html;
    }
}