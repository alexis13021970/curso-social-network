<?php

namespace BackendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
        $user_report = $em->getRepository('BackendBundle:User')->find(1);
        echo 'USUARIO: ' . $user_report->getName() . ' ' . $user_report->getEmail();
        var_dump($user_report);
        die();
        return $this->render('BackendBundle:Default:index.html.twig');
    }
}
