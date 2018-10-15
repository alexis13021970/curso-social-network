<?php
/**
 * Created by PhpStorm.
 * User: Alexis Brito
 * Date: 12/10/2018
 * Time: 9:53
 */

namespace AppBundle\Twig;


use Symfony\Bridge\Doctrine\RegistryInterface;

class LikedExtension extends \Twig_Extension
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
               new \Twig_SimpleFilter('liked',array($this,"likedFilter"))

           );
       }

    /**
     * @param $user
     * @param $fallowed
     */
    public function likedFilter($user, $publication)
       {
           $like_repo = $this->doctrine->getRepository('BackendBundle:Like')
                                            ->findOneBy(array(
                                                'user' => $user,
                                                'publication'=> $publication
                                            ));

           if (!empty($like_repo) && is_object($like_repo))
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
           return "liked_extension";
       }
}