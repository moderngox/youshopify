<?php


namespace Shopify\AdminBundle\Service;	
use Shopify\AdminBundle\Form\ProductType;
use Shopify\AdminBundle\Service\ClientService;
use Shopify\AdminBundle\Form\JotForm;

/**
 * YouShopify API
 *
 * @copyright   2014 Modern Gox from Parisian Dog.
 * @link        http://www.parisiandog.com
 * @version     1.0
 * @package     YouShopify
 */
class YouShopifyService{

	/**
	* return the Shopify properties in order to connect the store
	*
	*/
	function initShopify(){
		/***** SHOPIFY STORE SETTINGS *******/
		$shop_domain ="frip-mint.myshopify.com";
		$api_key="38623838827b4d87858792d0e970e58f";
		$shared_secret="41ac08f5f06a6fb4bb368ab946339415";
		$access_token="c8b2fb6bd93481eafca5bc54cd24ae58";
		$client_service = new ClientService();
		$shopify = $client_service->client($shop_domain,$access_token,$api_key,$shared_secret);
		
		
		return $shopify;
	}

	/**
	*
	* retrive the Jotform submissions list
	* We can use this method if you integrated jotforms in your shop and your 
	* independent create-form is not up to work yet(i.e Maintenance mode)
	*
	* $chosen_form_number: the number of the chosen form
	*
	*/
	function getJotFormList($chosen_form_number){

		define("TITLE", "Name of the product");
		define("TYPE", "type");
		define ("DESCRIPTION", "Product story");
		define ("IMAGE", "Product image 1");
		$keys= array("title"=>"Name of the product",
			"type"=>"type",
			"description" => "Product story",
			"image" => "Product image 1");
		try {

			$jotformAPI = new JotForm("670b9b37cd5fe8f1a00fba654376bd2c"); //jotform API number

			$forms = $jotformAPI->getForms(0, 0, null, null);

			$chosenForm = $forms[$chosen_form_number];

			$latestFormID = $chosenForm["id"];

			$submissions = $jotformAPI->getFormSubmissions($latestFormID);


			$jotFormList = array();
			foreach($submissions as $submission){

				$product = new ProductType($id=uniqid());
				foreach ($submission["answers"] as $attribute){

					foreach($keys as $key=>$value){	
						$product->id = uniqid();
						switch ($attribute["text"]) {
							case 'Name of the product':
							$product->title = $attribute["answer"];
							break;
							case 'type':
							$product->type = $attribute["answer"];
							break;
							case 'Product story':
							$product->description = $attribute["answer"];
							break;
							case 'Product image 1':
							$product->image = $attribute["answer"][0];
							break;
							//TODO add VENDOR
						}

					}	
				}

				$jotFormList[] = $product;

			}
		}
		catch (Exception $e) {
			var_dump($e->getMessage());
		}

		return $jotFormList;		

	}
/**
* Simple mapping from our Bundle product format to the Shopify_API one
* $product is a ProductType
**/
function map($product){

	$shopify_product = array
	(

		"product"=>array( "title" =>$product->title, 
			"product_type" => $product->type,
			"body_html" =>	$product->description,
			"vendor"=>$product->vendor,
			"price"=>$product->price,
			"images"=>array( array("src"=>$product->image)),
			"published"=> true)
		);

	return $shopify_product;

}

/**
* Simple mapping between a $form(Form->getData) and a bundle $product (ProductType)
* 
**/
function mapFormToProduct($form,$product){

	
	$product->title=$form['title']->getData();
	$product->type=$form['type']->getData();
	$product->description=$form['description']->getData();
	$product->description=$form['description']->getData();
	$product->price=$form['price']->getData();
	$product->image=$form['image']->getData();
	$product->vendor=$form['vendor']->getData();
}

/**
* Create a product to your store
* 
**/
function create_update_Product($product,$collection_id){

	$shopify = $this->initShopify();

	$product_created = $shopify('POST', "/admin/products.json",$this->map($product), $response_headers);
	$this->addProductToCollect($shopify, $product_created['id'],$collection_id);
	return $product_created;
}

/**
* retrieve your shop collections
* 
**/
function getCollections(){
	$shopify = $this->initShopify();
	$collections = $shopify('GET', "/admin/custom_collections.json");
	return $collections;
}

/**
* Add a product to a collection $collection_id with the $product_id 
* 
**/
function addProductToCollect($shopify, $product_id,$collection_id){

	$collect = array
	(

		"collect"=>array(  
			"product_id" => $product_id,
			"collection_id" => $collection_id
			)
		);

	$shopify('POST',"/admin/collects.json",$collect);

}
}


?>