<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace AppBundle\Service;

use \Doctrine\ORM\EntityManager;

/**
 * Description of ProductStock
 *
 * @author anton
 */
class ProductStockChecker {

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    /**
     * @var array
     */
    private $data = [
        'old' => [],
        'new' => [],
        'productsTitle' => [],
        'productsSimilar' => []
    ];

    public function __construct(EntityManager $entityManager) {
        $this->em = $entityManager;
    }

    /**
     * check
     * 
     * @param array $data
     * @return type
     */
    public function check($data)
    {
        $repository = $this->em->getRepository('AppBundle:Bunch');
        $productRepository = $this->em->getRepository('AppBundle:ProductStock');


        foreach ($data as $row=>$values)
        {
            foreach ($values as $col=>$val)
            {
                if ($col == 'Наименование')
                {
                    $result = $repository->findOneByTitle($val);
                    if (!empty($result)) {
                        $values['db_item'] = $result;
                        $this->data['old'][] = $values;
                    }
                    else {
                        // find not only by bunch name, but and by product title in this bunch
                        $result = $productRepository->findOneWithBunch($val);
                        if (!empty($result)) {
                            $values['db_item'] = $result->getBunch();
                            $this->data['old'][] = $values;
                        }
                        else {
                            $this->data['new'][] = $values;
                        }
                    }

                }
            }
        }

        
        return $this->data;
    }

    /**
     * setProductsTitle
     *
     * @param $productsTitle
     * @return $this
     */
    public function setProductsTitle($productsTitle)
    {
        $this->data['productsTitle'] = $productsTitle;

        return $this;
    }

    /**
     * getProductsTitle
     *
     * @return mixed
     */
    public function getProductsTitle()
    {
        return $this->data['productsTitle'];
    }

    /**
     * setProductsSimilar
     *
     * @param $productsSimilar
     * @return $this
     */
    public function setProductsSimilar($productsSimilar)
    {
        $this->data['productsSimilar'] = $productsSimilar;

        return $this;
    }

    /**
     * saveDataToTmpFile
     * 
     * @param type $path
     * @return string
     */
    public function saveDataToTmpFile($path)
    {
        $filename = $path . DIRECTORY_SEPARATOR . 'tmp_'. md5(time());
        file_put_contents($filename, serialize($this->data));
        
        return $filename;
    }

    /**
     * updateDataInTmpFile
     *
     * @param type $filename
     * @return string
     */
    public function updateDataInTmpFile($filename)
    {
        file_put_contents($filename, serialize($this->data));

        return $filename;
    }
    
    /**
     * loadDataFromTmpFile
     * 
     * @param type $filename
     * @return type
     */
    public function loadDataFromTmpFile($filename)
    {
        $content = file_get_contents($filename);
        $this->data = unserialize($content);
        
        return $this->data;
    }
}
