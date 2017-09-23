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
            new \Twig_SimpleFilter('usort', array($this, 'usortFilter'))
        ];
    }

    public function usortFilter($items, $sortRule)
    {
        $values = $items->toArray();

        usort($values, function ($a, $b) use ($sortRule) {
            if ($a->getCreated() == $b->getCreated()) return 0;
            if ($sortRule === 'ASC'){
                return $a->getCreated() < $b->getCreated() ? -1 : 1;
            } else {
                return $a->getCreated() > $b->getCreated() ? -1 : 1;
            }
        });

        return $values;
    }


}