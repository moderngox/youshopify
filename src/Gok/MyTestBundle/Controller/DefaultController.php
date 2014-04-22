<?php

namespace Gok\MyTestBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('GokMyTestBundle:Default:index.html.twig');
    }
}
