<?php

namespace AppBundle\Repository;

/**
 * ProviderRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class ProviderRepository extends \Doctrine\ORM\EntityRepository
{
    public function search($terms)
    {
        $qb = $this->createQueryBuilder('p')
            ->select('p')
            ->orderBy('p.title', 'ASC');

        if (!empty($terms['title'])) {
            $qb->andWhere('n.title LIKE :title');
            $qb->setParameter('title', '%'.$terms['title'].'%');
        }

        return $qb->getQuery()->getResult();
    }
}
