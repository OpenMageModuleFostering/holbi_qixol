<?php
class Holbi_Qixol_Block_Adminhtml_Sticker_Edit extends Mage_Adminhtml_Block_Widget_Form_Container {

    public function __construct() {
        parent::__construct();

        $this->_objectId = 'id';
        $this->_blockGroup = 'qixol';
        $this->_controller = 'adminhtml_sticker';

        $this->_updateButton('save', 'label', Mage::helper('qixol')->__('Save Item'));
        $this->_updateButton('delete', 'label', Mage::helper('qixol')->__('Delete Item'));

        $this->_addButton('saveandcontinue', array(
            'label' => Mage::helper('adminhtml')->__('Save And Continue Edit'),
            'onclick' => 'saveAndContinueEdit()',
            'class' => 'save',
                ), -100);

        $this->_formScripts[] = "
            /*function toggleEditor() {
                if (tinyMCE.getInstanceById('sticker_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'sticker_content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'sticker_content');
                }
            }*/

            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }

            function showTypeContents(){
                var typeId=$('sticker_type').value;
                var show = ((typeId==0)?'block':'none');
                var hide = ((typeId==0)?'none':'block');
                $('filename').setStyle({display:show});
                $('filename_delete').setStyle({display:show});
                //$('sticker_content').setStyle({display:hide});
                setTimeout('stickerContentType()',1000);
                alert($('filename').getStyle('display'))
            }
         
            function stickerContentType(){
                var typeId=$('sticker_type').value;
                var hide = ((typeId==0)?'none':'block');
                $('buttonssticker_content').setStyle({display:hide});
                $('sticker_content_parent').setStyle({display:hide});
            }


            /* Event.observe('sticker_type', 'change', function(){
                    showTypeContents();
                });
            Event.observe(window, 'load', function(){
                    showTypeContents();
                }); */
        ";
    }

    public function getHeaderText() {
        if (Mage::registry('sticker_data') && Mage::registry('sticker_data')->getId()) {
            return Mage::helper('qixol')->__("Edit Item '%s'", $this->htmlEscape(Mage::registry('sticker_data')->getTitle()));
        } else {
            return Mage::helper('qixol')->__('Add Item');
        }
    }

}