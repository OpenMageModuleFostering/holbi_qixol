<?php
class Holbi_Qixol_Adminhtml_BannerController extends Mage_Adminhtml_Controller_action {

    protected function _initAction() {
        $this->loadLayout()
                ->_setActiveMenu('qixol/items')
                ->_addBreadcrumb(Mage::helper('qixol')->__('Banner Manager'), Mage::helper('qixol')->__('Banner Manager'));

        return $this;
    }

    public function indexAction() {
        $this->_initAction()
                ->renderLayout();
    }

    public function editAction() {
        $id = $this->getRequest()->getParam('id');
        $model = Mage::getModel('qixol/banner')->load($id);

        //$BannerImages = Mage::getModel('qixol/banner')->getBannerImages($id);


        if ($model->getId() || $id == 0) {
            $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
            if (!empty($data)) {
                $model->setData($data);
            }


            Mage::register('banner_data', $model);

            $this->loadLayout();
            $this->_setActiveMenu('qixol/items');

            $this->_addBreadcrumb(Mage::helper('qixol')->__('Banner Manager'), Mage::helper('qixol')->__('Banner Manager'));
            $this->_addBreadcrumb(Mage::helper('qixol')->__('Item Banner'), Mage::helper('qixol')->__('Item Banner'));

            $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

            $this->_addContent($this->getLayout()->createBlock('qixol/adminhtml_banner_edit'))
                    ->_addLeft($this->getLayout()->createBlock('qixol/adminhtml_banner_edit_tabs'));
            $version = substr(Mage::getVersion(), 0, 3);
            if (($version=='1.4' || $version=='1.5') && Mage::getSingleton('cms/wysiwyg_config')->isEnabled()) {
                $this->getLayout()->getBlock('head')->setCanLoadTinyMce(true);
            }
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
        if ($data = $this->getRequest()->getPost()) {
            if (is_array($data['display_zone'])){
              $data['display_zone']=join(",",$data['display_zone']);
             }

            $model = Mage::getModel('qixol/banner');
            $model->setData($data)
                    ->setId($this->getRequest()->getParam('id'));

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

                if ($this->getRequest()->getParam('back')||$image_saved) {
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
        if ($this->getRequest()->getParam('id') > 0) {
            try {
                $model = Mage::getModel('qixol/banner')->load($this->getRequest()->getParam('id'));
                $_helper = Mage::helper('qixol');
                //$filePath = Mage::getBaseDir('media').DS.$_helper->updateDirSepereator($model->getFilename());
                $model->delete();
                //$this->removeFile($filePath);

                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('qixol')->__('Item was successfully deleted'));
                $this->_redirect('*/*/');
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            }
        }
        $this->_redirect('*/*/');
    }

    public function massDeleteAction() {
        $bannerIds = $this->getRequest()->getParam('banner');
        if (!is_array($bannerIds)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('qixol')->__('Please select item(s)'));
        } else {
            try {
                foreach ($bannerIds as $bannerId) {
                    $model = Mage::getModel('qixol/banner')->load($bannerId);
                    $_helper = Mage::helper('qixol');
                    //$filePath = Mage::getBaseDir('media').DS.$_helper->updateDirSepereator($model->getFilename());
                    $model->delete();
                    //$this->removeFile($filePath);
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                        Mage::helper('qixol')->__(
                                'Total of %d record(s) were successfully deleted', count($bannerIds)
                        )
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }

    public function massStatusAction() {
        $bannerIds = $this->getRequest()->getParam('banner');
        if (!is_array($bannerIds)) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Please select item(s)'));
        } else {
            try {
                foreach ($bannerIds as $bannerId) {
                    $banner = Mage::getSingleton('qixol/banner')
                                    ->load($bannerId)
                                    ->setStatus($this->getRequest()->getParam('status'))
                                    ->setIsMassupdate(true)
                                    ->save();
                }
                $this->_getSession()->addSuccess(
                        $this->__('Total of %d record(s) were successfully updated', count($bannerIds))
                );
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }

    public function exportCsvAction() {
        $fileName = 'banner.csv';
        $content = $this->getLayout()->createBlock('qixol/adminhtml_banner_grid')
                        ->getCsv();

        $this->_sendUploadResponse($fileName, $content);
    }

    public function exportXmlAction() {
        $fileName = 'banner.xml';
        $content = $this->getLayout()->createBlock('qixol/adminhtml_banner_grid')
                        ->getXml();

        $this->_sendUploadResponse($fileName, $content);
    }

    public function uploadAction()
    {
        try {
            $uploader = new Mage_Core_Model_File_Uploader('image');
            $uploader->setAllowedExtensions(array('jpg','jpeg','gif','png'));
            $uploader->addValidateCallback('catalog_product_image',
                Mage::helper('catalog/image'), 'validateUploadFile');
            $uploader->setAllowRenameFiles(true);
            $uploader->setFilesDispersion(true);
            
            // TODO: 'custom' and 'banners' to be read from config
            // TODO: better folder / file naming convention
//            $ext = substr($_FILES['filename']['name'], strrpos($_FILES['filename']['name'], '.') + 1);
//            $fname = 'File-' . time() . '.' . $ext;
//            $path = Mage::getBaseDir('media') . DS . 'custom' . DS . 'banners';
            $result = $uploader->save(
                Mage::getSingleton('catalog/product_media_config')->getBaseTmpMediaPath()
            );

//            Mage::dispatchEvent('catalog_product_gallery_upload_image_after', array(
//                'result' => $result,
//                'action' => $this
//            ));

            /**
             * Workaround for prototype 1.7 methods "isJSON", "evalJSON" on Windows OS
             */
            $result['tmp_name'] = str_replace(DS, "/", $result['tmp_name']);
            $result['path'] = str_replace(DS, "/", $result['path']);

            $result['url'] = Mage::getSingleton('catalog/product_media_config')->getTmpMediaUrl($result['file']);
            $result['file'] = $result['file'] . '.tmp';
            $result['cookie'] = array(
                'name'     => session_name(),
                'value'    => $this->_getSession()->getSessionId(),
                'lifetime' => $this->_getSession()->getCookieLifetime(),
                'path'     => $this->_getSession()->getCookiePath(),
                'domain'   => $this->_getSession()->getCookieDomain()
            );

        } catch (Exception $e) {
            $result = array(
                'error' => $e->getMessage(),
                'errorcode' => $e->getCode());
        }

        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }
    
//    public function addBannerImageAction() {
//        $this->_forward('edit/bannerimage_edit');
//    }
//    
//    protected function _sendUploadResponse($fileName, $content, $contentType='application/octet-stream') {
//        $response = $this->getResponse();
//        $response->setHeader('HTTP/1.1 200 OK', '');
//        $response->setHeader('Pragma', 'public', true);
//        $response->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true);
//        $response->setHeader('Content-Disposition', 'attachment; filename=' . $fileName);
//        $response->setHeader('Last-Modified', date('r'));
//        $response->setHeader('Accept-Ranges', 'bytes');
//        $response->setHeader('Content-Length', strlen($content));
//        $response->setHeader('Content-type', $contentType);
//        $response->setBody($content);
//        $response->sendResponse();
//        die;
//    }

    protected function removeFile($file) {
        try {
            $io = new Varien_Io_File();
            $result = $io->rmdir($file, true);
        } catch (Exception $e) {

        }
    }

}