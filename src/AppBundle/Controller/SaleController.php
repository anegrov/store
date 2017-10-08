<?php

namespace AppBundle\Controller;

use AppBundle\Entity\ProductStock;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity\ProductSale;
use AppBundle\Service\RateHelper;

class SaleController extends Controller
{
    /**
     * Index Action
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(Request $request)
    {
        $terms = $request->query->all();
        // set defaults
        $terms['page'] = (isset($terms['page'])) ? $terms['page'] : 1;
        $terms['limit'] = (isset($terms['limit'])) ? $terms['limit'] : 10;

        $cookies = $request->cookies;
        if ($cookies->has('limit-perpage')) {
            $terms['limit'] = $cookies->get('limit-perpage');
        }

        /* @var $em \Doctrine\ORM\EntityManager */
        $em = $this->getDoctrine()->getEntityManager();

        // get USD rate
        $rateHelper = $this->get(RateHelper::class);
        if (!($rate = $rateHelper->getRate('USD'))) {
            $this->addFlash('notice',
                array('status' => 'danger', 'message' => $rateHelper->getError())
            );
            return $this->redirectToRoute('app');
        } else {
            $terms['rate'] = $rate;
        }

        $repository = $em->getRepository('AppBundle:ProductSale');
        $pagination = $repository->searchBy($terms, $this->get('knp_paginator'));

        // stats
        $stats = $repository->getStats($rate);
        // total
        $total = $repository->getTotalBy($terms);

        $repository = $em->getRepository('AppBundle:Provider');
        $providers = $repository->findAll();

        $strIds = $request->cookies->get('sale-checked-ids', "");
        $ids = array_diff(explode(',', $strIds), ['']);

        return $this->render('AppBundle:sale:index.html.twig', [
            'items' => $pagination,
            'terms' => $terms,
            'providers' => $providers,
            'stats' => $stats,
            'total' => $total,
            'rate' => $rate,
            'ids' => $ids
        ]);
    }

    /**
     * selectProductsAction
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function selectProductsAction(Request $request)
    {
        $ids = $request->get('ids', []);
        $qty = $request->get('qty', []);
        $price = $request->get('price', []);
        $date = $request->get('date', []);

        /* @var $em \Doctrine\ORM\EntityManager */
        $em = $this->getDoctrine()->getEntityManager();

        $repo = $em->getRepository('AppBundle:ProductStock');
        $results = $repo->findBy(['id' => $ids]);

        $products = [];
        // clone products
        foreach ($ids as $key => $id) {
            foreach ($results as $item) {
                if ($item->getId() == $id) {
                    if ($item->getQty() == 0) {
                        $this->addFlash('notice',
                            array('status' => 'danger', 'message' => 'Продукт "' . $item->getTitle() . '" отсутствует на складе!')
                        );
                        return $this->redirectToRoute('app');
                    }

                    if ($item->getQty() < $qty[$key]) {
                        $this->addFlash('notice',
                            array('status' => 'danger', 'message' => 'Количество превышает допустимое для продукта (' . $item->getTitle() . ')')
                        );
                        return $this->redirectToRoute('app');
                    }
                    $products[] = $item;
                }
            }
        }

        if (empty($products)) {
            $this->addFlash('notice',
                array('status' => 'danger', 'message' => 'Не выбранно ни одного продукта!')
            );
            return $this->redirectToRoute('app');
        }

        // get USD rate
        $rateHelper = $this->get(RateHelper::class);
        if (!($rate = $rateHelper->getRate('USD'))) {
            $this->addFlash('notice',
                array('status' => 'danger', 'message' => $rateHelper->getError())
            );
            return $this->redirectToRoute('app');
        }

        return $this->render('AppBundle:sale:sale.html.twig', [
            'items' => $products,
            'ids' => $ids,
            'qty' => $qty,
            'price' => $price,
            'date' => $date,
            'rate' => $rate
        ]);

    }

    /**
     * Sale Action
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function saleAction(Request $request)
    {
        $ids = $request->get('id');
        $qty = $request->get('qty');
        $price = $request->get('price');
        $date = $request->get('date');
        $redirect = $request->get('redirect');

        /* @var $em \Doctrine\ORM\EntityManager */
        $em = $this->getDoctrine()->getEntityManager();
        $products = [];

        // validate
        foreach ($ids as $key => $id) {
            if (empty($qty[$key])) {
                $this->addFlash('notice',
                    array('status' => 'danger', 'message' => 'Не выбранно количество для продукта!')
                );
                return $this->redirectToRoute('app_product_check_sale', ['ids' => $ids, 'qty' => $qty, 'price' => $price, 'date' => $date]);
            } else {
                // check available quantity
                $product = $em->getRepository('AppBundle:ProductStock')
                    ->find($id);
                if ($product->getQty() < $qty[$key]) {
                    $this->addFlash('notice',
                        array('status' => 'danger', 'message' => 'Количество превышает допустимое для продукта (' . $product->getTitle() . ')')
                    );
                    return $this->redirectToRoute('app_product_check_sale', ['ids' => $ids, 'qty' => $qty, 'price' => $price, 'date' => $date]);
                } else {
                    // check all products, if products with equal id then sum quantity
                    $allQty = 0;
                    foreach ($ids as $k => $v) {
                        if ($v == $id) {
                            $allQty += $qty[$k];
                        }
                    }
                    if ($allQty > $product->getQty()) {
                        $this->addFlash('notice',
                            array('status' => 'danger', 'message' => 'Количество превышает допустимое для продукта (' . $product->getTitle() . ')')
                        );
                        return $this->redirectToRoute('app_product_check_sale', ['ids' => $ids, 'qty' => $qty, 'price' => $price, 'date' => $date]);
                    }
                }
                $products[$id] = $product;
            }
            if (empty($date[$key])) {
                $this->addFlash('notice',
                    array('status' => 'danger', 'message' => 'Не выбранна дата для продукта!')
                );
                return $this->redirectToRoute('app_product_check_sale', ['ids' => $ids, 'qty' => $qty, 'price' => $price, 'date' => $date]);
            }
            if (empty($price[$key])) {
                $this->addFlash('notice',
                    array('status' => 'danger', 'message' => 'Не выбранна цена продажи для продукта!')
                );
                return $this->redirectToRoute('app_product_check_sale', ['ids' => $ids, 'qty' => $qty, 'price' => $price, 'date' => $date]);
            }
        }

        // get USD rate
        $rateHelper = $this->get(RateHelper::class);
        if (!($rate = $rateHelper->getRate('USD'))) {
            $this->addFlash('notice',
                array('status' => 'danger', 'message' => $rateHelper->getError())
            );
            return $this->redirectToRoute('app');
        } else {
            $curRate = $rate;
        }

        $saleIds = [];
        foreach ($ids as $key => $id) {
            $productSale = new ProductSale();
            $productSale->setProductId($id);
            $productSale->setProduct($products[$id]);
            $productSale->setBunchId($products[$id]->getBunchId());
            $productSale->setBunch($products[$id]->getBunch());
            $productSale->setQty($qty[$key]);
            //price
            if (strpos($price[$key], ',') !== false) {
                $arr = explode(',', $price[$key]);
                $priceSale = number_format($arr[0] . '.' . $arr[1], 2, '.', '');
            } else {
                $priceSale = $price[$key];
            }
            $productSale->setPrice($priceSale);
            $productSale->setPriceUsd($priceSale / $curRate);

            $bunch = $products[$id]->getBunch();

            $productsRemaining = $bunch->getProducts();

            $count = 0;
            foreach ($productsRemaining as $item) {
                $count += $item->getQty();
            }


            if ($count > 1) {
                $productSale->setDisabledRedBall(true);
            } else {
                $productSale->setDisabledRedBall(false);
                $productsSaleInBunch = $bunch->getProductsSale();

                foreach ($productsSaleInBunch as $value) {
                    $value->setDisabledRedBall(false);
                }
            }


            //date
            $dateSale = \DateTime::createFromFormat('d-m-Y', $date[$key]);
            $productSale->setDate($dateSale);

            $em->persist($productSale);
            $em->flush();

            $saleIds[] = $productSale->getId();
        }

        $this->addFlash('notice',
            array('status' => 'success', 'message' => 'Все выбранные продукты успешно проданы!')
        );

        if ($redirect == "crossout") {
            return $this->redirectToRoute('app_crossout', ['ids' => $saleIds]);
        } else {
            return $this->redirectToRoute('app_product_sale_index');
        }
    }

    /**
     * namesAction
     *
     * Use in select2 ajax query for import
     * $results must have structure like this:
     * array (
     *    'results' => array(
     *          array('id'=> <value>, 'text' => <value>)
     *    )
     * )
     *  or
     * array (
     *    'results' => array(
     *          array('text'=> <value>, 'children' => array(array('id'=> <value>, 'text' => <value>)))
     *    )
     * )
     *
     * @param Request $request
     * @return type
     */
    public function ajaxSelect2SearchAction(Request $request)
    {
        $term = $request->get('term');
        $name = $term['term'];

        $productsSale = $this->getDoctrine()->getRepository('AppBundle:ProductSale')
            ->findSimilarNames($name);

        $results = [
            'results' => [
                [
                    'text' => 'Продукты',
                    'children' => $productsSale
                ]
            ]
        ];

        return $this->json($results);
    }

    /**
     * listNotCrossout
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listNotCrossoutAction(Request $request)
    {
        $terms = $request->query->all();
        // set defaults
        $terms['page'] = (isset($terms['page'])) ? $terms['page'] : 1;
        $terms['limit'] = (isset($terms['limit'])) ? $terms['limit'] : 10;

        $cookies = $request->cookies;
        if ($cookies->has('limit-perpage')) {
            $terms['limit'] = $cookies->get('limit-perpage');
        }

        $alreadyChanged = $request->get('alreadyChanged');

        /* @var $em \Doctrine\ORM\EntityManager */
        $em = $this->getDoctrine()->getEntityManager();

        $repository = $em->getRepository('AppBundle:ProductSale');
        $pagination = $repository->searchNotCrossoutBy($terms, $this->get('knp_paginator'), $alreadyChanged);

        // get USD rate
        $rateHelper = $this->get(RateHelper::class);
        if (!($rate = $rateHelper->getRate('USD'))) {
            $this->addFlash('notice',
                array('status' => 'danger', 'message' => $rateHelper->getError())
            );
            return $this->redirectToRoute('app');
        }

        // stats
        $repository = $em->getRepository('AppBundle:ProductSale');
        $stats = $repository->getStatsNotCrossout($rate);
        // total
        $total = $repository->getTotalNotCrossoutBy($terms);

        $repository = $em->getRepository('AppBundle:Provider');
        $providers = $repository->findAll();
        $flag = null;
        $ids = [];
        if (!$alreadyChanged) {
        
                $strIds = $request->cookies->get('not-crossout-checked-ids', "");
                $ids = array_diff(explode(',', $strIds), ['']);


        } else {
            $flag = 1;
            $strIds = $request->cookies->get('not-crossout-checked-ids-popup', "");
            $ids = array_diff(explode(',', $strIds), ['']);
        }

        return $this->render('AppBundle:sale:not_crossout.html.twig', [
            'items' => $pagination,
            'terms' => $terms,
            'providers' => $providers,
            'stats' => $stats,
            'total' => $total,
            'rate' => $rate,
            'ids' => $ids,
            'flag'=>$flag
        ]);
    }

    /**
     * ajaxBallRemoveAction
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function ajaxBallRemoveAction(Request $request)
    {
        $id = $request->get('id');

        /* @var $em \Doctrine\ORM\EntityManager */
        $em = $this->getDoctrine()->getEntityManager();
        $repoSale = $em->getRepository('AppBundle:ProductSale');
        $productSale = $repoSale->find($id);
        if ($productSale instanceof ProductSale) {
            $productSale->setDisabledRedBall(1);
            $em->persist($productSale);
            $em->flush();

            return $this->json(['status' => 'success']);
        }

        return $this->json(['status' => 'error']);
    }

    /**
     * checkQtyAction
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function checkQtyAction(Request $request)
    {
        $productId = $request->get('id');
        $qty = $request->get('qty');

        /* @var $em \Doctrine\ORM\EntityManager */
        $em = $this->getDoctrine()->getEntityManager();
        $repo = $em->getRepository('AppBundle:ProductStock');
        $productStock = $repo->find($productId);

        if (!empty($productStock) && $qty <= $productStock->getQty()) {
            return $this->json(['status' => 'success']);
        } else {
            $bunch = $productStock->getBunch();
            $products = $bunch->getProducts();
            $html = $this->render('AppBundle:product:_table_for_sale_products.html.twig', [
                'items' => $products
            ]);

            return $this->json(['status' => 'error', 'html' => $html->getContent()]);
        }
    }
}
