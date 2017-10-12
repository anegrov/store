<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity\ProductCrossout;
use AppBundle\Service\RateHelper;

class CrossoutController extends Controller
{

    /**
     * List Action
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listAction(Request $request)
    {
        $terms = $request->query->all();
        // set defaults
        $terms['page'] = (isset($terms['page'])) ? $terms['page'] : 1;
        $terms['limit'] = (isset($terms['limit'])) ? $terms['limit'] : 10;

        $cookies = $request->cookies;
        if ( $cookies->has('limit-perpage') ) {
            $terms['limit'] = $cookies->get('limit-perpage');
        }

        /* @var $em \Doctrine\ORM\EntityManager */
        $em = $this->getDoctrine()->getEntityManager();
        $repository = $em->getRepository('AppBundle:ProductCrossout');
        $pagination = $repository->searchBy($terms, $this->get('knp_paginator'));

        //stats
        $stats = $repository->getStats();
        // total
        $total = $repository->getTotalBy($terms);

        $repository = $em->getRepository('AppBundle:Provider');
        $providers = $repository->findAll();

        // get USD rate
        $rateHelper = $this->get(RateHelper::class);
        if ( ! ($rate = $rateHelper->getRate('USD') ) ) {
            $this->addFlash('notice',
                array('status' => 'danger', 'message' => $rateHelper->getError())
            );
            return $this->redirectToRoute('app');
        }

        $strIds = $request->cookies->get('crossout-checked-ids', "");
        $ids = array_diff(explode(',', $strIds), ['']);

        return $this->render('AppBundle:crossout:list.html.twig', [
            'items'     => $pagination,
            'terms'     => $terms,
            'providers' => $providers,
            'stats'     => $stats,
            'total'     => $total,
            'rate'      => $rate,
            'ids'       => $ids
        ]);
    }

    /**
     * Crossout Action
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function crossoutAction(Request $request)
    {
        $ids = $request->get('ids');
        /* @var $em \Doctrine\ORM\EntityManager */
        $em = $this->getDoctrine()->getEntityManager();
        $repo = $em->getRepository('AppBundle:ProductSale');
        $products = $repo->findBy(['id'=>$ids]);

        // check qty
        $dbItem = [];
        if (!empty($products)) {
            foreach( $products as $product) {
                $dbItem[] = $repo->findOneBy(array("id" => $product->getId()));
            }
        }

        return $this->render('AppBundle:crossout:crossout.html.twig', [
            'items' => $products
        ]);
    }

    /**
     * Do Action
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function doAction(Request $request)
    {
        $products = $request->get('product', []);

        /* @var $em \Doctrine\ORM\EntityManager */
        $em = $this->getDoctrine()->getEntityManager();
        $repository = $em->getRepository('AppBundle:ProductSale');

        if (!empty($products)) {
            // validate
            foreach ($products as $productSaleId=>$item) {
                // check: product crossout is select?
                if (empty($item['crossout'])) {
                    $this->addFlash('notice',
                        array('status' => 'danger', 'message' => 'Не выбран продукт(ы) для списания!')
                    );
                    // find all product id which we want crossout
                    foreach ($products as $item) {$ids[] = $item['id'];}
                    return $this->redirectToRoute('app_crossout', ['ids[]' => $ids]);
                }

                // $productSaleId - product id which we want crossout
                // check: product in DB?
                $productSale = $repository->find($productSaleId);
                if (empty($productSale)) {
                    $this->addFlash('notice',
                        array('status' => 'danger', 'message' => 'Продукт (id:'.$productSaleId.') не найден!')
                    );
                    // find all product id which we want crossout
                    foreach ($products as $id=>$item) {$ids[] = $id;}
                    return $this->redirectToRoute('app_crossout', ['ids' => $ids]);
                }
                else {
                    // remember product
                    $products[$productSaleId]['productSale'] = $productSale;
                }

                // foreach product which we want replace instead $productSale
                foreach($item['crossout']['product_id'] as $key=>$productSaleCrossoutId) {
                    // check: product in DB?
                    $productSaleCrossout = $repository->find($productSaleCrossoutId);
                    if (empty($productSaleCrossout)) {
                        $this->addFlash('notice',
                            array('status' => 'danger', 'message' => 'Продукт (id:'.$productSaleCrossoutId.') не найден!')
                        );
                        // find all product id which we want crossout
                        foreach ($products as $id=>$item) {$ids[] = $id;}
                        return $this->redirectToRoute('app_crossout', ['ids' => $ids]);
                    }
                    else {
                        // remember product
                        $products[$productSaleId]['crossout']['productSale'] = $productSaleCrossout;
                    }
                }
            }

            // foreach product which we want crossout
            foreach ($products as $productSaleId=>$item) {
                // product which we want crossout
                $productSale = $repository->find($productSaleId);
                // foreach product which we want replace instead $productSale
                foreach($item['crossout']['product_id'] as $key=>$productId) {
                    $crossout = new ProductCrossout();

                    $crossout->setProductSaleCrossoutId($productSale->getId());
                    $crossout->setProductSaleCrossout($productSale);

                    // product which we want replace instead $productSale
                    $product = $repository->find($productId);

                    $crossout->setProductSaleId($product->getId());
                    $crossout->setProductSale($product);

                    $crossout->setQty($productSale->getQty());
                    $crossout->setPrice($item['crossout']['price'][$key]);
                    $date = \DateTime::createFromFormat('d-m-Y', $item['crossout']['date'][$key]);
                    $crossout->setDate($date);

                    $em->persist($crossout);
                    $em->flush();
                }
            }
        }
        else {
            $this->addFlash('notice',
                array('status' => 'danger', 'message' => 'Не выбран продукт(ы) для списания')
            );
            return $this->redirectToRoute('app_crossout');
        }
        $this->addFlash('notice',
            array('status' => 'success', 'message' => 'Все выбранные продукты успешно списаны!')
        );
        return $this->redirectToRoute('app_not_crossout_list');
    }
}
