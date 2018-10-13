<?php
/**
 * Created by PhpStorm.
 * User: Alexis Brito
 * Date: 13/10/2018
 * Time: 16:00
 */

namespace AppBundle\twig;


class LongTimeExtension extends \Twig_Extension
{
     public function getFilters()
     {
         return array(
             new \Twig_SimpleFilter('long_time',array($his,'LongTimeFilter'))
         );

     }

     public function LongTimeFilter()
     {
         return $result;
     }

     public function getName(){
         return 'LongTimeFilter';
     }
}