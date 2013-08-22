<?php
class TVPage_Connect_Model_Mysql4_Tvpconnect extends Mage_Core_Model_Mysql4_Abstract {
	public function _construct() {
		$this->_init('tvpconnect/tvpconnect', 'key_id');
	}
}
