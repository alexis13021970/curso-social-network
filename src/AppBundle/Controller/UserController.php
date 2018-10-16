<?php

namespace AppBundle\Controller;

use AppBundle\Form\RegisterType;
use AppBundle\Form\UserType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use BackendBundle\Entity\User;
use Symfony\Component\HttpFoundation\Session\Session;

class UserController extends Controller
{
    private $session;
    public function __construct()
    {
        $this->session = new Session();
    }

    public function loginAction(Request $request)
     {
         if (is_object($this->getUser())){
             return $this->redirect('home');
         }

         $authenticationUtils= $this->get('security.authentication_utils');
         $error = $authenticationUtils->getLastAuthenticationError();
         $lastUsername = $authenticationUtils->getLastUsername();
         return $this->render('AppBundle:User:login.html.twig', array(
             'lastUsername' => $lastUsername,
             'error' => $error));
     }

     public function registerAction(Request $request)
     {
         if (is_object($this->getUser())){
             return $this->redirect('home');
         }
         $user = new User;
         $form = $this->createForm(RegisterType::class, $user);
         $form->handleRequest($request);
         if ($form->isSubmitted())
         {
             if ($form->isValid())
             {
                 $em = $this->getDoctrine()->getManager();

                 $query = $em->createQuery('SELECT u FROM BackendBundle:User u WHERE u.email = :email OR u.nick = :nick')
                             ->setParameter('email',$form->get('email')->getData())
                             ->setParameter('nick',$form->get('nick')->getData());
                 $user_isset = $query->getResult();
                 if (count($user_isset) == 0){
                     $factory = $this->get("security.encoder_factory");
                     $encoder = $factory->getEncoder($user);
                     $password = $encoder->encodePassword($form->get('password')->getData(),$user->getSalt());

                     $user->setPassword($password);
                     $user->setRole('ROLE_USER');
                     $user->setImage(null);

                     $em->persist($user);
                     $flush = $em->flush();

                     if ($flush == null){
                         $status = 'Se ha registrado correctamente';
                         $this->session->getFlashBag()->add('status' ,$status);

                         return $this->redirect('login');
                     }else{
                         $status = 'No te has registrado correctamente';
                     }

                 }else{

                    $status = 'El usuario ya existe!!';
                     $this->session->getFlashBag()->add('status' ,$status);

                 }




             }else{
                 $status = 'No te has registrado correctamente!!';
                 $this->session->getFlashBag()->add('status' ,$status);
             }

         }

         return $this->render('AppBundle:User:register.html.twig',array(
                'form' => $form->createView()
         ));
     }

     public function nickTestAction(Request $request)
     {
          $nick = $request->get('nick');
          $em = $this->getDoctrine()->getManager();
          $use_repo = $em->getRepository('BackendBundle:User')->findOneBy(array(
               'nick' => $nick
          ));
          var_dunp($use_repo);
          $result = 'used';
          if (count($use_repo) >= 1 && is_object($use_repo)){
              $result = 'used';
          } else{
              $result = 'unused';
          }

          return new Response($result);
     }

    /**
     * @param Request $request
     * @return Response
     */
    public function editUserAction(Request $request)
     {
           $user = $this->getUser();
           $user_image = $user->getImage();
           $form = $this->createForm(UserType::class,$user);


         $form->handleRequest($request);
         if ($form->isSubmitted())
         {
             if ($form->isValid())
             {
                 $em = $this->getDoctrine()->getManager();

                 $query = $em->createQuery('SELECT u FROM BackendBundle:User u WHERE u.email = :email OR u.nick = :nick')
                     ->setParameter('email',$form->get('email')->getData())
                     ->setParameter('nick',$form->get('nick')->getData());
                 $user_isset = $query->getSingleResult();
                 if ( count($user_isset) == 0 || $user->getEmail() == $user_isset->getEmail() && $user->getNick() == $user_isset->getNick() ){

                     //upload file

                     $file = $form['image']->getData();
                     if (!empty($file) && $file != null){
                         $ext = $file->guessExtension();
                         if ($ext == 'jpg' || $ext == 'jpeg' || $ext == 'png' || $text == 'gif' ){
                             $filename = $user->getId().time() . '.' . $ext;
                             $file->move('uploads/users',$filename);
                             $user->setImage($filename);
                         }

                     }else{
                         $user->setImage($user_image);
                     }

                     $em->persist($user);
                     $flush = $em->flush();

                     if ($flush == null){
                         $status = 'Has modificado tus datos correctamente';
                     }else{
                         $status = 'No se han modificado tus datos correctamente';
                     }

                 }else{

                     $status = 'El usuario no ya existe!!';
                     $this->session->getFlashBag()->add('status' ,$status);

                 }




             }else{
                 $status = 'No se han actualizado sus datos correctamente!!';
                 $this->session->getFlashBag()->add('status' ,$status);
             }
             $this->session->getFlashBag()->add("status",$status);
             $this->redirect('my-data');
         }

           return $this->render('AppBundle:User:edit_user.html.twig',array(
               'form' => $form->createView()

           ));


     }
     public function usersAction(Request $request)
     {
         $em = $this->getDoctrine()->getManager();
         $dql = "SELECT u FROM BackendBundle:User u ORDER BY u.id";

         $query = $em->createQuery($dql);

         $paginator = $this->get('knp_paginator');
         $pagination = $paginator->paginate($query,$request->query->getInt('page',1),5);

         return $this->render('AppBundle:User:users.html.twig',array(
               'pagination' => $pagination
         ));
     }

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function searchAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $search = trim($request->query->get('search',null));

        if ($search == null){
             return $this->redirect($this->generateUrl('home'));
        }
        $dql = "SELECT u FROM BackendBundle:User u"
               ." WHERE u.name LIKE :search OR u.surname LIKE :search"
               ." OR u.nick LIKE :search ORDER BY u.id";

        $query = $em->createQuery($dql)->setParameter('search',"%$search%" );

        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate($query,$request->query->getInt('page',1),5);

        return $this->render('AppBundle:User:users.html.twig',array(
            'pagination' => $pagination
        ));
    }

    /**
     * @param Request $request
     * @param null $nickname
     */
    public function profileAction(Request $request, $nickname = null)
    {
        $em = $this->getDoctrine()->getManager();
        if ($nickname != null)
        {
            $user_repo = $em->getRepository('BackendBundle:User');
            $user = $user_repo->findOneBy(array('nick' => $nickname));
        }else{
            $user = $this->getUser();
        }

        if (empty($user) && !is_object($user))
        {
            $this->redirect($this->generateUrl('home'));
        }
        $user_id = $user->getId();
        $dql = "SELECT p FROM BackendBundle:Publication p WHERE p.user = $user_id ORDER BY p.id DESC";
        $query = $em->createQuery($dql);

        $paginator = $this->get('knp_paginator');
        $publications = $paginator->paginate($query, $request->query->getInt('page',1),5);

        return $this->render('AppBundle:User:profile.html.twig',array(
              'user' => $user,
              'pagination' => $publications
        ));

    }
}