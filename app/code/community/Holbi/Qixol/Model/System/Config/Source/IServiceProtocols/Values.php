<?php
class Holbi_Qixol_Model_System_Config_Source_IServiceProtocols_Values
{

    public function getAllOptions()
    {
        $hlp = Mage::helper('qixol');
        return array(
            array('label'=>$hlp->__('REST services [RECOMMENDED]'), 'value'=>'REST'),
            array('label'=>$hlp->__('SOAP services'), 'value'=>'SOAP')
        );
    }

    public function toOptionArray(){
        $hlp = Mage::helper('qixol');

        return array(
            array(
                'value' => 'REST',
                'label' => $hlp->__('REST services [RECOMMENDED]')
            ),
            array(
                'value' => 'SOAP',
                'label' => $hlp->__('SOAP services')
            )
        );
    }
}