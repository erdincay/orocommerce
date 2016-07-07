<?php

namespace OroB2B\Bundle\PricingBundle\Entity;

use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\Mapping as ORM;

use Oro\Bundle\B2BEntityBundle\Storage\ObjectIdentifierAwareInterface;

use OroB2B\Bundle\ProductBundle\Entity\Product;

/**
 * @ORM\Entity()
 * @ORM\Table(
 *      name="orob2b_price_list_to_product",
 *      uniqueConstraints={
 *          @ORM\UniqueConstraint(
 *              name="orob2b_price_list_to_product_uidx",
 *              columns={"product_id", "price_list_id"}
 *          )
 *      }
 * )
 * @ORM\EntityListeners({ "OroB2B\Bundle\PricingBundle\Entity\EntityListener\PriceListProductEntityListener" })
 */
class PriceListToProduct implements ObjectIdentifierAwareInterface
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var PriceList
     *
     * @ORM\ManyToOne(targetEntity="OroB2B\Bundle\PricingBundle\Entity\PriceList")
     * @ORM\JoinColumn(name="price_list_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    protected $priceList;

    /**
     * @var Product
     *
     * @ORM\ManyToOne(targetEntity="OroB2B\Bundle\ProductBundle\Entity\Product")
     * @ORM\JoinColumn(name="product_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    protected $product;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_manual", type="boolean")
     */
    protected $manual = true;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return PriceList
     */
    public function getPriceList()
    {
        return $this->priceList;
    }

    /**
     * @param PriceList $priceList
     * @return $this
     */
    public function setPriceList(PriceList $priceList)
    {
        $this->priceList = $priceList;

        return $this;
    }
    /**
     * @return Product
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * @param Product $product
     * @return $this
     */
    public function setProduct(Product $product)
    {
        $this->product = $product;

        return $this;
    }

    /**
     * @return boolean
     */
    public function isManual()
    {
        return $this->manual;
    }

    /**
     * @param bool $manual
     * @return $this
     */
    public function setManual($manual)
    {
        $this->manual = $manual;

        return $this;
    }

    /**
     * @return string
     */
    public function getObjectIdentifier()
    {
        if (!$this->product || !$this->priceList || !$this->product->getId() || !$this->priceList->getId()) {
            throw new \InvalidArgumentException('Product id and priceList id, required for identifier generation');
        }

        return ClassUtils::getClass($this) . '_' . $this->product->getId() . '_' . $this->priceList->getId();
    }
}
