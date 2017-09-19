<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use AppBundle\Service\RateHelper;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class ExportController extends Controller
{
    /**
     * Export to xls
     *
     * @Route("/export/", name="app_export")
     */
    public function exportProductTableAction(Request $request)
    {
        $terms = $request->query->all();

        /* @var $em \Doctrine\ORM\EntityManager */
        $em = $this->getDoctrine()->getManager();
        $repository = $em->getRepository('AppBundle:Bunch');

        $items = $repository->findAllBy($terms);

        return $this->_createXlsWithBunch($items, 'Товары');
    }

    /**
     * Export to xls
     *
     * @Route("/export-sale/", name="app_export_sale")
     */
    public function exportProductSaleTableAction(Request $request)
    {
        $terms = $request->query->all();

        /* @var $em \Doctrine\ORM\EntityManager */
        $em = $this->getDoctrine()->getManager();
        $repository = $em->getRepository('AppBundle:ProductSale');

        $items = $repository->findAllBy($terms);

        return $this->_createXls($items, 'Проданные Товары', 'sale');
    }

    /**
     * Export to xls
     *
     * @Route("/export-crossout/", name="app_export_crossout")
     */
    public function exportProductCrossoutTableAction(Request $request)
    {
        $terms = $request->query->all();

        /* @var $em \Doctrine\ORM\EntityManager */
        $em = $this->getDoctrine()->getManager();
        $repository = $em->getRepository('AppBundle:ProductCrossout');

        $items = $repository->findAllBy($terms);

        return $this->_createXls($items, 'Списанные Товары', 'crossout');
    }

    /**
     * Export to xls
     *
     * @Route("/export-not-crossout/", name="app_export_not_crossout")
     */
    public function exportProductNotCrossoutTableAction(Request $request)
    {
        $terms = $request->query->all();

        /* @var $em \Doctrine\ORM\EntityManager */
        $em = $this->getDoctrine()->getManager();
        $repository = $em->getRepository('AppBundle:ProductSale');

        $items = $repository->findAllNotCrossoutBy($terms);

        return $this->_createXls($items, 'Не Списанные Товары', 'not-crossout');
    }

    /**
     * _createXls
     *
     * @param Response $content
     * @return mixed
     */
    protected function _createXls($items, $title, $table)
    {
        // ask the service for a excel object
        /* @var $phpExcelObject \PHPExcel */
        $objPHPExcel = $this->get('phpexcel')->createPHPExcelObject();

        $objPHPExcel->getProperties()->setCreator("liuggio")
            ->setLastModifiedBy("User")
            ->setTitle($title)
            ->setSubject($title)
            ->setDescription($title)
            ->setKeywords($title)
            ->setCategory($title);

        $objPHPExcel->setActiveSheetIndex(0);
        $objPHPExcel->getActiveSheet()->setTitle($title);
        // set active sheet index to the first sheet, so Excel opens this as the first sheet
        $objPHPExcel->setActiveSheetIndex(0);

        // get USD rate
        $rateHelper = $this->get(RateHelper::class);
        if ( ! ($rate = $rateHelper->getRate('USD') ) ) {
            $this->addFlash('notice',
                array('status' => 'danger', 'message' => $rateHelper->getError())
            );
            return $this->redirectToRoute('app');
        }

        $rowCount = 1;
        $objPHPExcel->getActiveSheet()->setCellValue('A'.$rowCount,'Товары');
        $objPHPExcel->getActiveSheet()->setCellValue('B'.$rowCount,'Количесвто, шт.');
        $objPHPExcel->getActiveSheet()->setCellValue('C'.$rowCount,'Закупка, руб.');
        $objPHPExcel->getActiveSheet()->setCellValue('D'.$rowCount,'Закупка, $');
        $objPHPExcel->getActiveSheet()->setCellValue('E'.$rowCount,'Продажа, руб.');
        $objPHPExcel->getActiveSheet()->setCellValue('F'.$rowCount,'Продажа, $');
        if ($table == 'crossout') {
            $objPHPExcel->getActiveSheet()->setCellValue('G'.$rowCount,'Цена списания, руб.');
            $objPHPExcel->getActiveSheet()->setCellValue('H'.$rowCount,'Дата списания');
        }
        if ($table == 'sale') {
            $objPHPExcel->getActiveSheet()->setCellValue('G'.$rowCount,'Прибыль, $');
            $objPHPExcel->getActiveSheet()->setCellValue('H'.$rowCount,'Прибыль, руб.');
            $objPHPExcel->getActiveSheet()->setCellValue('I'.$rowCount,'Дата продажи');
        }
        if ($table == 'sale' || $table == 'not-crossout') {
            $col = ($table == 'sale') ? 'I' : 'G';
            $objPHPExcel->getActiveSheet()->setCellValue($col.$rowCount,'Дата продажи');
        }

        foreach ($items as $item) {
            if (is_array($item)) $product = $item[0]; else $product = $item;

            $rowCount++;
            $objPHPExcel->getActiveSheet()->setCellValue('A'.$rowCount, $product->getTitle());
            $objPHPExcel->getActiveSheet()->setCellValue('B'.$rowCount, $product->getQty());
            $objPHPExcel->getActiveSheet()->setCellValue('C'.$rowCount, $product->getPurchasePriceByn());
            $objPHPExcel->getActiveSheet()->setCellValue('D'.$rowCount, $product->getPurchasePrice());
            $objPHPExcel->getActiveSheet()->setCellValue('E'.$rowCount, $product->getPrice());
            $objPHPExcel->getActiveSheet()->setCellValue('F'.$rowCount, $product->getPrice()/$rate);

            if ($table == 'crossout') {
                $objPHPExcel->getActiveSheet()->setCellValue('G'.$rowCount, $product->getPrice());
                $objPHPExcel->getActiveSheet()->setCellValue('H'.$rowCount, $product->getDate());
            }
            if ($table == 'sale') {
                $profitUSD = ($product->getPrice()/$rate)*$product->getQty() - $product->getProduct()->getPrice()*$product->getQty();
                $objPHPExcel->getActiveSheet()->setCellValue('G'.$rowCount, $profitUSD);
                $profitBYN = $product->getPrice()*$product->getQty() - $product->getProduct()->getPriceByn()*$product->getQty();
                $objPHPExcel->getActiveSheet()->setCellValue('H'.$rowCount, $profitBYN);
                $objPHPExcel->getActiveSheet()->setCellValue('I'.$rowCount, $product->getDate()->format('Y-m-d'));
            }
            if ($table == 'sale' || $table == 'not-crossout') {
                $col = ($table == 'sale') ? 'I' : 'G';
                $objPHPExcel->getActiveSheet()->setCellValue($col.$rowCount, $product->getDate()->format('Y-m-d'));
            }
        }

        if ($table == 'crossout') $rows = 'A1:H1'; else if ($table == 'sale') $rows = 'A1:I1'; else if ($table == 'not-crossout') $rows = 'A1:G1';

        $objPHPExcel->getActiveSheet()->getStyle($rows)->getFill()->applyFromArray(array(
            'type' => \PHPExcel_Style_Fill::FILL_SOLID,
            'startcolor' => array(
                'rgb' => 'c8cfd4'
            )
        ));

        $objPHPExcel->getActiveSheet()->getRowDimension('1')->setRowHeight(65);
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(55.5);
        $objPHPExcel->getActiveSheet()->getStyle('A1')->getAlignment()->setWrapText(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(6.9);
        $objPHPExcel->getActiveSheet()->getStyle('B1')->getAlignment()->setWrapText(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(9.9);
        $objPHPExcel->getActiveSheet()->getStyle('C1')->getAlignment()->setWrapText(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(9.9);
        $objPHPExcel->getActiveSheet()->getStyle('D1')->getAlignment()->setWrapText(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(10.9);
        $objPHPExcel->getActiveSheet()->getStyle('E1')->getAlignment()->setWrapText(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(10.9);
        $objPHPExcel->getActiveSheet()->getStyle('F1')->getAlignment()->setWrapText(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(10.9);
        $objPHPExcel->getActiveSheet()->getStyle('G1')->getAlignment()->setWrapText(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(10.9);
        $objPHPExcel->getActiveSheet()->getStyle('H1')->getAlignment()->setWrapText(true);
        if ($table == 'sale') {
            $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(10.9);
            $objPHPExcel->getActiveSheet()->getStyle('I1')->getAlignment()->setWrapText(true);
        }

        $objPHPExcel->getActiveSheet()->getStyle($rows)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle($rows)->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);

        // create the writer
        $writer = $this->get('phpexcel')->createWriter($objPHPExcel, 'Excel2007');

        // create the response
        $response = $this->get('phpexcel')->createStreamedResponse($writer);
        // adding headers
        $dispositionHeader = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            'export.xlsx'
        );
        $response->headers->set('Content-Type', 'text/vnd.ms-excel; charset=utf-8');
        $response->headers->set('Pragma', 'public');
        $response->headers->set('Cache-Control', 'maxage=1');
        $response->headers->set('Content-Disposition', $dispositionHeader);

        return $response;
    }

    /**
     * _createXls
     *
     * @param Response $content
     * @return mixed
     */
    protected function _createXlsWithBunch($items, $title)
    {
        // ask the service for a excel object
        /* @var $phpExcelObject \PHPExcel */
        $objPHPExcel = $this->get('phpexcel')->createPHPExcelObject();

        $objPHPExcel->getProperties()->setCreator("liuggio")
            ->setLastModifiedBy("User")
            ->setTitle($title)
            ->setSubject($title)
            ->setDescription($title)
            ->setKeywords($title)
            ->setCategory($title);

        $objPHPExcel->setActiveSheetIndex(0);
        $objPHPExcel->getActiveSheet()->setTitle($title);
        // set active sheet index to the first sheet, so Excel opens this as the first sheet
        $objPHPExcel->setActiveSheetIndex(0);


        $rowCount = 1;
        $objPHPExcel->getActiveSheet()->setCellValue('A'.$rowCount,'Товары');
        $objPHPExcel->getActiveSheet()->setCellValue('B'.$rowCount,'Наличие, шт');
        $objPHPExcel->getActiveSheet()->setCellValue('C'.$rowCount,'Диапазон цен, руб.');
        $objPHPExcel->getActiveSheet()->setCellValue('D'.$rowCount,'Диапазон цен,$');
        $objPHPExcel->getActiveSheet()->setCellValue('E'.$rowCount,'Цена последнего товара, руб.');
        $objPHPExcel->getActiveSheet()->setCellValue('F'.$rowCount,'Дата, поставки последнего');

        foreach ($items as $item) {
            if (is_array($item)) {
                $bunch = $item[0];
                $lastDate = (isset($item['last_date'])) ? substr($item['last_date'], 0, 10)  : $bunch->getLastProduct()->getCreated()->format('Y-m-d');
            }
            else {
                $bunch = $item;
            }

            $rowCount++;
            $objPHPExcel->getActiveSheet()->setCellValue('A'.$rowCount, $bunch->getTitle());
            $objPHPExcel->getActiveSheet()->setCellValue('B'.$rowCount, $bunch->getTotal());
            if ($bunch->getMaxPriceByn() != $bunch->getMinPriceByn()) {
                $diapazonPrice = $bunch->getMinPriceByn() - $bunch->getMaxPriceByn();
            } else {
                $diapazonPrice = $bunch->getMinPriceByn();
            }
            $objPHPExcel->getActiveSheet()->setCellValue('C'.$rowCount, $diapazonPrice);
            if ($bunch->getMaxPriceByn() != $bunch->getMinPriceByn()) {
                $diapazonPrice = $bunch->getMinPrice() - $bunch->getMaxPrice();
            } else {
                $diapazonPrice = $bunch->getMinPrice();
            }
            $objPHPExcel->getActiveSheet()->setCellValue('D'.$rowCount, $diapazonPrice);
            $objPHPExcel->getActiveSheet()->setCellValue('E'.$rowCount, $bunch->getLastProductPrice());
            $lastDate = (isset($lastDate)) ? $lastDate : $bunch->getLastProduct()->getCreated()->format('Y-m-d');
            $objPHPExcel->getActiveSheet()->setCellValue('F'.$rowCount, $lastDate);

            $objPHPExcel->getActiveSheet()->getStyle("A{$rowCount}:F{$rowCount}")->getFill()->applyFromArray(array(
                'type' => \PHPExcel_Style_Fill::FILL_SOLID,
                'startcolor' => array(
                    'rgb' => 'e4e7e8'
                )
            ));

            foreach ($bunch->getProducts() as $product)
            {
                $rowCount++;
                $objPHPExcel->getActiveSheet()->setCellValue('A'.$rowCount, $product->getTitle());
                $objPHPExcel->getActiveSheet()->setCellValue('B'.$rowCount, $product->getQty());
                $objPHPExcel->getActiveSheet()->setCellValue('C'.$rowCount, $product->getPriceByn());
                $objPHPExcel->getActiveSheet()->setCellValue('D'.$rowCount, $product->getPrice());
                $objPHPExcel->getActiveSheet()->setCellValue('E'.$rowCount, $product->getPrice());
                $objPHPExcel->getActiveSheet()->setCellValue('F'.$rowCount, $product->getCreated()->format('Y-m-d'));
            }
        }

        $objPHPExcel->getActiveSheet()->getStyle('A1:F1')->getFill()->applyFromArray(array(
            'type' => \PHPExcel_Style_Fill::FILL_SOLID,
            'startcolor' => array(
                'rgb' => 'c8cfd4'
            )
        ));

        $objPHPExcel->getActiveSheet()->getRowDimension('1')->setRowHeight(65);
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(55.5);
        $objPHPExcel->getActiveSheet()->getStyle('A1')->getAlignment()->setWrapText(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(6.9);
        $objPHPExcel->getActiveSheet()->getStyle('B1')->getAlignment()->setWrapText(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(9.9);
        $objPHPExcel->getActiveSheet()->getStyle('C1')->getAlignment()->setWrapText(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(9.9);
        $objPHPExcel->getActiveSheet()->getStyle('D1')->getAlignment()->setWrapText(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(10.9);
        $objPHPExcel->getActiveSheet()->getStyle('E1')->getAlignment()->setWrapText(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(10.9);
        $objPHPExcel->getActiveSheet()->getStyle('F1')->getAlignment()->setWrapText(true);

        $objPHPExcel->getActiveSheet()->getStyle('A1:F1')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('A1:F1')->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);

        // create the writer
        $writer = $this->get('phpexcel')->createWriter($objPHPExcel, 'Excel2007');

        // create the response
        $response = $this->get('phpexcel')->createStreamedResponse($writer);
        // adding headers
        $dispositionHeader = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            'export.xlsx'
        );
        $response->headers->set('Content-Type', 'text/vnd.ms-excel; charset=utf-8');
        $response->headers->set('Pragma', 'public');
        $response->headers->set('Cache-Control', 'maxage=1');
        $response->headers->set('Content-Disposition', $dispositionHeader);

        return $response;
    }
}
