<?php
/**
 * Created by PhpStorm.
 * User: maste
 * Date: 23.09.2017
 * Time: 14:55
 */

namespace AppBundle\Twig;


class AppExtension extends \Twig_Extension
{
    public function getFilters()
    {
        return [
//            new \Twig_SimpleFilter('usort', array($this, 'usortFilter'))
        ];
    }

    public function usortFilter($item)
    {
//        usort($item, function ($item1, $item2) {
//            if ($item1['created'] == $item2['created']) return 0;
//            return $item1['created'] < $item2['created'] ? -1 : 1;
//        });
//
//        return $item;
    }


}