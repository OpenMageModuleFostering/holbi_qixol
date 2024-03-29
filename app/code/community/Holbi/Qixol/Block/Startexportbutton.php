<?php
class Holbi_Qixol_Block_Startexportbutton extends Mage_Adminhtml_Block_System_Config_Form_Field
{

    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $this->setElement($element);
        $_hlp = Mage::helper('qixol');
        $html = $this->AddJs();

        $html .= '<div id="qixolexport_status_template" name="qixolexpor_status_template" style="display:none">';//none
        $html .= $this->getStatusTemplateHtml();
        $html .= '</div>';

        $start_import_button = $this->getLayout()->createBlock('adminhtml/widget_button')
            ->setType('button')
            ->setClass('scalable')
            ->setLabel('Export now')
            ->setOnClick("start_product_export()")
            ->toHtml();
        $safe_mode_set = ini_get('safe_mode');
        if($safe_mode_set){
            $html .="<p class='sinch-error'><b>You can't start export (safe_mode is 'On'. set safe_mode = Off in php.ini )<b></p>";
        } else {
            $html .= $start_import_button;    
        }

        $export=Mage::getModel('qixol/sinch');
        $last_import=$export->getDataOfLatestExport();
        $last_exp_status=$last_import['last_message'];
        if($last_exp_status=='error'){
            $html.='<div id="export_current_status_message" name="export_current_status_message" style="display:true"><br><br><hr/><p class="sinch-error">The export has failed.<br> Error reporting "'.$last_import['export_what'].'": "'.$last_import['status_export_message'].'"</p></div>';
        }elseif($last_imp_status=='success'){
            $html.='<div id="export_current_status_message" name="export_current_status_message" style="display:true"><br><br><hr/><p class="sinch-success">Data exported succesfully!</p></div>';
        }elseif($last_imp_status=='process'){
            $html.='<div id="export_current_status_message" name="export_current_status_message" style="display:true"><br><br><hr/><p>Export is running now</p></div>';
        }else{
            $html.='<div id="export_current_status_message" name="export_current_status_message" style="display:true"></div>';
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
               Start export
               &nbsp
               <span id='qixolexport_process'> 
                <img src='".$run_pic."'
                 alt='".$_hlp->__('product export run')."' /> 
               </span> 
            </li>   
              <!--li>
               Export finished   
               &nbsp
               <span id='qixolexport_export_done'>  
                <img src='".$run_pic."'
                 alt='".$_hlp->__('Export finished')."' /> 
               </span> 
            </li-->   

           </ul>
        ";
        return $html;
    }

    protected function AddJs()
    {
        $post_url=$this->getUrl('qixol_admin/ajax/ExportProduct');
        $post_url_upd=$this->getUrl('qixol_admin/ajax/UpdateStatus');
        $html = "
        <script>
            function start_product_export(){
                      set_run_icon();
                    status_data=document.getElementById('qixolexport_status_template');   
                    curr_status_data=document.getElementById('export_current_status_message'); 
                    curr_status_data.style.display='none';
                    status_data.style.display='';
//                    status_data.innerHTML='';
                    sinch = new Sinch('$post_url','$post_url_upd');
                    sinch.startProductExport();

                    //
            }
      function set_run_icon(){
          run_pic='<img src=\"".Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_SKIN)."adminhtml/default/default/images/_run.gif\""."/>'; 
                document.getElementById('qixolexport_process').innerHTML=run_pic;
                //document.getElementById('qixolexport_export_done').innerHTML=run_pic;
    
    

      } 

 var Sinch = Class.create();
 Sinch.prototype = {

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
startProductExport: function () {
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
    _this.updateTimer = setInterval(function(){_this.updateEvent();},4000);
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
    mess_id='qixolexport_'+message.replace(/\s+/g, '_');    
    if(!document.getElementById(mess_id)){
    //     alert(mess_id+' - not exist');
    }     
    else{
        //alert (mess_id+' - exist');
        $(mess_id).innerHTML='<img src=\"".Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_SKIN)."adminhtml/default/default/images/_yes.gif"."\"/>'
        if (mess_id=='qixolexport_export_done'){//if processed quicker
             $('qixolexport_process').innerHTML='<img src=\"".Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_SKIN)."adminhtml/default/default/images/_yes.gif"."\"/>'
        }
    }     
    if (extendedmessage!='')
    $('qixolexport_status_template').innerHTML=extendedmessage;
    //$('qixolexport_status_template').innerHTML=htm+'<br>'+this.objectMsg.message;
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