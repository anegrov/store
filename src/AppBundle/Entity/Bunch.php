<?php

namespace AppBundle\Entity;

/**
 * Bunch
 */
class Bunch
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
     * @var string
     */
    private $total;

    /**
     * @var bool
     */
    private $availability;

    /**
     * @var \DateTime
     */
    private $created;

    /**
     * @var \DateTime
     */
    private $updated;

    /**
     * @var array of \Entity
     */
    private $products;

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
     * @var enum
     */
    private $ball;

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
     * @return Product
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
     * Set total
     *
     * @param string $total
     *
     * @return Product
     */
    public function setTotal($total)
    {
        $this->total = $total;

        return $this;
    }

    /**
     * Get total
     *
     * @return string
     */
    public function getTotal()
    {
        return $this->total;
    }

    /**
     * Set availability
     *
     * @param boolean $availability
     *
     * @return Product
     */
    public function setAvailability($availability)
    {
        $this->availability = $availability;

        return $this;
    }

    /**
     * Get availability
     *
     * @return bool
     */
    public function getAvailability()
    {
        return $this->availability;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     *
     * @return Product
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
     * @return Product
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
     * setProducts
     *
     * @param $products
     * @return $this
     */
    public function setProducts($products)
    {
        $this->products = $products;

        return $this;
    }

    /**
     * getProducts
     *
     * @return array
     */
    public function getProducts()
    {
        return $this->products;
    }

    /**
     * setProducts
     *
     * @param $products
     * @return $this
     */
    public function setProductsSale($products)
    {
        $this->productsSale = $products;

        return $this;
    }

    /**
     * getProducts
     *
     * @return array
     */
    public function getProductsSale()
    {
        return $this->productsSale;
    }

    /**
     * Get Average Price
     *
     * @return float|int
     */
    public function getAveragePrice()
    {
        $products = $this->getProducts();
        $averagePrice = 0;
        if (count($products)>0) {
            foreach ($products as $item) {
                $averagePrice += $item->getPrice();
            }
            $averagePrice = $averagePrice/count($products);
        }

        return $averagePrice;
    }

    /**
     * Get Average Price BYN
     *
     * @return float|int
     */
    public function getAveragePriceByn()
    {
        $products = $this->getProducts();
        $averagePrice = 0;
        if (count($products)>0) {
            foreach ($products as $item) {
                $averagePrice += $item->getPriceByn();
            }
            $averagePrice = $averagePrice/count($products);
        }

        return $averagePrice;
    }

    /**
     * getLastProduct
     *
     * @return null|\Entity
     */
    public function getLastProduct()
    {
        $products = $this->getProducts();
        $lastProduct = NULL;
        if (count($products)>0) {
            $lastProduct = $products->last();
            $lastDate = $lastProduct->getCreated();
            foreach ($products as $product) {
                if ( $lastDate > $product->getCreated() ) {
                    $lastProduct = $product;
                    $lastDate = $product->getCreated();
                }
            }
        }
        return $lastProduct;
    }

    /**
     * getLastProductPrice
     *
     * @return int|float
     */
    public function getLastProductPrice()
    {
        $product = $this->getLastProduct();
        return (!empty($product) ? $product->getPriceByn() : 0);
    }

    /**
     * getMaxProductByPrice
     *
     * @return mixed|null
     */
    public function getMaxProductByPrice()
    {
        $products = $this->getProducts();
        $returnProductPrice = 0;
        $returnProduct = null;
        if (count($products)>0) {
            foreach ($products as $product) {
                if ($product->getPrice() > $returnProductPrice) {
                    $returnProduct = $product;
                    $returnProductPrice = $product->getPrice();
                }
            }
        }
        return $returnProduct;
    }

    /**
     * getMaxProductByPriceByn
     *
     * @return mixed|null
     */
    public function getMaxProductByPriceByn()
    {
        $products = $this->getProducts();
        $returnProductPrice = 0;
        $returnProduct = null;
        if (count($products)>0) {
            foreach ($products as $product) {
                if ($product->getPriceByn() > $returnProductPrice) {
                    $returnProduct = $product;
                    $returnProductPrice = $product->getPriceByn();
                }
            }
        }
        return $returnProduct;
    }

    /**
     * getMaxProductSaleByPriceByn
     *
     * @return mixed|null
     */
    public function getMaxProductSaleByPriceByn()
    {
        $products = $this->getProductsSale();
        $returnProductPrice = 0;
        $returnProduct = null;
        if (count($products)>0) {
            foreach ($products as $product) {
                if ($product->getPrice() > $returnProductPrice) {
                    $returnProduct = $product;
                    $returnProductPrice = $product->getPrice();
                }
            }
        }
        return $returnProduct;
    }

    /**
     * getMaxPrice
     *
     * @return int
     */
    public function getMaxPrice()
    {
        $product = $this->getMaxProductByPrice();
        return $product ? $product->getPrice() : 0;
    }

    /**
     * getMaxPrice
     *
     * @return int
     */
    public function getMaxSalePrice($rate)
    {
        $product = $this->getMaxProductSaleByPriceByn();
        return $product ? $product->getPrice()/$rate : 0;
    }

    /**
     * getMaxPriceByn
     *
     * @return int
     */
    public function getMaxPriceByn()
    {
        $product = $this->getMaxProductByPriceByn();
        return $product ? $product->getPriceByn() : 0;
    }

    /**
     * getMaxSalePriceByn
     *
     * @return int
     */
    public function getMaxSalePriceByn()
    {
        $product = $this->getMaxProductSaleByPriceByn();
        return $product ? $product->getPrice() : 0;
    }

    /**
     * getMaxProductByPrice
     *
     * @return mixed|null
     */
    public function getMinProductByPrice()
    {
        $products = $this->getProducts();
        $returnProductPrice = 0;
        $returnProduct = null;
        if (count($products)>0) {
            $returnProductPrice = $products[0]->getPrice();
            $returnProduct = $products[0];
            foreach ($products as $product) {
                if ($product->getPrice() < $returnProductPrice) {
                    $returnProduct = $product;
                    $returnProductPrice = $product->getPrice();
                }
            }
        }
        return $returnProduct;
    }

    /**
     * getMinProductByPriceByn
     *
     * @return mixed|null
     */
    public function getMinProductByPriceByn()
    {
        $products = $this->getProducts();
        $returnProductPrice = 0;
        $returnProduct = null;
        if (count($products)>0) {
            $returnProductPrice = $products[0]->getPriceByn();
            $returnProduct = $products[0];
            foreach ($products as $product) {
                if ($product->getPriceByn() < $returnProductPrice) {
                    $returnProduct = $product;
                    $returnProductPrice = $product->getPriceByn();
                }
            }
        }
        return $returnProduct;
    }

    /**
     * getMinProductSaleByPriceByn
     *
     * @return mixed|null
     */
    public function getMinProductSaleByPriceByn()
    {
        $products = $this->getProductsSale();
        $returnProductPrice = 0;
        $returnProduct = null;
        if (count($products)>0) {
            $returnProductPrice = $products[0]->getPrice();
            $returnProduct = $products[0];
            foreach ($products as $product) {
                if ($product->getPrice() < $returnProductPrice) {
                    $returnProduct = $product;
                    $returnProductPrice = $product->getPrice();
                }
            }
        }
        return $returnProduct;
    }

    /**
     * getMinPrice
     *
     * @return int
     */
    public function getMinPrice()
    {
        $product = $this->getMinProductByPrice();
        return $product ? $product->getPrice() : 0;
    }

    /**
     * getMinSalePrice
     *
     * @return int
     */
    public function getMinSalePrice($rate)
    {
        $product = $this->getMinProductSaleByPriceByn();
        return $product ? $product->getPrice()/$rate : 0;
    }

    /**
     * getMinPriceByn
     *
     * @return int
     */
    public function getMinPriceByn()
    {
        $product = $this->getMinProductByPriceByn();
        return $product ? $product->getPriceByn() : 0;
    }

    /**
     * getMinSalePriceByn
     *
     * @return int
     */
    public function getMinSalePriceByn()
    {
        $product = $this->getMinProductSaleByPriceByn();
        return $product ? $product->getPrice() : 0;
    }

    /**
     * Get Ball
     *
     * @return null|string
     */
    public function getProductBall()
    {
        $products = $this->getProducts();
        $balls = [];

        // if products have product with red ball return red ball
        foreach ($products as $product) {
            if ($product->getBall() == ProductStock::RED_BALL) {
                return ProductStock::RED_BALL;
            }
            else {
                $balls[$product->getBall()] = (isset($balls[$product->getBall()])) ? $balls[$product->getBall()]+1 : 0;
            }
        }

        // if products without red ball find blue ball and return
        if (isset($balls[ProductStock::BLUE_BALL])) {
            return ProductStock::BLUE_BALL;
        }

        // if products without blue ball find green ball and return
        if (isset($balls[ProductStock::GREEN_BALL])) {
            return ProductStock::GREEN_BALL;
        }

        return NULL;
    }

    /**
     * getMaxSaleDate
     *
     * @return mixed
     */
    public function getMaxSaleDate()
    {
        $products = $this->getProductsSale();
        if (count($products)>0) {
            $date = $products[0]->getDate();
            foreach ($products as $product) {
                if ( $date < $product->getDate() ) {
                    $date = $product->getDate();
                }
            }
            $date->setTime("0","0","0");
        }

        return $date;
    }

    /**
     * getMinSaleDate
     *
     * @return mixed
     */
    public function getMinSaleDate()
    {
        $products = $this->getProductsSale();
        if (count($products)>0) {
            $date = $products[0]->getDate();
            foreach ($products as $product) {
                if ( $date > $product->getDate() ) {
                    $date = $product->getDate();
                }
            }
            $date->setTime("0","0","0");
        }
        return $date;
    }

    /**
     * getProductIds
     *
     * @return array
     */
    public function getProductIds()
    {
        $ids = [];
        if (!empty($this->getProducts())) {
            foreach ( $this->getProducts() as $product ) {
                $ids[] = $product->getId();
            }
        }

        return $ids;
    }
}

