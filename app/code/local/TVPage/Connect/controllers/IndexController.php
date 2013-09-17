<?php
/**
 * The TVPage Controller
 */
class TVPage_Connect_IndexController extends Mage_Core_Controller_Front_Action{

	private $page;
	private $limit = 20;
	private $json;
  
  private $requestParams = array();

  /**
   * Help Action 
   * 
   * @return json help object
   */
  public function helpAction(){
    
    $this->json['helpText']  = "Pass actions with url/action?[vars]";
    $this->json['actions']  = array (
      'products' => array (
        'description' => "list of products",
        'options' => array (
          'q' => "search string",
          'cat[]' => 'filter categories',
          'p'=> 'page',
          'l'=> 'limit',
        )  
      ),
      'categories' => array(
        'description' => "List of categories"  
      )
    );
    
    $this->tvp_response();
  }  
  
  /**
   * Main controller function
   * 
   * @return type
   */
	public function indexAction(){
    $this->initTVP();
    return $this->helpAction();
	}
  
  /**
   * Retrieve products
   * 
   * @return json object with products based on filter criteria
   */
  public function productsAction(){
    $this->initTVP();
    $collection= Mage::getResourceModel('catalog/product_collection');
    
    $collection->addFieldToFilter('type_id', array('eq' => "simple"));
    // create filters
    if ( isset($this->requestParams['q']) ) {
      $search = $this->requestParams['q'];
    $collection->addFieldToFilter(
      array(
        array('attribute' => 'name', 'like' => "%$search%"))
      );
    }

    $categories = $this->requestParams['cat'];
    // now if categories were passed let's add them
    if (  is_array($categories) &&  sizeof($categories) > 0 ) {
      foreach ($categories as $cat) {
        if ($cat == "null") {
          $ctf[]['null'] = true;
        } else {
          $ctf[]['finset'] = $cat;
        }
      }
      
    $collection->joinField('category_id', 'catalog/category_product', 'category_id', 'product_id = entity_id', null, 'left')
      ->addAttributeToFilter('category_id',array($ctf));
    }

    $page = ((int)$this->requestParams['p'] + 1);
    $limit = (int)$this->requestParams['l'];
    if ($limit <= 0) {
      $limit = $this->limit;
    }
    
    $collection->setPageSize($limit);
    $collection->setCurPage($page);
    $collection->load();
    
    $this->json['last_page'] = $collection->getLastPageNumber();
    $this->json['num_products'] = $collection->getSize();
    $this->json['products'] = array();
    foreach ($collection AS $prod) {
      $this->json['products'][$prod['entity_id']] = $this->getProductInfo($prod['entity_id'],$prod['updated_at']);
    }
    
    $this->tvp_response();
  }
  
  /**
   * Retrieves all categories
   * 
   * @return void
   */
  public function categoriesAction(){
    $this->initTVP();
    $this->getCategories();
    $this->tvp_response();
  }
  
  /**
   * Send response back to client. Take into account jsonp wrapper
   * Echos data to stdout
   * 
   * @return void
   */
  protected function tvp_response(){
    header("Content-type:application/json");
    $callback = $this->requestParams['callback'];
    $data = json_encode($this->json);
    if ( strlen($callback) ) {
      $data = $callback.'('.$data.')';
    }
    echo $data;
  }  
  
  /**
   * Parses and sets the request parameters
   * 
   * @return void
   */
  protected function parseRequestParams(){
    $this->requestParams = Mage::app()->getRequest()->getParams();
  }
  
  /**
   * * Called by each control type.
   * * Initializes 
   * * Checks api key is valid
   *
   *
   */
  protected function initTVP(){
    $this->json = array();
    $this->json['version'] = $this->getTVPageConnectVersion();
    if ( !$this->verifyAccessKey($_GET['apiKey']) ) {
      $this->json['error'] = "Invalid API Key";
      $this->tvp_response();
      exit(1);
    }

    $this->parseRequestParams();
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
      $category = $this->getCategoryInfo($c);
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
      'children' => $cat->getChildren()
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
  
  /**
   * Retrieve the category model
   * 
   * @return type
   */
  private function getCategoryModel(){
    return Mage::getModel('catalog/category');
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
      'img' => htmlspecialchars($prod->getImageUrl()),
      //'model' => htmlspecialchars($prod->getModel()),
      'description'=>htmlspecialchars(strip_tags($prod->getShortDescription())),
      'isActive'=> ($prod->getStatus() == 1),
      'url'=>htmlspecialchars($prod->getProductUrl()),
      'price'=>htmlspecialchars($prod->getPrice()),
    );
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
		$mod_info = (array)Mage::getConfig()->getNode('modules/TVPage_Connect')->children();
    return $mod_info['version'];
	}
}