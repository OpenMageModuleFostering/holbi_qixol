<?php
class Holbi_Qixol_Model_System_Config_Source_Cart_Settings
{

    public function getAllOptions()
    {
        $hlp = Mage::helper('qixol');
        return array(
            array('label'=>$hlp->__('Show promotion end-user/customer description'), 'value'=>'displaytext'),
            array('label'=>$hlp->__('Show promotion name'), 'value'=>'displayname'),
            array('label'=>$hlp->__('Show promotion type'), 'value'=>'displaytype'),
            array('label'=>$hlp->__("Don't display"), 'value'=>'dontdisplay'),
        );
    }

    public function toOptionArray(){
        $hlp = Mage::helper('qixol');

        return array(
            array(
                'value' => 'displaytext',
                'label' => $hlp->__('Show promotion end-user/customer description'),
            ),
            array(
                'value' => 'displayname',
                'label' => $hlp->__('Show promotion name'),
            ),
            array(
                'value' => 'displaytype',
                'label' => $hlp->__('Show promotion type'),
            ),
            array(
                'value' => 'dontdisplay',
                'label' => $hlp->__("Don't display"),
            ),
        );
    }
}