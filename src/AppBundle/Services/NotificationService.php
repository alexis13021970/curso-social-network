<?php
/**
 * Created by PhpStorm.
 * User: Alexis Brito
 * Date: 18/10/2018
 * Time: 6:06
 */

namespace AppBundle\Services;
use BackendBundle\Entity\Notification;


class NotificationService
{
        public $manager;
        public function __construct($manager)
        {
             $this->manager = $manager;
        }

    /**
     * @param $user
     * @param $type
     * @param $tipeId
     */
    public function set($user, $type, $tipeId, $extra = null)
        {
             $em = $this->manager;

             $notification = new Notification();
             $notification->setUser($user);
             $notification->setType($type);
             $notification->setTypeId($tipeId);
             $notification->setReaded(0);
             $notification->setCreatedAt(new \DateTime('now'));
             $notification->setExtra($extra);

             $em->persist($notification);
             $flush = $em->flush();

            $status = $flush == null ? true : false;
             return $status;
        }

}