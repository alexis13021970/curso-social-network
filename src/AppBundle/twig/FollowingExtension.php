<?php
/**
 * Created by PhpStorm.
 * User: Alexis Brito
 * Date: 12/10/2018
 * Time: 9:53
 */

namespace AppBundle\Twig;


use Symfony\Bridge\Doctrine\RegistryInterface;

class FollowingExtension extends \Twig_Extension
{
       protected  $doctrine;
       public function __construct(RegistryInterface $doctrine)
       {
           $this->doctrine = $doctrine;
       }

    /**
     * @return array
     */
    public function getFilters()
       {
           return array(
               new \Twig_SimpleFilter('following',array($this,"followingFilter"))

           );
       }

    /**
     * @param $user
     * @param $fallowed
     */
    public function followingFilter($user, $followed)
       {
           $following_repo = $this->doctrine->getRepository('BackendBundle:Following')
                                            ->findOneBy(array(
                                                'user' => $user,
                                                'followed'=> $followed
                                            ));

           if (!empty($following_repo) && is_object($following_repo))
           {
               $result = true;
           }else{
               $result = false;
           }
           return $result;
       }

    /**
     * @return string
     */
    public function getName()
       {
           return "following_extension";
       }
}