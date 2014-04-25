Symfony Standard Edition
========================

Welcome to You Shop(ify) API. This API allows you to communicate with your shopify store data such as products and collections. Thus, you can create a web app which consists in a CRUD service dedicated to your shop vendors. They will able to manage their products freely without the help of the shop administrator.

1) Prerequisites
----------------------------------

* Read the official shopify API documentation
* Visit and read documentation of these github pages: <a href="https://github.com/sandeepshetty/shopify_api" target="_blank">Shopify-api</a> & <a href="https://github.com/jotform/jotform-api-phptarget="_blank">Jotform-api</a>
* Check demos on the <a href="youshopify.herokuapp.com" target="_blank">You Shop(ify) site</a>.
* Clone the bundle

2) Usage
--------------------------------------
The bundle gathers the source code of the Shop(ify) website.
You will be interested in the Service package and particularly the ```YouShopifyService.php```:
```
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
      "body_html" =>  $product->description,
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
```
A simple usage of the API is shown in the ```ShopifyAdminController.php```:

```
class ShopifyAdminController extends Controller
{
  

  public function indexAction(Request $request)
  {
    return $this->render('ShopifyAdminBundle:Default:index.html.twig');
  }

  public function listSubmissionsAction(Request $request)
  {
    $service = $this->container->get('youshopify.service');
    $jotforms_list= $service->getJotFormList(2); // 2 represents the chosen Jotform number
        //put the list in the session
    $session = $request->getSession();
    $session->set('jotform_products',$jotforms_list);


    return $this->render('ShopifyAdminBundle:Default:jotform_submissions.html.twig',array('products' => $jotforms_list));

  }

  public function aboutAction(){

    return $this->render('ShopifyAdminBundle:Default:about.html.twig');
  }

  public function addAction(Request $request){

    $result_url="http://frip-mint.myshopify.com//collections/frontpage/products/"; //result url
    $new_product=new ProductType($id=uniqid());
    $form =$this->createForm(new ProductType());
    $form->handleRequest($request);
  //get YouShopify service
    $service = $this->container->get('youshopify.service');

    if ($form->isValid()) {


      $service->mapFormToProduct($form,$new_product);
    $collections= $service->getCollections(); //retrieve the collections shop
    $create=$service->create_update_Product($new_product,$collections[0]['id']); //
    $result_url.=$create['handle'];
    $request->getSession()->getFlashBag()->set("success",
      "Your product were created! Click <a href=\"$result_url\" target=\"_BLANK\">here </a> to display");

  }

  return $this->render('ShopifyAdminBundle:Default:product.html.twig',array('form' => $form->createView()));
}


public function sendAction(Request $request, $id){

  $result_url="http://frip-mint.myshopify.com//collections/frontpage/products/"; //result url
  $session = $request->getSession();
  //retrieve the selected product
  $products = $session->get('jotform_products');
  foreach($products as $product){
    if($product->id == $id){
      $product_to_edit = $product;
      break;
    }
  }
  //get YouShopify service
  $service = $this->container->get('youshopify.service');

  //init Form
  $form =$this->createForm(new ProductType(),$product_to_edit);
  $form->handleRequest($request);


  if ($form->isValid()) {
    
    
    $collections= $service->getCollections();
    $create=$service->create_update_Product($product_to_edit,$collections[0]['id']);
    $result_url.=$create['handle'];
    $request->getSession()->getFlashBag()->set("success",
      "Your product were created! Click <a href=\"$result_url\" target=\"_BLANK\">here </a> to display");
    
     return $this->redirect($this->generateUrl('submission_send',array('id'=>$id)));    
  }

  return $this->render('ShopifyAdminBundle:Default:product.html.twig',array('form' => $form->createView()));
}
}

```
3) Contribution
--------------------------------------
You Shop(ify) is a simple work-in-a-progress API. Feel free to contribute in adding functions to the ```YouShopifyService.php``` or demo samples pages.