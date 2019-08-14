<?php
class Holbi_Qixol_Model_Status extends Varien_Object {
    const STATUS_DISABLED = 0;
    const STATUS_ENABLED = 1;

    static public function getOptionArray() {
        return array(
            self::STATUS_DISABLED => Mage::helper('qixol')->__('Disabled'),
            self::STATUS_ENABLED => Mage::helper('qixol')->__('Enabled')
        );
    }

}