<?php
/**
 * The TVPage Controller
 */
class TVPage_Connect_IndexController extends Mage_Core_Controller_Front_Action{

	private $pageNr;
	private $pageSize = 100;
	private $json;
  private $search = '';

  /**
   * Main controller function
   * 
   * @return type
   */
	public function indexAction(){
		// Initialize XML Response
    $this->json = array();
		$this->json['version'] = $this->getTVPageConnectVersion();
    if ( !$this->verifyAccessKey($_GET['apiKey']) ) {
      $this->json['error'] = "Invalid API Key";
      return $this->echoResult();
     }
    
    // Parse parameters and initiliase variables
    $this->pageNr = (((isset($_GET['page']) && (is_numeric($_GET['page']))) ? (int) $_GET['page'] : 0)) + 1;	// OS uses 0 for first page
    $action = (isset($_GET['action']) ? $_GET['action'] : '');
    $this->search = (isset($_GET['search']) && strlen($_GET['search']) ? $_GET['search'] : '');	// OS uses 0 for first page
    
    if ( !strlen($action) ) {
      $this->json['error'] = "No action passed";
      return $this->echoResult();
    }
    
    switch ($action) {
      case 'products':
        $this->productsAction();
        break;
      
      case 'categories':
        $this->categoriesAction();
        break;
      
      default:
        $this->json["error"] = "Unknown action: ".$action;
        break;
    }
	}

  /**
   * Retrieve products. Fills the product array
   * 
   * @return void
   */
	public function productsAction(){
		if( $this->getPageNoIsValid() ) {
			$this->getProducts();
      $this->echoResult();
		}
	}

  /**
   * Retrieves all categories
   * 
   * @return void
   */
  public function categoriesAction(){
    $this->getCategories();
    $this->echoResult();
  }
  /**
   * Add the products to the data array
   * 
   * @return void
   */
	private function getProducts() {
    $this->json['products'] = array();
		$prodCol = $this->getProductCollection();
		foreach($prodCol as $prod){
      $prod = $this->getProductInfo($prod['entity_id'],$prod['updated_at']);
      if ( is_array($prod) ) {
        $this->json['products'][$prod['id']] = $prod;
      }
		}
	}
  
  /**
   * Fetch categories
   * 
   * @return void sets the data object's categories
   */
  private function getCategories(){
    $this->json['categories'] = array();
    $collection = $this->getCategoryCollection();
    foreach ($collection AS $c) {
      $category = $this->getCategoryInfo($c['entity_id'], $c);
      if (is_array($category)) {
        $this->json['categories'][$category['id']] = $category;
      }
    }
  }

  /**
   * Return the product info for a single product
   * 
   * @param int $id Product id
   * 
   * @return array product info
   */
	private function getCategoryInfo($categoryData){
    $id = $categoryData['entity_id'];
		$cat = $this->getCategoryModel()->load($id);

		return array(
      'id' => $id,
      'name' => htmlspecialchars($cat->getName()),
      'product_count' => htmlspecialchars($cat->getProductCount()),
      'parent_id' => $categoryData['parent_id'] ,
      'level' => $categoryData['level'],
      //'entity_type_id'=>  $c['entity_type_id']
      //'attribute_set_id'=>  $c['attribute_set_id']
    );
	}
  
  /**
   * Retrieve the product collection 
   * 
   * @param int $pageSize
   * @param int $pageNo
   * 
   * @return array
   */
	private function getCategoryCollection(){
		$categoryModel = $this->getCategoryModel();
		$categoryData = $categoryModel->getCollection()
      //->setPageSize($pageSize)
      //->setCurPage($pageNo)
      ->getData();
    
		return $categoryData;
	}
  
  private function getCategoryModel(){
    return Mage::getModel('catalog/category');
  }
  
  /**
   * Echos the json encoded data array to stdout
   * 
   * @return void
   */
  protected function echoResult(){
    header("Content-type:application/json");
    $data = json_encode($this->json);
    if ( strlen($_GET['callback']) ) {
      $data = $_GET['callback'].'('.$data.')';
    }
    echo $data;
  }
  
  /**
   * Get the product model
   * 
   * @return Model
   */
	private function getProductModel(){
		return Mage::getModel('catalog/product');
	}

  /**
   * Retrieve the product collection 
   * 
   * @param int $pageSize
   * @param int $pageNo
   * 
   * @return array
   */
	private function getProductCollection(){
		$prodModel = $this->getProductModel();
		$prod_data = $prodModel->getCollection();
    $prod_data->addFieldToFilter('type_id', array('eq' => "simple"));
    $prod_data->setPageSize($this->pageSize)
    ->setCurPage($this->pageNr)
    ->getData();
    
		return $prod_data;
	}
  
  /**
   * Return the product info for a single product
   * 
   * @param int $id Product id
   * 
   * @return array product info
   */
	private function getProductInfo($id){
		$prod = $this->getProductModel()->load($id);
		$type = $prod->getTypeId();
    
    // Only simple products for now
    if ( $type !== "simple" ) {
      return false;
    }
    
		return array(
      'id' => $id,
      'code' => htmlspecialchars($prod->getSku()),
      'name' => htmlspecialchars($prod->getName()),
      //'Model' => htmlspecialchars($prod->getModel()),
      'description'=>htmlspecialchars(strip_tags($prod->getShortDescription())),
      'isActive'=> ($prod->getStatus() == 1),
      'url'=>htmlspecialchars($prod->getProductUrl()),
      'price'=>htmlspecialchars($prod->getPrice()),
    );
	}

  /**
   * Check page is valid 
   * 
   * @return bool
   */
	private function getPageNoIsValid(){
    // TODO: add filter search string... 
    $productCollections = Mage::getModel('catalog/product')->getCollection()->getData();
    return ($this->pageNr <= ceil(count($productCollections)/$this->pageSize));
	}
  
  /**
   * Retrieve the access key from database
   * 
   * @return string key
   */
	private function getAccessKey(){
		$helper =  $this->getHelper();
		$key = $helper->getKey();
		return $key;
	}

  /**
   * Checks api key match
   * 
   * @param string $userKey Key passed in request
   * 
   * @return boolean TRUE for match, otherwise FALSEs
   */
	private function verifyAccessKey($userKey){
		$key = $this->getAccessKey();
		if($userKey === $key){
			return true;
		}
	}

  /**
   * Retrieve the helper object
   * 
   * @return type
   */
	private function getHelper(){
		return Mage::helper('tvpconnect');
	}

  /**
   * Retrieve the version for this plugin
   * 
   * @return string
   */
	private function getTVPageConnectVersion(){
		//$mod_info = (array)Mage::getConfig()->getNode('modules/TVPage_Connect')->children();
		return '1.0.0';
	}
}