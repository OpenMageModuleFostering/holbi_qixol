<?php
class Holbi_Qixol_Model_System_Config_Source_Iservices_Values
{

    public function getAllOptions()
    {
        $hlp = Mage::helper('qixol');
        return array(
            array('label'=>$hlp->__('evaluation services'), 'value'=>'evaluation'),
            array('label'=>$hlp->__('live services'), 'value'=>'live'),

        );
    }

    public function toOptionArray(){
        $hlp = Mage::helper('qixol');

        return array(
            array(
                'value' => 'evaluation',
                'label' => $hlp->__('evaluation services'),
            ),
            array(
                'value' => 'live',
                'label' => $hlp->__('live services'),
            ),
        );
    }
}