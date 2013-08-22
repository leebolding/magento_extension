<?php

class TVPage_Connect_Model_Mysql4_Tvpconnect_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('tvpconnect/tvpconnect');
    }
}