<?php

namespace AppBundle\Entity;

/**
 * ProductStock
 */
class ProductStock
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $title;

    /**
     * @var int
     */
    private $bunchId;

    /**
     * @var int
     */
    private $providerId;

    /**
     * @var string
     */
    private $price;

    /**
     * @var string
     */
    private $priceByn;

    /**
     * @var string
     */
    private $selfcost;

    /**
     * @var int
     */
    private $qty;

    /**
     * @var float
     */
    private $nds;

    /**
     * @var float
     */
    private $weightQty;

    /**
     * @var float
     */
    private $weight;

    /**
     * @var boolean
     */
    private $star1;

    /**
     * @var boolean
     */
    private $star2;

    /**
     * @var boolean
     */
    private $star3;

    /**
     * @var boolean
     */
    private $sale = false;

    /**
     * @var boolean
     */
    private $scrap = false;

    /**
     * @var enum
     */
    private $ball;

    const RED_BALL = 'red'; // It mean that the bunch is new
    const BLUE_BALL = 'blue'; // It mean that the bunch was empty and become full
    const GREEN_BALL = 'green'; // It mean that the in bunch added new product

    /**
     * @var boolean
     */
    private $documents;

    /**
     * @var \DateTime
     */
    private $created;

    /**
     * @var \DateTime
     */
    private $updated;

    /**
     * @var \Entity
     */
    private $bunch;

    /**
     * @var array of \Entity
     */
    private $productsSale;

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set title
     *
     * @param string $title
     *
     * @return ProductStock
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set bunchId
     *
     * @param integer $bunchId
     *
     * @return ProductStock
     */
    public function setBunchId($bunchId)
    {
        $this->bunchId = $bunchId;

        return $this;
    }

    /**
     * Get bunchId
     *
     * @return int
     */
    public function getBunchId()
    {
        return $this->bunchId;
    }

    /**
     * Set providerId
     *
     * @param integer $providerId
     *
     * @return ProductStock
     */
    public function setProviderId($providerId)
    {
        $this->providerId = $providerId;

        return $this;
    }

    /**
     * Get providerId
     *
     * @return int
     */
    public function getProviderId()
    {
        return $this->providerId;
    }

    /**
     * Set price
     *
     * @param string $price
     *
     * @return ProductStock
     */
    public function setPrice($price)
    {
        $this->price = $price;

        return $this;
    }

    /**
     * Get price
     *
     * @return string
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * Set priceByn
     *
     * @param string $priceByn
     *
     * @return ProductStock
     */
    public function setPriceByn($priceByn)
    {
        $this->priceByn = $priceByn;

        return $this;
    }

    /**
     * Get priceByn
     *
     * @return string
     */
    public function getPriceByn()
    {
        return $this->priceByn;
    }

    /**
     * Set selfcost
     *
     * @param string $selfcost
     *
     * @return ProductStock
     */
    public function setSelfcost($selfcost)
    {
        $this->selfcost = $selfcost;

        return $this;
    }

    /**
     * Get selfcost
     *
     * @return string
     */
    public function getSelfcost()
    {
        return $this->selfcost;
    }

    /**
     * Set qty
     *
     * @param integer $qty
     *
     * @return ProductStock
     */
    public function setQty($qty)
    {
        $this->qty = $qty;

        return $this;
    }

    /**
     * Get qty
     *
     * @return int
     */
    public function getQty()
    {
        return $this->qty;
    }

    /**
     * Set nds
     *
     * @param float $nds
     *
     * @return ProductStock
     */
    public function setNds($nds)
    {
        $this->nds = $nds;

        return $this;
    }

    /**
     * Get nds
     *
     * @return float
     */
    public function getNds()
    {
        return $this->nds;
    }

    /**
     * Set weightQty
     *
     * @param float $weightQty
     *
     * @return ProductStock
     */
    public function setWeightQty($weightQty)
    {
        $this->weightQty = $weightQty;

        return $this;
    }

    /**
     * Get weightQty
     *
     * @return float
     */
    public function getWeightQty()
    {
        return $this->weightQty;
    }

    /**
     * Set weight
     *
     * @param float $weight
     *
     * @return ProductStock
     */
    public function setWeight($weight)
    {
        $this->weight = $weight;

        return $this;
    }

    /**
     * Get weight
     *
     * @return float
     */
    public function getWeight()
    {
        return $this->weight;
    }

    /**
     * Set Star1
     *
     * @param boolean $star
     *
     * @return ProductStock
     */
    public function setStar1($star)
    {
        $this->star1 = $star;

        return $this;
    }

    /**
     * getStar1
     *
     * @return bool
     */
    public function getStar1()
    {
        return $this->star1;
    }

    /**
     * Set Star2
     *
     * @param boolean $star
     *
     * @return ProductStock
     */
    public function setStar2($star)
    {
        $this->star2 = $star;

        return $this;
    }

    /**
     * getStar2
     *
     * @return bool
     */
    public function getStar2()
    {
        return $this->star2;
    }

    /**
     * Set Star3
     *
     * @param boolean $star
     *
     * @return ProductStock
     */
    public function setStar3($star)
    {
        $this->star3 = $star;

        return $this;
    }

    /**
     * getStar3
     *
     * @return bool
     */
    public function getStar3()
    {
        return $this->star3;
    }

    /**
     * Set Ball
     *
     * @param boolean $ball
     *
     * @return ProductStock
     */
    public function setBall($ball)
    {
        $this->ball = $ball;

        return $this;
    }

    /**
     * getBall
     *
     * @return string
     */
    public function getBall()
    {
        return $this->ball;
    }

    /**
     * getSale
     *
     * @return bool
     */
    public function getSale()
    {
        return $this->sale;
    }

    /**
     * Set Sale
     *
     * @param boolean $flag
     *
     * @return ProductStock
     */
    public function setSale($flag)
    {
        $this->sale = $flag;

        return $this;
    }

    /**
     * getScap
     *
     * @return bool
     */
    public function getScrap()
    {
        return $this->scrap;
    }

    /**
     * Set scap
     *
     * @param boolean $flag
     *
     * @return ProductStock
     */
    public function setScrap($flag)
    {
        $this->scrap = $flag;

        return $this;
    }

    /**
     * Set documents
     *
     * @param $documents
     *
     * @return $this
     */
    public function setDocuments($documents)
    {
        $this->documents = $documents;

        return $this;
    }

    /**
     * Get documents
     *
     * @return boolean
     */
    public function getDocuments()
    {
        return $this->documents;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     *
     * @return ProductStock
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Get created
     *
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set updated
     *
     * @param \DateTime $updated
     *
     * @return ProductStock
     */
    public function setUpdated($updated)
    {
        $this->updated = $updated;

        return $this;
    }

    /**
     * Get updated
     *
     * @return \DateTime
     */
    public function getUpdated()
    {
        return $this->updated;
    }

    /**
     * Set Bunch
     *
     * @param |Entity $bunch
     *
     * @return ProductStock
     */
    public function setBunch($bunch)
    {
        $this->bunch = $bunch;

        return $this;
    }

    /**
     * Get Bunch
     *
     * @return int
     */
    public function getBunch()
    {
        return $this->bunch;
    }

    /**
     * Set Products Sale
     *
     * @param array of |Entity $products
     *
     * @return ProductStock
     */
    public function setProductsSale($products)
    {
        $this->productsSale = $products;

        return $this;
    }

    /**
     * Get Products Sale
     *
     * @return array
     */
    public function getProductsSale()
    {
        return $this->productsSale;
    }

    /**
     * Get DateDiff
     *
     * @return \DateInterval|false
     */
    public function getDateDiff()
    {
        $date = $this->getCreated();
        $now = new \DateTime();

        $interval = date_diff($date, $now);
        return $interval;
    }

}

