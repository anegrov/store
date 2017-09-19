<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace AppBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use AppBundle\Entity\Bunch;
use AppBundle\Entity\Provider;
use AppBundle\Entity\ProductStock;

/**
 * Description of LoadProviderData
 *
 * @author anton
 */
class LoadStockData implements FixtureInterface
{
    /**
     * load
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        /* start: create prociders */
//        $providerTest = new Provider();
//        $providerTest->setTitle('ЗАО TEST');
//
//        $manager->persist($providerTest);
//        $manager->flush();
//
//        $providerTest = new Provider();
//        $providerTest->setTitle('ЗАО TEST2');
//
//        $manager->persist($providerTest);
//        $manager->flush();
//
//        $providerTest = new Provider();
//        $providerTest->setTitle('ЗАО TEST3');
//
//        $manager->persist($providerTest);
//        $manager->flush();
//
//        /* end: create prociders */
//
//        /* start: create products */
//        $bunchTest = new Bunch();
//        $bunchTest->setTitle('7281 Т-соединения и изогнутые рельсы/ЧЕШСКАЯ РЕСПУБЛИКА');
//        $bunchTest->setAvailability(true);
//        $bunchTest->setStar1(false);
//        $bunchTest->setStar2(false);
//        $bunchTest->setStar3(false);
//
//        $manager->persist($bunchTest);
//        $manager->flush();
//
//        $productStockTest = new ProductStock();
//        $productStockTest->setTitle('7281 Т-соединения и изогнутые рельсы/ЧЕШСКАЯ РЕСПУБЛИКА');
//        $productStockTest->setBunchId($bunchTest->getId());
//        $productStockTest->setBunch($bunchTest);
//        $productStockTest->setProviderId($providerTest->getId());
//        $productStockTest->setPrice('22');
//        $productStockTest->setPriceByn('42');
//        $productStockTest->setSelfcost('42');
//        $productStockTest->setQty(10);
//        $productStockTest->setNds(10);
//        $productStockTest->setWeight(10);
//        $productStockTest->setStar1(false);
//        $productStockTest->setStar2(false);
//        $productStockTest->setStar3(false);
//        $productStockTest->setWeightQty('234,45');
//        $productStockTest->setDocuments(1);
//
//        $manager->persist($productStockTest);
//        $manager->flush();
//
//        $bunchTest = new Bunch();
//        $bunchTest->setTitle('B2834 DOHVINCI Набор "Ваза дизайнера"/КИТАЙ');
//        $bunchTest->setAvailability(true);
//        $bunchTest->setStar1(false);
//        $bunchTest->setStar2(false);
//        $bunchTest->setStar3(false);
//
//        $manager->persist($bunchTest);
//        $manager->flush();
//
//        $productStockTest = new ProductStock();
//        $productStockTest->setTitle('B2834 DOHVINCI Набор "Ваза дизайнера"/КИТАЙ');
//        $productStockTest->setBunchId($bunchTest->getId());
//        $productStockTest->setBunch($bunchTest);
//        $productStockTest->setProviderId($providerTest->getId());
//        $productStockTest->setPrice('22');
//        $productStockTest->setPriceByn('42');
//        $productStockTest->setSelfcost('42');
//        $productStockTest->setQty(10);
//        $productStockTest->setNds(10);
//        $productStockTest->setWeight(10);
//        $productStockTest->setStar1(false);
//        $productStockTest->setStar2(false);
//        $productStockTest->setStar3(false);
//        $productStockTest->setWeightQty(234);
//        $productStockTest->setDocuments(1);
//
//        $manager->persist($productStockTest);
//        $manager->flush();

        /* end: create products */

    }
}
