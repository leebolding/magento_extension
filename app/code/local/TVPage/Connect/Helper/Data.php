<?php
class TVPage_Connect_Helper_Data extends Mage_Core_Helper_Abstract{

	private $data;

	 public function __construct(){
		$model = Mage::getModel('tvpconnect/tvpconnect');
		$data_arr = $model->getCollection()->getData();
		$this->data['key_id'] = $data_arr[0]['key_id'];
		$this->data['key'] = $data_arr[0]['key'];
		$this->data['url'] = $data_arr[1]['key'];
	}

	function getKeyId(){
		return $this->data['key_id'];
	}

	function getKey(){
		return $this->data['key'];
	}

	function getUrl(){
		return $this->data['url'];
	}
}
?>
