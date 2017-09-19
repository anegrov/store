<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Config;
use AppBundle\Entity\Provider;
use AppBundle\Form\ProviderType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * Config controller.
 *
 * @Route("/config")
 */
class ConfigController extends Controller
{
    /**
     * changeAction
     *
     * @Route("/change", name="app_config_change")
     * @Method("POST")
     * @Template()
     */
    public function changeAction(Request $request)
    {
        /* @var $em \Doctrine\ORM\EntityManager */
        $em = $this->getDoctrine()->getManager();

        $key = $request->get('name');
        $val = $request->get('value');

        $config = $em->getRepository('AppBundle:Config')
           ->findOneBy(['name'=>$key]);

        if (!empty($config)) {
            $config->setValue($val);
        }
        else {
            $config = new Config();
            $config->setName($key);
            $config->setValue($val);
        }

        $em->persist($config);
        $em->flush();

        return $this->json([$key=>$val]);
    }
}