<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Form\PublicationType;
use BackendBundle\Entity\Publication;
use BackendBundle\Entity\User;
use BackendBundle\Entity\Like;


class LikeController extends Controller
{

    public function likeAction($id = null)
    {
        $user = $this->getUser();
        $em = $this->getDoctrine()->getManager();
        $publication_repo = $em->getRepository('BackendBundle:Publication');
        $publication = $publication_repo->find($id);

        $like = new Like();
        $like->setUser($user);
        $like->setPublication($publication);

        $em->persist($like);
        $flush = $em->flush();
        if ($flush == null)
        {
            $status = "Te gusta esta publicación";
        }else{
            $status = "No se ha podido guardar el me gusta";
        }
        return new Response($status);
    }

    public function unlikeAction($id = null)
    {
         $user = $this->getUser();
         $em = $this->getDoctrine()->getManager();

         $like_repo = $em->getRepository('BackendBundle:Like');
         $like = $like_repo->findOneBy(array(
              'user' => $user,
              'publication' => $id
         ));

         $em->remove($like);
         $flush = $em->flush();
         if ($flush == null)
         {
             $status = "Ya no te gusta esta publicación";
         }else{
             $status = "No se ha podido desmarcar el me gusta";

         }

         return new Response($status);
    }

    public function likesAction(Request $request, $nicname = null)
    {
        $em = $this->getDoctrine()->getManager();
        $repo_user = $em->getRepository('BackendBundle:User');
        if ($nicname != null) {
            $user = $repo_user->findBy(array('nick' => $nicname));
        } else {
            $user = $this->getUser();
        }
        if (empty($user) || !is_object($user)) {
            return $this->redirect($this->generateUrl('home'));

        }

        $user_id = $user->getId();
        $dql = "SELECT l FROM BackendBundle:Like l WHERE l.user = $user_id ORDER BY l.user DESC";

        $query = $em->createQuery($dql);

        $paginator = $this->get('knp_paginator');
        $likes = $paginator->paginate($query, $request->query->getInt('page', 1), 5);

        return $this->render('AppBundle:Like:likes.html.twig', array(
            'user' => $user,
            'pagination' => $likes
        ));

    }

}