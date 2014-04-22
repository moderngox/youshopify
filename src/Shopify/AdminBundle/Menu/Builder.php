<?php
// src/Acme/DemoBundle/Menu/Builder.php
namespace Shopify\AdminBundle\Menu;

use Knp\Menu\FactoryInterface;
use Symfony\Component\DependencyInjection\ContainerAware;
use Knp\Menu\Renderer\ListRenderer; 


use Knp\Menu\Matcher\Matcher;

class Builder extends ContainerAware
{
    public function mainMenu(FactoryInterface $factory, array $options)
    {
        $menu = $factory->createItem('root');

        //$menu->addChild('Home', array('route' => 'index'));
        //$menu->addChild('About', array('route' => 'about'));
        $menu->addChild('JotForm-Shop(ify)', array('route' => 'jotform_submissions'));
        $menu->addChild('Add-an-item', array('route' => 'add_demo'));
        $menu->addChild('The demo shop', array('uri' => 'http://frip-mint.myshopify.com/'))->setLinkAttributes(array('target' => '_blank'));
       

        $renderer = new ListRenderer(new Matcher());
        return $menu;
    }
}

?>