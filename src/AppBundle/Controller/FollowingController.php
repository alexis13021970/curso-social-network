<?php

namespace AppBundle\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use BackendBundle\Entity\User;
use BackendBundle\Entity\Following;
use Symfony\Component\HttpFoundation\Session\Session;


class FollowingController extends Controller
{
    private $session;
    public function __construct()
    {
        $this->session = new Session();
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function followAction(Request $request)
     {
          $logger = $this->get('logger');
          $user = $this->getUser();
          $followed_id = $request->get('followed');
         $logger->info('xxxx');
         $logger->info($followed_id);





          $em = $this->getDoctrine()->getManager();
          $user_repo = $em->getRepository('BackendBundle:User');
          $followed = $user_repo->find($followed_id);
         $logger->info('yyyy');
         $logger->info($followed);

          $following = new Following();
          $following->setUser($user);
          $following->setFollowed($followed);

          $em->persist($following);
          $flush =$em->flush();

          if ($flush==null){
              $status = "Ahora estas siguiendo a este usuario!!";
          } else{
              $status = "no se ha podido seguir a este usuario!!";
          }

          return new Response($status);


     }

    /**
     * @param Request $request
     * @return Response
     */
    public function unfollowAction(Request $request)
    {

        $logger = $this->get('logger');
        $user = $this->getUser();
        $followed_id = $request->get('unfollowed');


        $em = $this->getDoctrine()->getManager();
        $follow_repo = $em->getRepository('BackendBundle:Following');
        $following = $follow_repo->findOneBy(array(
               'user' => $user,
               'followed' => $followed_id
        ));





        $em->remove($following);
        $flush = $em->flush();

        if ($flush==null){
            $status = "Has dejado de seguir a este usuario!!";
        } else{
            $status = "no se ha podido dejar de seguir a este usuario!!";
        }

        return new Response($status);
    }

    public function followingAction(Request $request, $nicname = null)
    {
         $em = $this->getDoctrine()->getManager();
         $repo_user = $em->getRepository('BackendBundle:User');
         if ($nicname != null)
         {
             $user = $repo_user->findBy(array('nick' => $nicname));
         }else{
             $user = $this->getUser();
         }
         if (empty($user) || !is_object($user))
         {
             return $this->redirect($this->generateUrl('home'));

         }

         $user_id = $user->getId();
         $dql ="SELECT f FROM BackendBundle:Following f WHERE f.user = $user_id ORDER BY f.user DESC";

         $query = $em->createQuery($dql);

         $paginator = $this->get('knp_paginator');
         $following = $paginator->paginate($query,$request->query->getInt('page',1),5);

         return $this->render('AppBundle:Following:following.html.twig',array(
               'type' => 'following',
               'profile_user' => $user,
               'pagination' => $following
         ));

    }



}