<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    TVPage
 * @package     TVPage_Connect
 * @author      Lior Kuyer <lkuyer@tvpage.com>
 */


/**
 * Product collection override to prevent exception
 * 
 * @category    TVPage
 * @package     TVPage_Connect
 * @author      Lior Kuyer <lkuyer@tvpage.com>
 */
class TVPage_Connect_Model_Resource_ProductCollection extends Mage_Catalog_Model_Resource_Product_Collection
{
    /**
     * Add an object to the collection
     *
     * @param Varien_Object $object
     * @return Mage_Eav_Model_Entity_Collection_Abstract
     */
    public function addItem(Varien_Object $object)
    {
      try {
        $rv =  parent::addItem($object);
      } catch (Exception $e) {
        $itemId = $this->_getItemId($object);
        if (!is_null($itemId)) {
            $this->_items[$itemId] = $object;
        } else {
            $this->_addItem($object);
        }
        $rv = $this;
      }
      
      return $rv;
    }
}
