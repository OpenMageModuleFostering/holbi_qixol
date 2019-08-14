<?php
class Holbi_Qixol_Block_Sales_Order_Total extends Mage_Core_Block_Template
{
    /**
     * Get label cell tag properties
     *
     * @return string
     */
    public function getLabelProperties()
    {
        return $this->getParentBlock()->getLabelProperties();
    }

    /**
     * Get order store object
     *
     * @return Mage_Sales_Model_Order
     */
    public function getOrder()
    {
        return $this->getParentBlock()->getOrder();
    }

    /**
     * Get totals source object
     *
     * @return Mage_Sales_Model_Order
     */
    public function getSource()
    {
        return $this->getParentBlock()->getSource();
    }

    /**
     * Get value cell tag properties
     *
     * @return string
     */
    public function getValueProperties()
    {
        return $this->getParentBlock()->getValueProperties();
    }

    /**
     * Initialize reward points totals
     *
     */
    public function initTotals()
    {
        if ((float) $this->getOrder()->getBasePointsAmount()) {
            $source = $this->getSource();
            $value  = $source->getPointsAmount();

            $this->getParentBlock()->addTotal(new Varien_Object(array(
                'code'   => 'points',
                'strong' => false,
                'label'  => 'Reward points discount',
                'value'  => $source instanceof Mage_Sales_Model_Order_Creditmemo ? - $value : -$value
            )));
        }

        return $this;
    }
}
