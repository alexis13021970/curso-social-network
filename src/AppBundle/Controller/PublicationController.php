<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Form\PublicationType;
use BackendBundle\Entity\Publication;
use Symfony\Component\HttpFoundation\Session\Session;

class PublicationController extends Controller
{
    private $session;
    public function __construct()
    {
        $this->session = new Session();
    }

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(Request $request)
    {

        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $publication = new Publication();
        $form = $this->createForm(PublicationType::class,$publication);
        $form->handleRequest($request);



        if ($form->isSubmitted())
        {
            if ($form->isValid())
            {
                //upload file
                $file = $form['image']->getData();
                if (!empty($file) && $file !=null)
                {
                    $ext = $file->guessExtension();

                    if ($ext == 'jpg' || $ext == 'jpeg' || $ext == 'png' || $ext == 'gif' )
                    {
                        $file_name = $user->getId().time(). "." . $ext;

                        $file->move('uploads/publications/images',$file_name);
                        $publication->setImage($file_name);
                    }else
                    {
                        $publication->setImage(null);
                    }
                }else
                {
                    $publication->setImage(null);
                }
                //upload Document
                $doc = $form['document']->getData();
                if (!empty($doc) && $doc !=null)
                {
                    $ext = $doc->guessExtension();
                    if ($ext == 'pdf')
                    {
                        $file_name = $user->getId().time(). "." . $ext;

                        $doc->move('uploads/publications/documents',$file_name);
                        $publication->setDocument($file_name);
                    }else
                    {
                        $publication->setDocument(null);
                    }
                }else
                {
                    $publication->setDocument(null);
                }
                $publication->setUser($user);
                $publication->setCreatedAt(new \DateTime('now'));

                $em->persist($publication);
                $flush = $em->flush();
                if ($flush == null)
                {
                    $status ='La publicación se ha creado correctamente!!';
                }else
                {
                    $status ='Error al añadir la publicación';
                }
            }else
            {
                  $status = 'La publicación no se ha creado porque el formulario no es válido';
            }
            $this->session->getFlashBag()->add('status',$status);
            return $this->redirectToRoute('home');


        }

        $publications = $this->getPublication($request);


        return $this->render('AppBundle:Publication:home.html.twig',array(
             'form' => $form->createView(),
             'pagination' => $publications

        ));

    }

    /**
     * @param $request
     */
    public function getPublication($request)
    {
             $user = $this->getUser();
             $em = $this->getDoctrine()->getManager();
             $publications_repo = $em->getRepository('BackendBundle:Publication');
             $following_repo = $em->getRepository('BackendBundle:Following');

             $following = $following_repo->findBy(array(
                   'user' => $user
             ));
             $following_array = array();
             foreach ($following as $follow){
                 $following_array[] = $follow->getFollowed();
             }

             $query = $publications_repo->createQueryBuilder('p')
                    ->where('p.user = :user_id')
                    ->orWhere('p.user IN (:following)')
                    ->orderBy('p.user','desc')
                    ->addOrderBy('p.createdAt','desc')
                    ->setParameter('user_id',$user->getId())
                    ->setParameter('following', $following_array)
                    ->getQuery();

              $paginator = $this->get('knp_paginator');
              $pagination = $paginator->paginate($query,$request->query->getInt('page',1),5);

              return $pagination;

    }
}