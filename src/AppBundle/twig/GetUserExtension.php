<?php
/**
 * Created by PhpStorm.
 * User: Alexis Brito
 * Date: 18/10/2018
 * Time: 9:52
 */

namespace AppBundle\Twig;
use Symfony\Bridge\Doctrine\RegistryInterface;


class GetUserExtension extends \Twig_Extension
{
    private $doctrine;
    public function __construct(RegistryInterface $doctrine)
    {
         $this->doctrine = $doctrine;
    }

    /**
     * @return array|\Twig_SimpleFilter[]
     */
    public function getFilters()
    {
          return array(
              new \Twig_SimpleFilter('get_user',array($this,'getUserFilter'))
          );

    }

    /**
     * @param $user_id
     */
    public function getUserFilter($user_id)
    {
          $user_repo = $this->doctrine->getRepository('BackendBundle:User');
          $user = $user_repo->findOneBy(array(
               'id' => $user_id
          ));

          if (!empty($user) && is_object($user))
          {
              $result = $user;
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
        return 'get_user_extension';
    }

}