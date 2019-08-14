<?php
class Holbi_Qixol_Model_Bonuspoints extends Mage_Core_Model_Abstract
{
   protected $customerId = -1;
   protected $storeId    = -1;
   protected $currentPoints  = NULL;
   protected $earnedPoints = NULL;
   protected $spentPoints    = NULL;

    public function _construct()
    {
        parent::_construct();
        $this->_init('qixol/bonuspoints','points_account_id');
    }
  

      
  public function save() {
        $connection = Mage::getSingleton('core/resource')->getConnection('bonuspoints_write');
                
        $connection->beginTransaction();
        $fields = array();
        $fields['customer_id'] = $this->customerId;
        $fields['store_id'] = (int)$this->storeId;
        $fields['current_points'] = (int)$this->currentPoints;
        $fields['earned_points'] = (int)$this->earnedPoints;
        $fields['spent_points'] = (int)$this->spentPoints;

        
        try {
            $this->_beforeSave();
            if (!is_null($this->pointsAccountId)) {
              
              
              $where = $connection->quoteInto('customer_id=?',$fields['customer_id']);
              $connection->update('qixol_cutomer_points', $fields, $where);
                } 
                else {
              $connection->insert('qixol_cutomer_points', $fields);
              //$this->rewardpointsAccountId =$connection->lastInsertId('rewardpoints_account');
              //$this->rewardpointsAccountId =2;
                }
           $connection->commit();
           $this->_afterSave();
          }
        catch (Exception $e) {
            $connection->rollBack();
            throw $e;
        }
          return $this;
      }
        
  public function load($id , $field=null) {
        if ($field === null) {
          $field = 'customer_id';
        }
        $connection = Mage::getSingleton('core/resource')->getConnection('bonuspoints_read');
        $select = $connection->select()->from('qixol_cutomer_points')->where('qixol_cutomer_points.'.$field.'=?', $id);
        $data = $connection->fetchRow($select);
        if (!$data) {
        return $this;
        }
        $this->setPointsAccountId($data['points_account_id']);
        $this->setCustomerId($data['customer_id']);
        $this->setStoreId($data['store_id']);
        $this->setCurrentPoints($data['current_points']);
        $this->setEarnedPoints($data['earned_points']);
        $this->setSpentPoints($data['spent_points']);
        $this->_afterLoad();
        return $this;
        }
    
    
    public function addPoints($p, $customerId) {
      $collpoint = Mage::getModel('qixol/bonuspoints')->load($customerId);
      if($collpoint){
        $currentPoints = $collpoint->getCurrentPoints();
        $earnedPoints = $collpoint->getEarnedPoints();
        $spentPoints = $collpoint->getSpentPoints();
      }
      else{
        $spentPoints = 0;
        $currentPoints = 0;
        $earnedPoints = 0;
      }
        $storeId    = Mage::app()->getStore()->getStoreId();;
      Mage::log('add points $p: '. $p);
      $this->currentPoints = $currentPoints + $p;
      $this->earnedPoints = $earnedPoints + $p;
      $this->spentPoints = $spentPoints;
      $this->customerId = $customerId;
      $this->storeId = $storeId;
    }
    
    public function subtractPoints($p, $customerId) {
      $collpoint = Mage::getModel('qixol/bonuspoints')->load($customerId);
      if($collpoint){
        $currentPoints = $collpoint->getCurrentPoints();
        $earnedPoints = $collpoint->getEarnedPoints();
        $spentPoints = $collpoint->getSpentPoints();
      }
      else{
        $spentPoints = 0;
        $currentPoints = 0;
        $earnedPoints = 0;
      }
      $storeId    = Mage::app()->getStore()->getStoreId();;
      Mage::log('substract points $p: '. $p);
      $this->currentPoints = $currentPoints - $p;
      $this->spentPoints = $spentPoints + $p;
      $this->customerId = $customerId;
      $this->earnedPoints = $earnedPoints;
      $this->storeId = $storeId;
    }
          
  
}