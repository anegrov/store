<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use AppBundle\Service\RateHelper;

/**
 * Stats controller.
 *
 * @Route("/stats")
 */
class StatsController extends Controller
{
    /**
     * Lists all stats
     *
     * @Route("/", name="app_stats_index")
     * @Method("GET")
     * @Template()
     */
    public function indexAction(Request $request)
    {
        /* @var $em \Doctrine\ORM\EntityManager */
        $em = $this->getDoctrine()->getManager();

        // get USD rate
        $rateHelper = $this->get(RateHelper::class);
        if ( ! ($rate = $rateHelper->getRate('USD') ) ) {
            $this->addFlash('notice',
                array('status' => 'danger', 'message' => $rateHelper->getError())
            );
            return $this->redirectToRoute('app');
        }
        $terms['rate'] = $rate;

        $productStockRepo = $em->getRepository('AppBundle:ProductStock');
        $productSaleRepo = $em->getRepository('AppBundle:ProductSale');
        $productCrossoutRepo = $em->getRepository('AppBundle:ProductCrossout');

        $statsSale = $productSaleRepo->getStats($rate);
        $statsCrossout = $productCrossoutRepo->getStats();
        $statsNotCrossout = $productSaleRepo->getStatsNotCrossout($rate);

        // stats by not crossout products
        $curDate = new \DateTime();
        $notCrossoutMonth = $request->get('notCrossoutMonth', $curDate->format('m')*1);
        $notCrossoutWeeksStats = $productSaleRepo->findWeeksStatsByMonth($notCrossoutMonth);

        // stats by not crossout products
        $curDate = new \DateTime();
        $stockMonth = $request->get('stockMonth', $curDate->format('m')*1);
        $stockWeeksStats = $productStockRepo->findWeeksStatsByMonth($stockMonth);

        $totalStock = $productStockRepo->getTotal();
        $totalSale = $productSaleRepo->getTotalBy($terms);
        $totalNotCrossout = $productSaleRepo->getTotalNotCrossoutBy($terms);
        $totalCrossout = $productCrossoutRepo->getTotal();

        $stockStatsByMonth = $productStockRepo->findStatsByMonth();
        $saleStatsByMonth = $productSaleRepo->findStatsByMonth($terms);
        $crossoutStatsByMonth = $productSaleRepo->findStatsByMonth($terms);
        $notCrossoutStatsByMonth = $productSaleRepo->findNotCrossoutStatsByMonth($terms);

        $involvedFunds = $em->getRepository('AppBundle:Config')
                            ->findOneBy(['name'=>'involved_funds']);
        $involvedFunds = ($involvedFunds) ? $involvedFunds->getValue() : 0;

        return array(
            'statsSale' => $statsSale,
            'statsCrossout' => $statsCrossout,
            'statsNotCrossout' => $statsNotCrossout,
            'statsWeeksNotCrossout' => $notCrossoutWeeksStats,
            'statsWeeksStock'   => $stockWeeksStats,
            'totalStock'        => $totalStock,
            'totalSale'         => $totalSale,
            'totalNotCrossout' => $totalNotCrossout,
            'totalCrossout'    => $totalCrossout,
            'statsStockByMonth'  => $stockStatsByMonth,
            'statsSaleByMonth'   => $saleStatsByMonth,
            'statsCrossoutByMonth' => $crossoutStatsByMonth,
            'statsNotCrossoutByMonth' => $notCrossoutStatsByMonth,
            'rate'      => $rate,
            'involvedFunds' => $involvedFunds,
            'notCrossoutMonth' => $notCrossoutMonth,
            'stockMonth'    => $stockMonth
        );
    }
}