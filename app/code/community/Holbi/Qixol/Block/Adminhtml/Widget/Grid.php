<?php
require_once 'Mage/Adminhtml/Block/Widget/Grid.php';
class Holbi_Qixol_Block_Adminhtml_Widget_Grid extends Mage_Adminhtml_Block_Widget_Grid {

    public function addColumn($columnId, $column) {
        if (is_array($column)) {
            $this->_columns[$columnId] = $this->getLayout()->createBlock('qixol/adminhtml_widget_grid_column')
                            ->setData($column)
                            ->setGrid($this);
        }
        /* elseif ($column instanceof Varien_Object) {
          $this->_columns[$columnId] = $column;
          } */ else {
            throw new Exception(Mage::helper('adminhtml')->__('Wrong column format'));
        }

        $this->_columns[$columnId]->setId($columnId);
        $this->_lastColumnId = $columnId;
        return $this;
    }

}
