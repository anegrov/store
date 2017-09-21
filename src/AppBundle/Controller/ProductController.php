<?php

namespace AppBundle\Controller;

use AppBundle\Entity\ProductStock;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Service\RateHelper;


class ProductController extends Controller
{
    /**
     * indexAction
     */
    public function indexAction(Request $request)
    {
        $terms = $request->query->all();
        // set defaults
        $terms['page'] = (isset($terms['page'])) ? $terms['page'] : 1;
        $terms['limit'] = (isset($terms['limit'])) ? $terms['limit'] : 10;

        $cookies = $request->cookies;
        if ( $cookies->has('limit-perpage') ) {
            $terms['limit'] = $cookies->get('limit-perpage');
        }

        // get USD rate
        $rateHelper = $this->get(RateHelper::class);
        if ( ! ($rate = $rateHelper->getRate('USD') ) ) {
            $this->addFlash('notice',
                array('status' => 'danger', 'message' => $rateHelper->getError())
            );
            return $this->redirectToRoute('app');
        }

        /* @var $em \Doctrine\ORM\EntityManager */
        $em = $this->getDoctrine()->getManager();
        $repository = $em->getRepository('AppBundle:Bunch');
        $pagination = $repository->searchBy($terms, $this->get('knp_paginator'));

        $total = $repository->getTotal($terms);

        $repository = $em->getRepository('AppBundle:Provider');
        $providers = $repository->findAll();

        $strIds = $request->cookies->get('stock-checked-ids', "");
        $ids = array_diff(explode(',', $strIds), ['']);

        $returnIds=[];
        foreach($ids as $key=>$str) {
            $idQty = json_decode($str);
            foreach ($idQty as $key=>$itm) {
                $returnIds[$key] = $itm;
            }
        }



        return $this->render('AppBundle:product:main.html.twig', [
            'items'     => $pagination,
            'total'     => $total,
            'terms'     => $terms,
            'providers' => $providers,
            'ids'       => $returnIds,
            'rate'      => $rate
        ]);
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
    public function namesAction(Request $request)
    {
        $term = $request->get('term');
        $withProduct = $request->get('with-product', false);
        $onlyProduct = $request->get('only-product', false);
        $extended = $request->get('extended', false);
        $name = $term['term'];

        if ($onlyProduct || $withProduct) {
            $products = $this->getDoctrine()->getRepository('AppBundle:ProductStock')
                ->findSimilarNames($name);

            $productsResults = [];
            foreach ($products as $product) {
                $data = ['id' => 'p_' . $product->getId(), 'text' => $product->getTitle()];
                if ($extended) {
                    $data['price'] = $product->getPriceByn();
                }

                $productsResults[] = $data;
            }
        }

        if ($onlyProduct === false) {
            $bunchs = $this->getDoctrine()->getRepository('AppBundle:Bunch')
                ->findSimilarNames($name);

            $bunchsResults = [];
            foreach ($bunchs as $bunch) {
                $bunchsResults[] = ['id' => 'b_' . $bunch->getId(), 'text' => $bunch->getTitle()];
            }
        }

        if ($onlyProduct) {
            $results = [
                'results' => [
                    [
                        'text' => 'Продукты',
                        'children' => $productsResults
                    ]
                ]
            ];
        }
        else {
            if ($withProduct) {
                $results = [
                    'results' => [
                        [
                            'text' => 'Группы продуктов',
                            'children' => $bunchsResults
                        ],
                        [
                            'text' => 'Продукты',
                            'children' => $productsResults
                        ]
                    ]
                ];
            } else {
                $results = [
                    'results' => $bunchsResults
                ];
            }
        }

        return $this->json($results);
    }

    /**
     * starAction
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function starAction(Request $request)
    {
        $starName = $request->get('starName');
        $starValue = $request->get('starValue');
        $productId = $request->get('productId');
        $bunchId = $request->get('bunchId');

        /* @var $em \Doctrine\ORM\EntityManager */
        $em = $this->getDoctrine()->getEntityManager();

        if (!empty($productId)) {
            /* @var $product \AppBundle\Entity\ProductStock */
            $item = $em->getRepository('AppBundle:ProductStock')
                ->find($productId);
        }
        else {
            /* @var $product \AppBundle\Entity\ProductStock */
            $item = $em->getRepository('AppBundle:Bunch')
                ->find($bunchId);
        }

        if (empty($item)) {
            return $this->json(['status' => 'error']);
        }

        if ($starName == 'star1') {
            $item->setStar1($starValue);
        }
        if ($starName == 'star2') {
            $item->setStar2($starValue);
        }
        if ($starName == 'star3') {
            $item->setStar3($starValue);
        }

        $em->persist($item);
        $em->flush();

        return $this->json(['status' => 'success']);
    }

    /**
     * SeachAjax Action
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function seachAjaxAction(Request $request)
    {
        $title = $request->get('title');
        $unless = $request->get('unless', []);

        /* @var $em \Doctrine\ORM\EntityManager */
        $em = $this->getDoctrine()->getEntityManager();
        /* @var $product \AppBundle\Entity\ProductStock */
        $products = $em->getRepository('AppBundle:ProductStock')
            ->searchByTitle($title, $unless);

        return $this->render('AppBundle:product:_table_for_sale_products.html.twig', [
            'items'     => $products
        ]);
    }

    /**
     * Ball Change Action
     *
     * If ball status is red, then set blue. If status is blue, then change to green.
     * If ball is green, then change to null.
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function ballChangeAction(Request $request)
    {
        $itemId = $request->get('id');

        /* @var $em \Doctrine\ORM\EntityManager */
        $em = $this->getDoctrine()->getEntityManager();

        $item = $em->getRepository('AppBundle:Bunch')
            ->find($itemId);

        if (empty($item)) {
            return $this->json(['status' => 'error', 'message' => 'Bunch not found']);
        }
        if ($item->getBall() == ProductStock::RED_BALL) {
            $item->setBall( ProductStock::BLUE_BALL );
        }
        elseif ( $item->getBall() == ProductStock::BLUE_BALL ) {
            $item->setBall( ProductStock::GREEN_BALL );
        }
        elseif ( $item->getBall() == ProductStock::GREEN_BALL ) {
            $item->setBall( NULL );
        }elseif ($item->getBall() == NULL){
            $item->setBall(ProductStock::RED_BALL);
        }

        $em->persist($item);
        $em->flush();

        return $this->json([ 'status' => 'success', 'ball' => $item->getBall() ]);
    }
}
