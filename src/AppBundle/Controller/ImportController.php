<?php

namespace AppBundle\Controller;

use AppBundle\Repository\RateHistoryRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity\RateHistory;
use AppBundle\Entity\ProductStock;
use AppBundle\Entity\Bunch;
use AppBundle\Service\ExcelReader;
use AppBundle\Service\ProductStockChecker;
use AppBundle\Service\RateHelper;

class ImportController extends Controller
{

    const LIMIT = 10;

    /**
     * Step1 Action
     * 
     * @param Request $request
     * @return type
     */
    public function step1Action(Request $request)
    {
        /* @var $em \EntityManager */
        $em = $this->getDoctrine()->getManager();
        $providers = $em->getRepository('AppBundle:Provider')
                       ->findAll();
        
        return $this->render('AppBundle:import:step1.html.twig', [
            "providers" => $providers
        ]);
    }
    
    /**
     * Step2 Action
     *
     * @param Request $request
     */
    public function step2Action(Request $request)
    {
        $provider_id = $request->get('provider_id');
        $provider = $this->getDoctrine()->getRepository('AppBundle:Provider')
            ->find($provider_id);
        // check provider
        if (empty($provider)) {
            $this->addFlash('notice',
                array('status' => 'danger', 'message' => 'Поставщик отсутствует в базе!')
            );
            return $this->redirectToRoute('app_import_step1', []);
        }

        return $this->render('AppBundle:import:step2.html.twig', [
            'provider_id' => $request->get('provider_id'),
            'has_documents' => $request->get('has_documents'),
        ]);
    }
    
    /**
     * Step3 Action
     * 
     * @param Request $request
     */
    public function step3Action(Request $request)
    {
        $provider_id = $request->get('provider_id');
        $has_documents = $request->get('has_documents');
        
        $uploadedFile = $request->files->get('excel');
        $data = [];
        if ($uploadedFile && $uploadedFile->isValid())
        {
            $directory = $this->getParameter('tmp_upload_directory');
            $name = 'tmp_' . md5(time());
            $file = $uploadedFile->move($directory, $name);

            // get USD rate
            $rateHelper = $this->get(RateHelper::class);
            if ( ! ($rate = $rateHelper->getRate('USD') ) ) {
                $this->addFlash('notice',
                    array('status' => 'danger', 'message' => $rateHelper->getError())
                );
                return $this->redirectToRoute('app_import_step2', [
                    'provider_id' => $provider_id,
                    'has_documents' => $has_documents
                ]);
            }

            // read excel file uploaded by user
            $excelReader = $this->get(ExcelReader::class);
            $data = $excelReader->read($file);
            $data = $excelReader->validate($data, $rate, $rateHelper);
            
            // get USD rate
            if ( ! ($data = $rateHelper->applyCurrencyToArray('USD', $data, ['Цена, руб.', 'Стоимость, руб.'], ['Цена, $', 'Стоимость, $'])) )
            {
                $this->addFlash('notice',
                    array('status' => 'danger', 'message' => $rateHelper->getError())
                );
                return $this->redirectToRoute('app_import_step2', [
                    'provider_id' => $provider_id,
                    'has_documents' => $has_documents
                ]);
            }

            // rate history
            if ($result = $rateHelper->getRate('USD')) {
                $em = $this->getDoctrine()->getManager();
                $rateHistory = $em->getRepository('AppBundle:RateHistory')
                    ->findByCurrentDate();
                if (! $rateHistory )
                {
                    $rateHistory = new RateHistory();
                    $rateHistory->setAbbreviation('USD');
                    $rateHistory->setDate(new \DateTime());
                    $rateHistory->setRate($result);

                    $em->persist($rateHistory);
                    $em->flush();
                }
            } else {
                $this->addFlash('notice',
                    array('status' => 'danger', 'message' => $rateHelper->getError())
                );
                return $this->redirectToRoute('app_import_step2', [
                    'provider_id' => $provider_id,
                    'has_documents' => $has_documents
                ]);
            }
           
            $checker = $this->get(ProductStockChecker::class);
            $checkedData = $checker->check($data);
            $filename = $checker->saveDataToTmpFile($directory);
            $this->container->get('session')->set('excelData', $filename);
            
            $provider = $this->getDoctrine()->getRepository('AppBundle:Provider')
                    ->find($provider_id);

            return $this->render('AppBundle:import:step3.html.twig', [
                'provider' => $provider,
                'has_documents' => $has_documents,
                'data' => $checkedData['old'],
                'filename' => $directory . DIRECTORY_SEPARATOR . $name
            ]);
        }
        else {
            $this->addFlash('notice',
                array('status' => 'danger', 'message' => 'Не верный формат файла')
            );
            return $this->redirectToRoute('app_import_step2', [
                'provider_id' => $provider_id,
                'has_documents' => $has_documents
            ]);
        }
    }
    
    /**
     * Step4 Action
     * 
     * @param Request $request
     */
    public function step4Action(Request $request)
    {
        $provider_id = $request->get('provider_id');
        $has_documents = $request->get('has_documents');
        $filename = $request->get('filename');
        
        $excelReader = $this->get(ExcelReader::class);
        $data = $excelReader->read($filename);
        
        $checker = $this->get(ProductStockChecker::class);
        $filename = $this->container->get('session')->get('excelData');
        $checkedData = $checker->loadDataFromTmpFile($filename);
        $checkedDataSlice = array_slice($checkedData['new'], 0, self::LIMIT);
        $count = ceil(count($checkedData['new'])/self::LIMIT);

        return $this->render('AppBundle:import:step4.html.twig', [
            'provider_id' => $provider_id,
            'has_documents' => $has_documents,
            'data' => $checkedDataSlice,
            'allPages'     => $count,
            'currentPage' => 1,
            'offset'    => 0,
            'limit'     => self::LIMIT
        ]);
    }

    /**
     * Step5 Action
     *
     * @param Request $request
     */
    public function step5Action(Request $request)
    {
        $provider_id = $request->get('provider_id');
        $has_documents = $request->get('has_documents');
        $limit = $request->get('limit');
        $offset = $request->get('offset');

        // set params for next step for data
        $offset = $offset + $limit;

        $checker = $this->get(ProductStockChecker::class);
        $filename = $this->container->get('session')->get('excelData');
        $checkedData = $checker->loadDataFromTmpFile($filename);

        $productsTitle = $request->get('title', []);
        $productsSimilar = $request->get('similar-product', []);

        $checker->setProductsTitle( array_merge($checkedData['productsTitle'], $productsTitle) );
        $checker->setProductsSimilar( array_merge($checkedData['productsSimilar'], $productsSimilar) );

        // save for next step
        $checker->updateDataInTmpFile($filename);

        $checkedDataSlice = array_slice($checkedData['new'], $offset, $limit);

        if (empty($checkedDataSlice)) {
            $response = $this->forward('AppBundle:Import:Step6', array(
                'request'  => $request
            ));
            return $response;
        }

        $count = ceil(count($checkedData['new'])/self::LIMIT);
        $currentPage = $offset/self::LIMIT + 1;

        return $this->render('AppBundle:import:step4.html.twig', [
            'provider_id' => $provider_id,
            'has_documents' => $has_documents,
            'data' => $checkedDataSlice,
            'limit' => $limit,
            'offset' => $offset,
            'allPages'     => $count,
            'currentPage' => $currentPage,
        ]);
    }

    /**
     * Step6 Action
     * 
     * @param Request $request
     */
    public function step6Action(Request $request)
    {
        $providerId = $request->get('provider_id');
        $documents = $request->get('has_documents');
        
        $checker = $this->get(ProductStockChecker::class);
        $filename = $this->container->get('session')->get('excelData');
        $checkedData = $checker->loadDataFromTmpFile($filename);

        $em = $this->getDoctrine()->getEntityManager();
        $productRepo = $em->getRepository('AppBundle:ProductStock');

        list($countNewProductInserted, $countOldProductInserted, $countBunchCreated)
            = $productRepo->saveCheckedData($checkedData, $providerId, $documents);

        // clear tmp data
        unlink($filename);
        $this->container->get('session')->remove('excelData');
               
        return $this->render('AppBundle:import:step5.html.twig', [
            'countNewProductInserted' => $countNewProductInserted,
            'countOldProductInserted' => $countOldProductInserted,
            'countBunchCreated' => $countBunchCreated
        ]);
    }

    /**
     * Back Action
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function backAction(Request $request)
    {
        $checker = $this->get(ProductStockChecker::class);
        $filename = $this->container->get('session')->get('excelData');
        $checkedData = $checker->loadDataFromTmpFile($filename);
        $limit = $request->get('limit');
        $offset = $request->get('offset');

        $offset = $offset - $limit;

        $checkedData['productsSimilar'] = array_slice($checkedData['productsSimilar'],0, $offset );
        $checkedData['productsTitle'] = array_slice($checkedData['productsTitle'],0, $offset );

        $checker->setProductsTitle( $checkedData['productsTitle'] );
        $checker->setProductsSimilar( $checkedData['productsSimilar'] );

        // save for next step
        $checker->updateDataInTmpFile($filename);

        $offset = $offset - $limit;
        $request->request->set('offset', $offset);
        $request->request->set('title', []);
        $request->request->set('similar-product', []);

        return $this->forward('AppBundle:Import:Step5');
    }
}
