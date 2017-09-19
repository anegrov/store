<?php

namespace AppBundle\EventListener;

use AppBundle\Entity\ProductSale;
use Doctrine\ORM\Event\LifecycleEventArgs;
use AppBundle\Entity\ProductStock;

class ProductListener
{
    /**
     * postPersist
     *
     * @param LifecycleEventArgs $args
     */
    public function postPersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        $em = $args->getEntityManager();
        if ($entity instanceof ProductStock)
        {
            $bunch = $entity->getBunch();

            $bunchRepository = $em->getRepository('AppBundle:Bunch');
            $bunchRepository->recalcTotalFor($bunch);
        }

        if ($entity instanceof ProductSale) {
            $qty = $entity->getQty();
            $product = $entity->getProduct();
            $product->setQty( $product->getQty() - $qty );
            $bunch = $product->getBunch();
            $bunch->setTotal( $bunch->getTotal() - $qty );

            $em->persist($product);
            $em->persist($bunch);

            $em->flush();
        }
    }
}