<?php

namespace AppBundle\Repository;

use AppBundle\Entity\ProductCrossout;
use AppBundle\Entity\ProductStock;
use AppBundle\Entity\ProductSale;
use Doctrine\ORM\Query\ResultSetMapping;

/**
 * ProductSaleRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class ProductSaleRepository extends \Doctrine\ORM\EntityRepository
{
    /**
     * searchBy
     *
     * @param array $terms
     * @param $paginator
     * @return mixed
     */
    public function searchBy(array $terms, $paginator)
    {
        $query = $this->getQueryByTerms($terms);
        $pagination = $paginator->paginate($query, $terms['page'], $terms['limit']);

        return $pagination;
    }

    /**
     * findAllBy
     *
     * @param array $terms
     * @return array
     */
    public function findAllBy(array $terms)
    {
        $query = $this->getQueryByTerms($terms);
        return $query->getQuery()->getResult();
    }

    /**
     * getQueryByTerms
     *
     * @param $terms
     * @param $page
     * @param int $limit
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getQueryByTerms($terms)
    {
        /* @var $qb \Doctrine\ORM\QueryBuilder */
        $qb = $this->createQueryBuilder('ps');
        $qb->leftJoin(ProductStock::class, 'p', 'WITH', 'ps.productId=p.id');

        // filters data
        if (isset($terms['documents']) && $terms['documents'] != "") {
            $defined = false;
            foreach($qb->getDQLPart('join')['ps'] as $item) {
                if ($item->getJoin() == ProductStock::class) $defined = true;
            }
            if ($defined === false) {
                $qb->innerJoin(ProductStock::class, 'p', 'WITH', 'ps.productId=p.id');
            }
            $qb->andWhere('p.documents = :documents');
            $qb->setParameter('documents', $terms['documents']);
        }
        if (isset($terms['priceFrom']) && !empty($terms['priceFrom'])) {
            $qb->andWhere('p.priceByn >= :price_from');
            $qb->setParameter('price_from', $terms['priceFrom']);
        }
        if (isset($terms['priceTo']) && !empty($terms['priceTo'])) {
            $qb->andWhere('p.priceByn <= :price_to');
            $qb->setParameter('price_to', $terms['priceTo']);
        }
        if (isset($terms['dateFrom']) && !empty($terms['dateFrom'])) {
            $dateFrom = \DateTime::createFromFormat('d-m-Y', $terms['dateFrom']);
            $qb->andWhere('ps.date >= :date_from');
            $qb->setParameter('date_from', $dateFrom->format('Y-m-d 00:00:00'));
        }
        if (isset($terms['dateTo']) && !empty($terms['dateTo'])) {
            $dateTo = \DateTime::createFromFormat('d-m-Y', $terms['dateTo']);
            $qb->andWhere('ps.date <= :date_to');
            $qb->setParameter('date_to', $dateTo->format('Y-m-d 23:59:59'));
        }
        if (isset($terms['providerId']) && !empty($terms['providerId'])) {
            $qb->andWhere('p.providerId = :provider_id')
                ->setParameter('provider_id', $terms['providerId']);
        }
        if (isset($terms['title']) && !empty($terms['title'])) {
            $qb->andWhere('p.title LIKE :title')
                ->setParameter('title', '%'.$terms['title'].'%');
        }
        if (isset($terms['removeFromSite']) && !empty($terms['removeFromSite'])) {
            $qb->andWhere('p.qty = 0 AND ps.disabledRedBall = 0');
        }

        // sort by data
        if (isset($terms['sortCol']) && !empty($terms['sortCol'])) {
            $order = $terms['sortOrder'] ? $terms['sortOrder'] : 'ASC';
            if ($terms['sortCol'] == 'title') {
                $qb->orderBy('p.title', $order);
            }
            if ($terms['sortCol'] == 'total') {
                $qb->orderBy('ps.qty', $order);
            }
            if ($terms['sortCol'] == 'price-purchase') {
                $qb->orderBy('p.price', $order);
            }
            if ($terms['sortCol'] == 'price-purchase-byn') {
                $qb->orderBy('p.priceByn', $order);
            }
            if ($terms['sortCol'] == 'price-sale') {
                $qb->orderBy('ps.price', $order);
            }
            if ($terms['sortCol'] == 'price-sale-usd') {
                $qb->orderBy('ps.priceUsd', $order);
            }
            if ($terms['sortCol'] == 'price-profit-byn') {
                $qb->addSelect('SUM(ps.price*ps.qty-p.priceByn*ps.qty) AS total_profit_byn');
                $qb->orderBy('total_profit_byn', $order);
                $qb->groupBy('ps.id');
            }
            if ($terms['sortCol'] == 'price-profit') {
                $qb->addSelect('SUM(ps.price/:rate*ps.qty-p.price*ps.qty) AS total_profit');
                $qb->setParameter('rate', $terms['rate']);
                $qb->orderBy('total_profit', $order);
                $qb->groupBy('ps.id');
            }
            if ($terms['sortCol'] == 'price-date') {
                $qb->orderBy('ps.date', $order);
            }

        }
        else {
            // default sort
            $qb->orderBy('ps.date', 'DESC');
        }

        return $qb;
    }

    /**
     * searchNotCrossoutBy
     *
     * @param array $terms
     * @param $paginator
     * @param null $alreadyChanged
     * @return mixed
     */
    public function searchNotCrossoutBy(array $terms, $paginator, $alreadyChanged = null)
    {
        $query = $this->getQueryByTermsForNotCrossout($terms, $alreadyChanged);
        $pagination = $paginator->paginate($query, $terms['page'], $terms['limit']);

        return $pagination;
    }

    /**
     * findAllNotCrossoutBy
     *
     * @param array $terms
     * @return array
     */
    public function findAllNotCrossoutBy(array $terms)
    {
        $query = $this->getQueryByTermsForNotCrossout($terms);
        return $query->getQuery()->getResult();
    }

    /**
     * getQueryByTermsForNotCrossout
     *
     * @param $terms
     * @param null $alreadyChanged
     * @return \Doctrine\ORM\QueryBuilder
     * @internal param $page
     * @internal param int $limit
     */
    public function getQueryByTermsForNotCrossout($terms, $alreadyChanged = null)
    {
        /* @var $qb \Doctrine\ORM\QueryBuilder */
        $qb = $this->createQueryBuilder('ps')
                    ->leftJoin(ProductCrossout::class, 'pc', 'WITH', 'ps.id=pc.productSaleId')
                    ->where('pc.id IS NULL');
        $qb->where('pc.id IS NULL');

        // crossout table: if product whithout documents then not display
        $qb->innerJoin(ProductStock::class, 'p', 'WITH', 'ps.productId=p.id');
        $qb->andWhere('p.documents = 1');

        if ($alreadyChanged) {
            $qb->andWhere('ps.id not in (:alreadyChanged)');
            $qb->setParameter('alreadyChanged', $alreadyChanged);
        }

        // filters data
        if (isset($terms['priceFrom']) && !empty($terms['priceFrom'])) {
            $qb->andWhere('ps.price >= :price_from');
            $qb->setParameter('price_from', $terms['priceFrom']);
        }
        if (isset($terms['priceTo']) && !empty($terms['priceTo'])) {
            $qb->andWhere('ps.price <= :price_to');
            $qb->setParameter('price_to', $terms['priceTo']);
        }
        if (isset($terms['dateFrom']) && !empty($terms['dateFrom'])) {
            if (strripos($terms['dateFrom'], '/')){
                $dateFrom = \DateTime::createFromFormat('m/d/Y', $terms['dateFrom']);
            } else {
                $dateFrom = \DateTime::createFromFormat('d-m-Y', $terms['dateFrom']);
            }
            $qb->andWhere('ps.date >= :date_from');
            $qb->setParameter('date_from', $dateFrom->format('Y-m-d 00:00:00'));
        }
        if (isset($terms['dateTo']) && !empty($terms['dateTo'])) {
            if (strripos($terms['dateTo'], '/')){
                $dateTo = \DateTime::createFromFormat('m/d/Y', $terms['dateTo']);
            } else {
                $dateTo = \DateTime::createFromFormat('d-m-Y', $terms['dateTo']);
            }
            $qb->andWhere('ps.date <= :date_to');
            $qb->setParameter('date_to', $dateTo->format('Y-m-d 23:59:59'));
        }
        if (isset($terms['providerId']) && !empty($terms['providerId'])) {
            $qb->andWhere('p.providerId = :provider_id')
                ->setParameter('provider_id', $terms['providerId']);
        }
        if (isset($terms['title']) && !empty($terms['title'])) {
            $qb->andWhere('p.title LIKE :title')
                ->setParameter('title', '%'.$terms['title'].'%');
        }

        // sort by data
        if (isset($terms['sortCol']) && !empty($terms['sortCol'])) {
            $order = $terms['sortOrder'] ? $terms['sortOrder'] : 'ASC';
            if ($terms['sortCol'] == 'price-purchase-min-byn') {
                $qb->addSelect('MIN(p.priceByn) AS min_price');
                $qb->orderBy('min_price', $order);
                $qb->groupBy('ps.id');
            }
            if ($terms['sortCol'] == 'price-purchase-min') {
                $qb->addSelect('MIN(p.price) AS min_price');
                $qb->orderBy('min_price', $order);
                $qb->groupBy('ps.id');
            }
            if ($terms['sortCol'] == 'price-sale-min' || $terms['sortCol'] == 'price-sale-min-byn') {
                $qb->addSelect('MIN(ps.price) AS min_price');
                $qb->orderBy('min_price', $order);
                $qb->groupBy('ps.id');
            }
            if ($terms['sortCol'] == 'date') {
                $qb->orderBy('ps.date', $order);
            }
        }
        else {
            // default: sort by date
            $qb->orderBy('ps.date', 'DESC');
        }

        return $qb;
    }

    /**
     * getStatsNotCrossout
     *
     * @return array
     */
    public function getStatsNotCrossout($rate)
    {
        $stats = [];
        // current mounth
        $qb = $this->createQueryBuilder('ps')
            ->select('SUM(ps.price*ps.qty) AS sum_price, SUM(ps.qty) AS sum_qty')
            ->leftJoin(ProductCrossout::class, 'pc', 'WITH', 'ps.id=pc.productSaleId')
            ->leftJoin(ProductStock::class, 'p','WITH', 'ps.productId=p.id')
            ->where('pc.id IS NULL')
            ->andWhere('ps.date > :current_mounth_start AND ps.date < :current_mounth_end')
            ->setParameter('current_mounth_start', date('Y-m-01 00:00:00') )
            ->setParameter('current_mounth_end', date('Y-m-31 23:59:59') );

        $result = $qb->getQuery()->getResult();
        if (isset($result[0]['sum_price']) && !empty($result[0]['sum_price'])) {
            $stats['cur_mounth_price'] = $result[0]['sum_price'];
            $stats['cur_mounth_qty'] = $result[0]['sum_qty'];
        }
        else {
            $stats['cur_mounth_price'] = 0;
            $stats['cur_mounth_qty'] = 0;
        }

        // prev mounth
        $qb = $this->createQueryBuilder('ps')
            ->select('SUM(ps.price*ps.qty) AS sum_price, SUM(ps.qty) AS sum_qty')
            ->leftJoin(ProductCrossout::class, 'pc', 'WITH', 'ps.id=pc.productSaleId')
            ->leftJoin(ProductStock::class, 'p','WITH', 'ps.productId=p.id')
            ->where('pc.id IS NULL')
            ->andWhere('ps.date > :prev_mounth_start AND ps.date < :prev_mounth_end')
            ->setParameter('prev_mounth_start', date('Y-m-01 00:00:00', strtotime('last month')) )
            ->setParameter('prev_mounth_end', date('Y-m-31 23:59:59', strtotime('last month')) );
        $result = $qb->getQuery()->getResult();
        if (isset($result[0]['sum_price']) && !empty($result[0]['sum_price'])) {
            $stats['prev_mounth_price'] = $result[0]['sum_price'];
            $stats['prev_mounth_qty'] = $result[0]['sum_qty'];
        }
        else {
            $stats['prev_mounth_price'] = 0;
            $stats['prev_mounth_qty'] = 0;
        }

        // current quarter
        $quarter = ceil(date('n', time()) / 3);
        $quarter_mounth_end = $quarter*3;
        $quarter_mounth_start = $quarter*3 - 3;

        $quarter_date_start = new \DateTime();
        $quarter_date_start->setDate(date('Y'), $quarter_mounth_start , 1);
        $quarter_date_start->setTime(0,0,0);

        $quarter_date_end = new \DateTime();
        $quarter_date_end->setDate(date('Y'), $quarter_mounth_end+1 , 0);
        $quarter_date_end->setTime(23,59,59);

        $qb = $this->createQueryBuilder('ps')
            ->select('SUM(ps.price*ps.qty) AS sum_price, SUM(ps.qty) AS sum_qty')
            ->leftJoin(ProductCrossout::class, 'pc', 'WITH', 'ps.id=pc.productSaleId')
            ->leftJoin(ProductStock::class, 'p','WITH', 'ps.productId=p.id')
            ->where('pc.id IS NULL')
            ->andWhere('ps.date > :quarter_date_start AND ps.date < :quarter_date_end')
            ->setParameter('quarter_date_start', $quarter_date_start->format('Y-m-d H:i:s') )
            ->setParameter('quarter_date_end', $quarter_date_end->format('Y-m-d H:i:s') );
        $result = $qb->getQuery()->getResult();
        if (isset($result[0]['sum_price']) && !empty($result[0]['sum_price'])) {
            $stats['cur_quarter_price'] = $result[0]['sum_price'];
            $stats['cur_quarter_qty'] = $result[0]['sum_qty'];
        }
        else {
            $stats['cur_quarter_price'] = 0;
            $stats['cur_quarter_qty'] = 0;
        }

        // prev quarter
        $quarter = ceil(date('n', time()) / 3)-1;
        $quarter_mounth_end = $quarter*3;
        $quarter_mounth_start = $quarter*3 - 3;

        $quarter_date_start = new \DateTime();
        $quarter_date_start->setDate(date('Y'), $quarter_mounth_start , 1);
        $quarter_date_start->setTime(0,0,0);

        $quarter_date_end = new \DateTime();
        $quarter_date_end->setDate(date('Y'), $quarter_mounth_end+1 , 0);
        $quarter_date_end->setTime(23,59,59);

        $qb = $this->createQueryBuilder('ps')
            ->select('SUM(ps.price*ps.qty) AS sum_price, SUM(ps.qty) AS sum_qty')
            ->leftJoin(ProductCrossout::class, 'pc', 'WITH', 'ps.id=pc.productSaleId')
            ->leftJoin(ProductStock::class, 'p','WITH', 'ps.productId=p.id')
            ->where('pc.id IS NULL')
            ->andWhere('ps.date > :quarter_date_start AND ps.date < :quarter_date_end')
            ->setParameter('quarter_date_start', $quarter_date_start->format('Y-m-d H:i:s') )
            ->setParameter('quarter_date_end', $quarter_date_end->format('Y-m-d H:i:s') );
        $result = $qb->getQuery()->getResult();
        if (isset($result[0]['sum_price']) && !empty($result[0]['sum_price'])) {
            $stats['prev_quarter_price'] = $result[0]['sum_price'];
            $stats['prev_quarter_qty'] = $result[0]['sum_qty'];
        }
        else {
            $stats['prev_quarter_price'] = 0;
            $stats['prev_quarter_qty'] = 0;
        }

        // current year
        $qb = $this->createQueryBuilder('ps')
            ->select('SUM(ps.price*ps.qty) AS sum_price, SUM(ps.qty) AS sum_qty')
            ->leftJoin(ProductCrossout::class, 'pc', 'WITH', 'ps.id=pc.productSaleId')
            ->leftJoin(ProductStock::class, 'p','WITH', 'ps.productId=p.id')
            ->where('pc.id IS NULL')
            ->andWhere('ps.date > :current_year_start AND ps.date < :current_year_end')
            ->setParameter('current_year_start', date('Y-01-01 00:00:00') )
            ->setParameter('current_year_end', date('Y-12-31 23:59:59') );
        $result = $qb->getQuery()->getResult();
        if (isset($result[0]['sum_price']) && !empty($result[0]['sum_price'])) {
            $stats['cur_year_price_byn'] = $result[0]['sum_price'];
            $stats['cur_year_price'] = $result[0]['sum_price']/$rate;
            $stats['cur_year_qty'] = $result[0]['sum_qty'];
        }
        else {
            $stats['cur_year_price_byn'] = 0;
            $stats['cur_year_qty'] = 0;
        }

        return $stats;
    }

    /**
     * Get Stats
     *
     * @return array
     */
    public function getStats($rate)
    {
        $stats = [];
        // current mounth
        $qb = $this->createQueryBuilder('ps')
                ->select('SUM(ps.price*ps.qty) AS sum_price, SUM(ps.qty) AS sum_qty')
                ->where('ps.date > :current_mounth_start AND ps.date < :current_mounth_end')
                ->setParameter('current_mounth_start', date('Y-m-01 00:00:00') )
                ->setParameter('current_mounth_end', date('Y-m-31 23:59:59') );
        $result = $qb->getQuery()->getResult();
        if (isset($result[0]['sum_price']) && !empty($result[0]['sum_price'])) {
            $stats['cur_mounth_price'] = $result[0]['sum_price'];
            $stats['cur_mounth_qty'] = $result[0]['sum_qty'];
        }
        else {
            $stats['cur_mounth_price'] = 0;
            $stats['cur_mounth_qty'] = 0;
        }

        // prev mounth
        $qb = $this->createQueryBuilder('ps')
            ->select('SUM(ps.price*ps.qty) AS sum_price, SUM(ps.qty) AS sum_qty')
            ->where('ps.date > :prev_mounth_start AND ps.date < :prev_mounth_end')
            ->setParameter('prev_mounth_start', date('Y-m-01 00:00:00', strtotime('last month')) )
            ->setParameter('prev_mounth_end', date('Y-m-31 23:59:59', strtotime('last month')) );
        $result = $qb->getQuery()->getResult();
        if (isset($result[0]['sum_price']) && !empty($result[0]['sum_price'])) {
            $stats['prev_mounth_price'] = $result[0]['sum_price'];
            $stats['prev_mounth_qty'] = $result[0]['sum_qty'];
        }
        else {
            $stats['prev_mounth_price'] = 0;
            $stats['prev_mounth_qty'] = 0;
        }

        // current quarter
        $quarter = ceil(date('n', time()) / 3);
        $quarter_mounth_end = $quarter*3;
        $quarter_mounth_start = $quarter*3 - 3;

        $quarter_date_start = new \DateTime();
        $quarter_date_start->setDate(date('Y'), $quarter_mounth_start , 1);
        $quarter_date_start->setTime(0,0,0);

        $quarter_date_end = new \DateTime();
        $quarter_date_end->setDate(date('Y'), $quarter_mounth_end+1 , 0);
        $quarter_date_end->setTime(23,59,59);

        $qb = $this->createQueryBuilder('ps')
            ->select('SUM(ps.price*ps.qty) AS sum_price, SUM(ps.qty) AS sum_qty')
            ->where('ps.date > :quarter_date_start AND ps.date < :quarter_date_end')
            ->setParameter('quarter_date_start', $quarter_date_start->format('Y-m-d H:i:s') )
            ->setParameter('quarter_date_end', $quarter_date_end->format('Y-m-d H:i:s') );
        $result = $qb->getQuery()->getResult();
        if (isset($result[0]['sum_price']) && !empty($result[0]['sum_price'])) {
            $stats['cur_quarter_price'] = $result[0]['sum_price'];
            $stats['cur_quarter_qty'] = $result[0]['sum_qty'];
        }
        else {
            $stats['cur_quarter_price'] = 0;
            $stats['cur_quarter_qty'] = 0;
        }

        // prev quarter
        $quarter = ceil(date('n', time()) / 3)-1;
        $quarter_mounth_end = $quarter*3;
        $quarter_mounth_start = $quarter*3 - 3;

        $quarter_date_start = new \DateTime();
        $quarter_date_start->setDate(date('Y'), $quarter_mounth_start , 1);
        $quarter_date_start->setTime(0,0,0);

        $quarter_date_end = new \DateTime();
        $quarter_date_end->setDate(date('Y'), $quarter_mounth_end+1 , 0);
        $quarter_date_end->setTime(23,59,59);

        $qb = $this->createQueryBuilder('ps')
            ->select('SUM(ps.price*ps.qty) AS sum_price, SUM(ps.qty) AS sum_qty')
            ->where('ps.date > :quarter_date_start AND ps.date < :quarter_date_end')
            ->setParameter('quarter_date_start', $quarter_date_start->format('Y-m-d H:i:s') )
            ->setParameter('quarter_date_end', $quarter_date_end->format('Y-m-d H:i:s') );
        $result = $qb->getQuery()->getResult();
        if (isset($result[0]['sum_price']) && !empty($result[0]['sum_price'])) {
            $stats['prev_quarter_price'] = $result[0]['sum_price'];
            $stats['prev_quarter_qty'] = $result[0]['sum_qty'];
        }
        else {
            $stats['prev_quarter_price'] = 0;
            $stats['prev_quarter_qty'] = 0;
        }

        // current year
        $qb = $this->createQueryBuilder('ps')
            ->select('SUM(ps.price*ps.qty) AS sum_price, SUM(ps.qty) AS sum_qty')
            ->where('ps.date > :current_year_start AND ps.date < :current_year_end')
            ->setParameter('current_year_start', date('Y-01-01 00:00:00') )
            ->setParameter('current_year_end', date('Y-12-31 23:59:59') );
        $result = $qb->getQuery()->getResult();
        if (isset($result[0]['sum_price']) && !empty($result[0]['sum_price'])) {
            $stats['cur_year_price_byn'] = $result[0]['sum_price'];
            $stats['cur_year_price'] = $result[0]['sum_price']/$rate;
            $stats['cur_year_qty'] = $result[0]['sum_qty'];
        }
        else {
            $stats['cur_year_price_byn'] = 0;
            $stats['cur_year_qty'] = 0;
        }

        return $stats;
    }

    /**
     * getTotalBy
     *
     * @param $terms
     * @return mixed
     */
    public function getTotalBy($terms)
    {
        unset($terms['sortCol']);
        $total = [];
        $qb = $this->getQueryByTerms($terms);
        /* TODO: rate? which rate we must use? (current or date sale) */
        $qb->select('SUM(ps.qty) AS total_qty,
                           SUM(ps.price) AS total_sale_price,
                           SUM(p.price) AS total_purchase_price,
                           SUM(p.priceByn) AS total_purchase_price_byn,
                           SUM(ps.price*ps.qty-p.priceByn*ps.qty) AS total_profit_byn,
                           SUM((ps.price*ps.qty-p.priceByn*ps.qty)/:rate) AS total_profit')
            ->setParameter('rate', $terms['rate']);

        $result = $qb->getQuery()->getResult();
        if (isset($result[0]['total_qty']) && !empty($result[0]['total_qty'])) {
            $total['qty'] = $result[0]['total_qty'];
            $total['sale_price'] = $result[0]['total_sale_price'];
            $total['purchase_price'] = $result[0]['total_purchase_price'];
            $total['purchase_price_byn'] = $result[0]['total_purchase_price_byn'];
            $total['profit_byn'] = $result[0]['total_profit_byn'];
            $total['profit'] = $result[0]['total_profit'];
        }

        return $total;
    }

    /**
     * @return array
     */
    public function getTotalNotCrossout()
    {
        $total = [];

        $qb = $this->createQueryBuilder('ps')
            ->select('SUM(ps.qty) AS total_qty, SUM(ps.price) AS total_sale_price, SUM(p.price) AS total_purchase_price, SUM(p.priceByn) AS total_purchase_price_byn')
            ->leftJoin(ProductCrossout::class, 'pc', 'WITH', 'ps.id=pc.productSaleId')
            ->leftJoin(ProductStock::class, 'p','WITH', 'ps.productId=p.id')
            ->where('pc.id IS NULL');

        $result = $qb->getQuery()->getResult();
        if (isset($result[0]['total_qty']) && !empty($result[0]['total_qty'])) {
            $total['qty'] = $result[0]['total_qty'];
            $total['sale_price'] = $result[0]['total_sale_price'];
            $total['purchase_price'] = $result[0]['total_purchase_price'];
            $total['purchase_price_byn'] = $result[0]['total_purchase_price_byn'];
        }

        return $total;
    }

    /**
     * getTotalNotCrossoutBy
     *
     * @param $terms
     * @return array
     */
    public function getTotalNotCrossoutBy($terms)
    {
        unset($terms['sortCol']);
        $total = [];
        $qb = $this->getQueryByTermsForNotCrossout($terms);
        $qb->select('SUM(ps.qty) AS total_qty,
                           SUM(ps.price*ps.qty) AS total_sale_price,
                           SUM(p.price*ps.qty) AS total_purchase_price,
                           SUM(p.priceByn*ps.qty) AS total_purchase_price_byn');

        $defined = false;
        foreach($qb->getDQLPart('join')['ps'] as $item) {
            if ($item->getJoin() == ProductStock::class) $defined = true;
        }
        if ($defined === false) {
            $qb->innerJoin(ProductStock::class, 'p', 'WITH', 'ps.productId=p.id');
        }

        $result = $qb->getQuery()->getResult();
        if (isset($result[0]['total_qty']) && !empty($result[0]['total_qty'])) {
            $total['qty'] = $result[0]['total_qty'];
            $total['sale_price'] = $result[0]['total_sale_price'];
            $total['purchase_price'] = $result[0]['total_purchase_price'];
            $total['purchase_price_byn'] = $result[0]['total_purchase_price_byn'];
        }

        return $total;
    }

    /**
     * findSimilarNames
     *
     * @param type $title
     */
    public function findSimilarNames($title)
    {
        $result = $this->createQueryBuilder('ps')
            ->innerJoin(ProductStock::class, 'p', 'WITH', 'ps.productId=p.id')
            ->where('p.title LIKE :title')
            ->andWhere('p.documents = 1')
            ->setParameter('title', $title . '%')
            ->orderBy('ps.price', 'DESC')
            ->getQuery()
            ->getResult();

        return $result;
    }

    /**
     * findStatsByMounth
     *
     * @return array
     */
    public function findStatsByMonth($terms)
    {
        $date = new \DateTime();
        $qb = $this->createQueryBuilder('ps')
            ->select('SUM(ps.priceUsd*ps.qty) AS total_price,
                            SUM(ps.qty) AS total_qty,
                            SUM(ps.price*ps.qty) AS total_price_byn,
                            YEAR(ps.date) AS year,
                            MONTH(ps.date) AS month')
            ->where('YEAR(ps.date) = :cur_year')
            ->setParameter('cur_year', $date->format('Y'))

            ->groupBy('year, month');

        $tmp = $qb->getQuery()->getResult();
        $result = [];
        foreach ($tmp as $item) {
            $result[$item['month']] = $item;
        }

        return $result;
    }

    /**
     * findStatsByMounth
     *
     * @return array
     */
    public function findNotCrossoutStatsByMonth($terms)
    {
        $date = new \DateTime();
        $qb = $this->createQueryBuilder('ps')
            ->select('SUM(ps.price*ps.qty)/:rate AS total_price,
                            SUM(ps.qty) AS total_qty,
                            SUM(ps.price*ps.qty) AS total_price_byn,
                            YEAR(ps.date) AS year,
                            MONTH(ps.date) AS month')
            ->leftJoin(ProductCrossout::class, 'pc', 'WITH', 'ps.id=pc.productSaleId')
            ->where('pc.id IS NULL')
            ->andWhere('YEAR(ps.date) = :cur_year')
            ->setParameter('cur_year', $date->format('Y'))
            ->setParameter('rate', $terms['rate'])
            ->groupBy('year, month');

        $tmp = $qb->getQuery()->getResult();
        $result = [];
        foreach ($tmp as $item) {
            $result[$item['month']] = $item;
        }

        return $result;
    }

    /**
     * findProfitByMonth
     *
     * @return mixed
     */
    public function findDaysProfitByMonth($month, $terms)
    {
        if (is_null($terms['rate']) || empty($terms['rate'])) {
            $terms['rate'] = 1;
        }
        $date = new \DateTime();
        $qb = $this->createQueryBuilder('ps');
        $qb->select('SUM((ps.price*ps.qty)/:rate) AS total_price,
                            YEAR(ps.date) AS year,
                            MONTH(ps.date) AS month,
                            DAY(ps.date) AS day')
           ->andWhere('MONTH(ps.date) = :month')
           ->andWhere('YEAR(ps.date) = :cur_year')
           ->setParameter('cur_year', $date->format('Y'))
           ->setParameter('month', $month)
           ->setParameter('rate', $terms['rate'])
           ->groupBy('year, month, day');

        $tmp = $qb->getQuery()->getResult();
        $result = [];
        foreach($tmp as $item) {
            $result[$item['day']] = $item;
        }
        return $result;
    }

    /**
     * findWeeksStatsByMonth
     *
     * @param $month
     * @return array
     */
    public function findWeeksStatsByMonth($month)
    {
        $date = new \DateTime();
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('total_qty', 'total_qty');
        $rsm->addScalarResult('total_price', 'total_price');
        $rsm->addScalarResult('y', 'y');
        $rsm->addScalarResult('m', 'm');
        $rsm->addScalarResult('w', 'w');
        $qb = $this->getEntityManager()->createNativeQuery('
            SELECT SUM(ps.qty) AS total_qty,
                   SUM(ps.price*ps.qty) AS total_price,
                   YEAR(ps.date) AS y, MONTH(ps.date) AS m,
                   (CASE WHEN DAY(ps.date) < 8 THEN 1 
                    WHEN DAY(ps.date) > 7 AND DAY(ps.date) < 15 THEN 2
                    WHEN DAY(ps.date) > 14 AND DAY(ps.date) < 22 THEN 3
                    WHEN DAY(ps.date) > 21 AND DAY(ps.date) < 29 THEN 4
                    ELSE 5 END) AS w
                FROM product_sale AS ps
                LEFT JOIN product_crossout AS pc ON ps.id=pc.product_sale_id 
                    WHERE
                        pc.id IS NULL
                      AND MONTH(ps.date) = :mon
                      AND YEAR(ps.date) = :cur_year
                    GROUP BY y, m, w', $rsm)
            ->setParameter('cur_year', $date->format('Y'))
            ->setParameter('mon', $month);

        $tmp = $qb->getResult();
        $result = [];
        foreach($tmp as $item) {
            $item['year'] = $item['y'];
            $item['month'] = $item['m'];
            $item['week'] = $item['w'];
            $result[$item['week']] = $item;
        }
        return $result;
    }
}
