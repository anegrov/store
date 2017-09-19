<?php

namespace AppBundle\Entity;

use AppBundle\Entity\ProductStock;

/**
 * ProductCrossout
 *
 * Списанные
 */
class ProductCrossout
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var int
     */
    private $productSaleId;

    /**
     * @var ProductSale
     */
    private $productSale;

    /**
     * @var int
     */
    private $productSaleCrossoutId;

    /**
     * @var ProductSale
     */
    private $productSaleCrossout;

    /**
     * @var int
     */
    private $qty;

    /**
     * @var \DateTime
     */
    private $price;

    /**
     * @var \DateTime
     */
    private $date;


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
     * Set Product Sale Id
     *
     * @param integer $productId
     *
     * @return ProductCrossout
     */
    public function setProductSaleId($productId)
    {
        $this->productSaleId = $productId;

        return $this;
    }

    /**
     * Get productId
     *
     * @return int
     */
    public function getProductSaleId()
    {
        return $this->productSaleId;
    }

    /**
     * Set Product Sale
     *
     * @param ProductSale $product
     *
     * @return ProductCrossout
     */
    public function setProductSale($product)
    {
        $this->productSale = $product;

        return $this;
    }

    /**
     * Get product
     *
     * @return ProductStock
     */
    public function getProductSale()
    {
        return $this->productSale;
    }

    /**
     * setProductSaleCrossoutId
     *
     * @param integer $productCrossoutId
     *
     * @return ProductCrossout
     */
    public function setProductSaleCrossoutId($productCrossoutId)
    {
        $this->productSaleCrossoutId = $productCrossoutId;

        return $this;
    }

    /**
     * getProductSaleCrossoutId
     *
     * @return int
     */
    public function getProductSaleCrossoutId()
    {
        return $this->productSaleCrossoutId;
    }

    /**
     * setProductSaleCrossout
     *
     * @param ProductSale $product
     *
     * @return ProductCrossout
     */
    public function setProductSaleCrossout($product)
    {
        $this->productSaleCrossout = $product;

        return $this;
    }

    /**
     * getProductSaleCrossout
     *
     * @return ProductSale
     */
    public function getProductSaleCrossout()
    {
        return $this->productSaleCrossout;
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
     * @param \DateTime $price
     *
     * @return ProductCrossout
     */
    public function setPrice($price)
    {
        $this->price = $price;

        return $this;
    }

    /**
     * Get price
     *
     * @return \DateTime
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * Set date
     *
     * @param \DateTime $date
     *
     * @return ProductCrossout
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
        if ($this->getProductSale() instanceof ProductSale) {
            if ($this->getProductSale()->getProduct() instanceof ProductStock){
                return $this->getProductSale()->getProduct()->getTitle();
            }
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
        if ($this->getProductSale() instanceof ProductSale) {
            if ($this->getProductSale()->getProduct() instanceof ProductStock){
                return $this->getProductSale()->getProduct()->getPriceByn();
            }
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
        if ($this->getProductSale() instanceof ProductSale) {
            if ($this->getProductSale()->getProduct() instanceof ProductStock){
                return $this->getProductSale()->getProduct()->getPrice();
            }
        }
        return 0;
    }
}

