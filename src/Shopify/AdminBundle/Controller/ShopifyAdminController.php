<?php

namespace Shopify\AdminBundle\Controller;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Shopify\AdminBundle\Form\ProductType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

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
