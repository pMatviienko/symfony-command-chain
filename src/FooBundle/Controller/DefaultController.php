<?php

namespace FooBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('FooBundle:Default:index.html.twig');
    }
}
