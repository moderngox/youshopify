<?php
namespace Shopify\AdminBundle\Beans;

class Product
{
	public function __construct($id="",$title="",$type="",$description="",$image=""){

		$this->$id = $id;
		$this->title = $title;
		$this->type = $type;
		$this->description = $description;
		$this->image = $image;
  	
	}

	public function displayProduct(){

		var_dump($this);

	}

	public function getTitle(){
		return $this->title;
	}
	
	public function getType(){
		return $this->type;
	}

	public function getDescripton(){
		return $this->description;
	}

	public function getImage(){
		return $this->image;
	}
}

?>