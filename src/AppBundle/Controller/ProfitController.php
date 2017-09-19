<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Profit;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use AppBundle\Service\RateHelper;

/**
 * Profit controller.
 *
 * @Route("/profit")
 */
class ProfitController extends Controller
{
    /**
     * Profit Index Page
     *
     * @Route("/", name="app_profit_index")
     * @Route("/{month}", name="app_profit_month")
     * @Method("GET")
     * @Template()
     */
    public function indexAction(Request $request)
    {
        $date = new \DateTime();
        $month = $request->get('month', $date->format('m'))*1;
        $date->setDate($date->format('Y'), $month, 1);
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

        $repoSale = $em->getRepository('AppBundle:ProductSale');
        $saleStatsByMonth = $repoSale->findStatsByMonth($terms);

        $repoProfit = $em->getRepository('AppBundle:Profit');
        $profit = $repoProfit->findByMonth($month,$terms);

        return array(
            'rate'      => $rate,
            'statsSale' => $saleStatsByMonth,
            'profit'    => $profit,
            'date'      => $date
        );
    }

    /**
     * changeAction
     *
     * @Route("/change", name="app_profit_change")
     * @Method("POST")
     * @Template()
     */
    public function changeAction(Request $request)
    {
        $date = new \DateTime();
        $profits = $request->get('profit', []);
        $month = $request->get('month', $date->format("m"));
        $date->setDate($date->format('Y'), $month, 1);

        /* @var $em \Doctrine\ORM\EntityManager */
        $em = $this->getDoctrine()->getManager();

        foreach ($profits as $day=>$data) {

            $date->setDate($date->format('Y'), $month, $day);
            $profit = $em->getRepository('AppBundle:Profit')
                         ->findOneBy(['date'=>$date]);
            if (!empty($profit)) {
                $profit->setIncome($data['income']);
                $profit->setOutcome($data['outcome']);
            }
            else {
                $profit = new Profit();
                $profit->setDate($date);
                $profit->setIncome($data['income']);
                $profit->setOutcome($data['outcome']);
            }

            $em->persist($profit);
            $em->flush();
        }

        $repoProfit = $em->getRepository('AppBundle:Profit');
        $profit = $repoProfit->findByMonth($month);

        return $this->json($profit);
    }
}