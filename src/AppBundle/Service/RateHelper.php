<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace AppBundle\Service;

use AppBundle\Entity\RateHistory;
use \Doctrine\ORM\EntityManager;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Description of RateHelper
 *
 * @author anton
 */
class RateHelper {
    
    /*
     * @var array of string
     */
    private $errors = [];

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    /**
     * RateHelper constructor.
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager) {
        $this->em = $entityManager;
    }


    /**
     * getRate
     * 
     * Get currency rate from nbrb.by
     * 
     * @param type $currency
     * @param type $onDate
     * @return boolean
     */
    public function getRate($currency, $onDate = null)
    {
        $result = [];

        if (!isset($onDate) || empty($onDate)) {
            $onDate = new \DateTime();
        }
        if (is_string($onDate)) {
            $onDate = date_create($onDate);
        }
        if ( ! ($onDate instanceof \DateTime)) {
            throw new NotFoundHttpException('Date format not found ('.$onDate.') ');
            return false;
        }

        $onDate->setTime(0,0,0);

        $repo = $this->em->getRepository('AppBundle:RateHistory');
        $item = $repo->findOneBy([
            'abbreviation' => $currency,
            'date' => $onDate
        ]);

        if ($item) {
            return $item->getRate();
        }
        $rate = null;
        while (is_null($rate))
        {
            $curl = curl_init();

            $url = 'http://www.nbrb.by/API/ExRates/Rates/' . $currency . '?ParamMode=2&OnDate='.$onDate->format("Y-m-d");

            curl_setopt_array($curl, Array(
                CURLOPT_URL => $url,
                CURLOPT_USERAGENT => 'spider',
                CURLOPT_TIMEOUT => 120,
                CURLOPT_CONNECTTIMEOUT => 30,
                CURLOPT_RETURNTRANSFER => TRUE,
                CURLOPT_ENCODING => 'UTF-8'
            ));

            $html = curl_exec($curl);

            if ($html === false)
            {
                //$this->setError(curl_error($curl));
                continue;
            }

            $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);

            if ($http_code != 200)
            {
                //$this->setError("Неожиданный код HTTP: . $http_code");
                continue;
            }

            curl_close($curl);

            $result = json_decode($html);

            $rate = $result->Cur_OfficialRate;
        }

        $rateHistory = new RateHistory();
        $rateHistory->setDate($onDate);
        $rateHistory->setAbbreviation($currency);
        $rateHistory->setRate($rate);

        $this->em->persist($rateHistory);
        $this->em->flush();

        return $rate;
    }
    
    /**
     * applyCurrencyToArray
     * 
     * Convert columns with BYN currency to user curreny.
     * $columnsFrom convert to $columnsTo
     * 
     * @param string $currency Abbreviation
     * @param array $data 
     * @param mixed $columnsFrom
     * @param mixed $columnsTo
     * @return array
     * @throws \Exception
     */
    public function applyCurrencyToArray($currency, array $data, $columnsFrom, $columnsTo)
    {
        // validate
        if (!is_string($currency)) {
            throw new \Exception('function applyCurrencyToArray first argument not a string');
        }

        if (is_string($columnsFrom)) {
            $columnsFrom[] = $columnsFrom;
        }

        if (is_string($columnsTo)) {
            $columnsTo[] = $columnsTo;
        }

        // get currency rate
        if (!$rate = $this->getRate($currency)) {
            return false;
        }

        foreach ($data as $k => $item) {
            foreach ($columnsFrom as $key => $col) {
                if (isset($item[$col])) {
                    $data[$k][$columnsTo[$key]] = round($item[$col] / $rate, 2);
                }
            }
        }

        return $data;
    }
    
    /**
     * setError
     * 
     * @param type $message
     * @return \RateHelper
     */
    public function setError($message)
    {
        $this->errors[] = $message;
        return $this;
    }
    
    /**
     * getError
     * 
     * Get first error message
     * 
     * @return type
     */
    public function getError()
    {
        return current($this->errors);
    }
}
