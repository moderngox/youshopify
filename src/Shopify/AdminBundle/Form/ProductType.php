<?php

namespace Shopify\AdminBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ProductType extends AbstractType
{

	public function __construct($id="",$title="",$type="type",$description="",$image="",$price="",$vendor=""){

		
		$this->title = $title;
		$this->type = $type;
		$this->description = $description;
		$this->image = $image;
		$this->price= $price;
		$this->vendor= $vendor;
  	
	}

	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder->add('title', 'text',array('required'=>true));
		$builder->add('type', 'choice', array(
			'choices' => array('tee'=>'Tee',' ebook' => 'Ebook', 'jeans' => 'Jeans', 'sneakers' => 'Sneakers', 'hoodie' => 'Hoodie'),
			'preferred_choices' => array('ebook')));
		$builder->add('description', 'textarea',array('required'=>true));
		$builder->add('price', 'text',array('attr' => array('input_group' => array('prepend' => '€', 'append' => '.00', 'size' => 'small'))));
		$builder->add('image', 'text',array('required'=>true,'label' => 'Image URL'));
		$builder->add('vendor', 'text',array('required'=>true));
		//$builder->add('Save', 'submit');
		//$builder->add('Cancel', 'submit');
		$builder ->add('actions', 'form_actions', [
        'buttons' => [
            'save_button' => ['type' => 'submit', 'options' => ['label' => 'Save']],
            'cancel_button' => ['type' => 'button', 'options' => ['label' => 'Cancel']]
        ]
    ]); 
		
	}



	public function getName()
	{
		return 'product';
	}
}
