<?php

namespace AppBundle\Entity;

/**
 * ProductSale
 *
 * Проданные
 */
class ProductSale
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var int
     */
    private $productId;

    /**
     * @var ProductStock
     */
    private $product;

    /**
     * @var int
     */
    private $bunchId;

    /**
     * @var \Entity
     */
    private $bunch;

    /**
     * @var int
     */
    private $qty;

    /**
     * @var string
     */
    private $price;

    /**
     * @var string
     */
    private $priceUsd;

    /**
     * @var \DateTime
     */
    private $date;

    /**
     * @var boolean
     */
    private $disabledRedBall;

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
     * Set productId
     *
     * @param integer $productId
     *
     * @return ProductSale
     */
    public function setProductId($productId)
    {
        $this->productId = $productId;

        return $this;
    }

    /**
     * Get productId
     *
     * @return int
     */
    public function getProductId()
    {
        return $this->productId;
    }

    /**
     * Set product
     *
     * @param ProductStock $product
     *
     * @return ProductCrossout
     */
    public function setProduct($product)
    {
        $this->product = $product;

        return $this;
    }

    /**
     * Get product
     *
     * @return ProductStock
     */
    public function getProduct()
    {
        return $this->product;
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
     * Set qty
     *
     * @param integer $qty
     *
     * @return ProductSale
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
     * Set price
     *
     * @param string $price
     *
     * @return ProductSale
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
     * Set priceUsd
     *
     * @param string $priceUsd
     *
     * @return ProductSale
     */
    public function setPriceUsd($priceUsd)
    {
        $this->priceUsd = $priceUsd;

        return $this;
    }

    /**
     * Get price
     *
     * @return string
     */
    public function getPriceUsd()
    {
        return $this->priceUsd;
    }

    /**
     * Set date
     *
     * @param \DateTime $date
     *
     * @return ProductSale
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date
     *
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * getTitle
     *
     * @return string
     */
    public function getTitle()
    {
        if ($this->getProduct() instanceof ProductStock) {
            return $this->getProduct()->getTitle();
        }
        return '-';
    }

    /**
     * getPurchasePriceByn
     *
     * @return float
     */
    public function getPurchasePriceByn()
    {
        if ($this->getProduct() instanceof ProductStock) {
            return $this->getProduct()->getPriceByn();
        }
        return 0;
    }

    /**
     * getPurchasePrice
     *
     * @return float
     */
    public function getPurchasePrice()
    {
        if ($this->getProduct() instanceof ProductStock) {
            return $this->getProduct()->getPrice();
        }
        return 0;
    }

    /**
     * getQtyInStock
     *
     * @return int
     */
    public function getQtyInStock()
    {
        if ($this->getProduct() instanceof ProductStock) {
            return $this->getProduct()->getQty();
        }
        return 0;
    }

    /**
     * setDisabledRedBall
     *
     * @param boolean $disabledRedBall
     *
     * @return ProductSale
     */
    public function setDisabledRedBall($disabledRedBall)
    {
        $this->disabledRedBall = $disabledRedBall;

        return $this;
    }

    /**
     * getDisabledRedBall
     *
     * @return boolean
     */
    public function getDisabledRedBall()
    {
        return $this->disabledRedBall;
    }
}

