<?php

namespace AppBundle\Controller;


use AppBundle\Form\PrivateMessageType;
use BackendBundle\Entity\PrivateMessage;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;

class PrivateMessageController extends Controller
{

    private $session;

    /**
     * PrivateMessageController constructor.
     */
    public function __construct()
    {

        $this->session = new Session();
    }

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(Request $request)
    {

        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();

        $private_message = new PrivateMessage();
        $form = $this->createForm(PrivateMessageType::class, $private_message, array(
            'empty_data' => $user
        ));
        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                //upload file
                $file = $form['image']->getData();
                if (!empty($file) && $file != null) {
                    $ext = $file->guessExtension();

                    if ($ext == 'jpg' || $ext == 'jpeg' || $ext == 'png' || $ext == 'gif') {
                        $file_name = $user->getId() . time() . "." . $ext;

                        $file->move('uploads/messages/images', $file_name);
                        $private_message->setImage($file_name);
                    } else {
                        $private_message->setImage(null);
                    }
                } else {
                    $private_message->setImage(null);
                }
                //upload  File
                $doc = $form['file']->getData();
                if (!empty($doc) && $doc != null) {
                    $ext = $doc->guessExtension();
                    if ($ext == 'pdf') {
                        $file_name = $user->getId() . time() . "." . $ext;

                        $doc->move('uploads/messages/documents', $file_name);
                        $private_message->setFile($file_name);
                    } else {
                        $private_message->setFile(null);
                    }
                } else {
                    $private_message->setFile(null);
                }
                $private_message->setEmiter($user);
                $private_message->setCreatedAt(new \DateTime('now'));
                $private_message->setReaded(0);

                $em->persist($private_message);
                $flush = $em->flush();
                if ($flush == null){
                    $status = "Mensaje privado enviado correctamente";
                }else{
                    $status = "Mensaje privado no se ha enviado ";
                }
            } else {
                $status = "Mensaje privado no se ha enviado ";
            }
            $this->session->getFlashBag()->add('status',$status);
            return $this->redirectToRoute('private_message_index');

        }
        $private_messages = $this->getPrivateMessages($request);
        return $this->render('AppBundle:PrivateMessage:index.html.twig', [
            'form' => $form->createView()

        ]);
    }

    /**
     * @param Request $request
     */
    public function sendedAction(Request $request)
    {
          $private_messages = $this->getPrivateMessages($request, "sended");

          return $this->render('AppBundle:PrivateMessage:send.html.twig',array(
                 'pagination' => $private_messages
          ));


    }
    public function getPrivateMessages($request , $type = null){
         $em = $this->getDoctrine()->getManager();
         $user = $this->getUser();
         $user_id = $user->getId();

         if ($type = "sended"){
             $dql = "SELECT p FROM BackendBundle:PrivateMessage p WHERE p.emiter = $user_id ORDER BY p.id DESC";
         }else{
             $dql = "SELECT p FROM BackendBundle:PrivateMessage p WHERE p.receiver = $user_id ORDER BY p.id DESC";
         }

         $query = $em->createQuery($dql);
         $paginator = $this->get('knp_paginator');
         $paginacion = $paginator->paginate($query, $request->query->getInt('page',1),5);

         return $paginacion;

    }

}
