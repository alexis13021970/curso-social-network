<?php
/**
 * Created by PhpStorm.
 * User: Alexis Brito
 * Date: 18/10/2018
 * Time: 22:36
 */

namespace BackendBundle\Repository;
use Doctrine\ORM\EntityRepository;

class UserRepository extends EntityRepository
{
    /**
     * @param $user
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getFollowingUsers($user){
          $em = $this->getEntityManager();
          $following_repo = $em->getRepository('BackendBundle:Following');
          $following = $following_repo->findBy(array(
              'user' => $user
          ));

          $following_array = array();
          foreach($following as $follow){
               $following_array[] = $follow->getFollowed();
          }

          $user_repo = $em->getRepository('BackendBundle:User');
          $users = $user_repo->createQueryBuilder('u')
                            ->where('u.id != :user')
                            ->andWhere('u.id IN (:following)')
                            ->setParameter('user', $user->getId())
                            ->setParameter('following',$following_array)
                            ->orderBy('u.id','DESC');

          return $users;

      }
}