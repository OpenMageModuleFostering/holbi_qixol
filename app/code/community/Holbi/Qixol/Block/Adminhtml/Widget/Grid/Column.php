<?php
require_once 'Mage/Adminhtml/Block/Widget/Grid/Column.php';

class Holbi_Qixol_Block_Adminhtml_Widget_Grid_Column extends Mage_Adminhtml_Block_Widget_Grid_Column {

    protected function _getRendererByType() {
        switch (strtolower($this->getType())) {
            case 'banner':
                $rendererClass = 'qixol/adminhtml_widget_grid_column_renderer_banner';
                break;
            case 'sticker':
                $rendererClass = 'qixol/adminhtml_widget_grid_column_renderer_sticker';
                break;
            default:
                $rendererClass = parent::_getRendererByType();
                break;
        }
        return $rendererClass;
    }

    protected function _getFilterByType() {
        switch (strtolower($this->getType())) {
            case 'banner':
                $filterClass = 'qixol/adminhtml_widget_grid_column_filter_banner';
                break;
            case 'sticker':
                $filterClass = 'qixol/adminhtml_widget_grid_column_filter_banner';
                break;
            default:
                $filterClass = parent::_getFilterByType();
                break;
        }
        return $filterClass;
    }

}