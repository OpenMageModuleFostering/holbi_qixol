<?php
class Holbi_Qixol_Adminhtml_ShippingmapController extends Mage_Adminhtml_Controller_action {

    protected function _initAction() {
        $this->loadLayout()
                ->_setActiveMenu('qixol/shippingmap')
                ->_addBreadcrumb(Mage::helper('qixol')->__('Shipping Method Integration Codes'), Mage::helper('qixol')->__('Shipping Method Integration Codes'));

        return $this;
    }

    public function indexAction() {
        $this->_initAction()
                ->renderLayout();
    }

    public function editAction() {
        $id = $this->getRequest()->getParam('id');
        $model = Mage::getModel('qixol/shippingmap')->load($id);

        if ($model->getShippingName() || $id == '') {

            $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
            if (!empty($data)) {
                $model->setData($data);
            }

            Mage::register('shippingmap_data', $model);

            $this->loadLayout();
            $this->_setActiveMenu('qixol/shippingmap');

            $this->_addBreadcrumb(Mage::helper('qixol')->__('Shippings Map'), Mage::helper('qixol')->__('Shippings Map'));

            $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

            $this->_addContent($this->getLayout()->createBlock('qixol/adminhtml_shippingmap_edit'))
                    ->_addLeft($this->getLayout()->createBlock('qixol/adminhtml_shippingmap_edit_tabs'));
            $version = substr(Mage::getVersion(), 0, 3);
            $this->renderLayout();
        } else {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('qixol')->__('Item does not exist'));
            $this->_redirect('*/*/');
        }
    }

    public function newAction() {
        $this->_forward('edit');
    }

    public function saveAction() {
        $imagedata = array();

        if ($data = $this->getRequest()->getPost()) {
            $model = Mage::getModel('qixol/shippingmap');
            $model->setData($data)
                    ->setId($this->getRequest()->getParam('shipping_name'));

            try {
                if ($model->getCreatedTime == NULL || $model->getUpdateTime() == NULL) {
                    $model->setCreatedTime(now())
                            ->setUpdateTime(now());
                } else {
                    $model->setUpdateTime(now());
                }
  
                $model->save();
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('qixol')->__('Item was successfully saved'));
                Mage::getSingleton('adminhtml/session')->setFormData(false);

                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', array('id' => $model->getId()));
                    return;
                }
                $this->_redirect('*/*/');
                return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setFormData($data);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('qixol')->__('Unable to find item to save'));
        $this->_redirect('*/*/');
    }

    public function deleteAction() {
        if ($this->getRequest()->getParam('id') !='') {
            try {
                $model = Mage::getModel('qixol/shippingmap')->load($this->getRequest()->getParam('id'));
                $_helper = Mage::helper('qixol');
                $model->delete();
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('qixol')->__('Item was successfully deleted'));
                $this->_redirect('*/*/');
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            }
        }
        $this->_redirect('*/*/');
    }


}