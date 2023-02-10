<?php
/**
 * Copyright © Q-Solutions Studio: eCommerce Nanobots. All rights reserved.
 *
 * @category    Nanobots
 * @package     Nanobots_ProductPack
 * @author      Jakub Winkler <jwinkler@qsolutionsstudio.com>
 * @author      Wojtek Wnuk <wojtek@qsolutionsstudio.com>
 * @author      Łukasz Owczarczuk <lukasz@qsolutionsstudio.com>
 */

declare(strict_types=1);

namespace Nanobots\ProductPack\Model;

use Nanobots\ProductPack\Api\Data\PackOptionInterface;
use Magento\Framework\Model\AbstractModel;

class PackOption extends AbstractModel implements PackOptionInterface
{
    protected bool $isDelete = false;

    /**
     * @inheritDoc
     */
    public function _construct()
    {
        $this->_init(ResourceModel\PackOption::class);
    }

    /**
     * @return int
     */
    public function getPackoptionId()
    {
        return $this->_getData(self::PACKOPTION_ID);
    }

    /**
     * @param int $packoptionId
     * @return PackOption
     */
    public function setPackoptionId($packoptionId)
    {
        return $this->setData(self::PACKOPTION_ID, $packoptionId);
    }

    /**
     * @return int
     */
    public function getProductId()
    {
        return $this->_getData(self::PRODUCT_ID);
    }

    /**
     * @param int $productId
     * @return PackOption
     */
    public function setProductId($productId)
    {
        return $this->setData(self::PRODUCT_ID, $productId);
    }

    public function getFrontendTitle(string $localeCode = 'pl_PL')
    {
        if ($localeCode !== 'pl_PL' && !empty($this->getLabelEn())) {
            return $this->getLabelEn();
        }
        return $this->getLabelPl();
    }

    /**
     * @return string
     */
    public function getDiscountType()
    {
        return $this->_getData(self::DISCOUNT_TYPE);
    }

    /**
     * @param string $discountType
     * @return PackOption
     */
    public function setDiscountType($discountType)
    {
        return $this->setData(self::DISCOUNT_TYPE, $discountType);
    }

    /**
     * @return float
     */
    public function getDiscountValue()
    {
        return $this->_getData(self::DISCOUNT_VALUE);
    }

    /**
     * @param float $discountValue
     * @return PackOption
     */
    public function setDiscountValue($discountValue)
    {
        return $this->setData(self::DISCOUNT_VALUE, $discountValue);
    }

    /**
     * @return float
     */
    public function getExtraWeight()
    {
        return $this->_getData(self::EXTRA_WEIGHT);
    }

    /**
     * @param float $extraWeight
     * @return PackOption
     */
    public function setExtraWeight($extraWeight)
    {
        return $this->setData(self::EXTRA_WEIGHT, $extraWeight);
    }

    /**
     * @return int
     */
    public function getPackSize()
    {
        return $this->_getData(self::PACK_SIZE);
    }

    /**
     * @param int $packSize
     * @return PackOption
     */
    public function setPackSize($packSize)
    {
        return $this->setData(self::PACK_SIZE, $packSize);
    }

    /**
     * @return int
     */
    public function getPackageName()
    {
        return $this->_getData(self::PACKAGE_NAME);
    }

    /**
     * @param int $packageName
     * @return PackOption
     */
    public function setPackageName($packageName)
    {
        return $this->setData(self::PACKAGE_NAME, $packageName);
    }

    /**
     * @return int
     */
    public function getSortOrder()
    {
        return $this->_getData(self::SORT_ORDER);
    }

    /**
     * @param int $sortOrder
     * @return PackOption
     */
    public function setSortOrder($sortOrder)
    {
        return $this->setData(self::SORT_ORDER, $sortOrder);
    }

    /**
     * @return bool
     */
    public function isDelete()
    {
        if (!is_null($this->_getData(self::IS_DELETE))) {
            $this->isDelete = (bool)$this->_getData(self::IS_DELETE);
        }

        return $this->isDelete;
    }

    /**
     * @param bool $isDelete
     * @return PackOption
     */
    public function setIsDelete($isDelete)
    {
        $this->isDelete = $isDelete;
        return $this;
    }

    /**
     * @return \Nanobots\ProductPack\Api\Data\PackOptionExtensionInterface|null
     */
    public function getExtensionAttributes()
    {
        return $this->_getData(self::EXTENSION_ATTRIBUTES_KEY);
    }

    /**
     * @param \Nanobots\ProductPack\Api\Data\PackOptionExtensionInterface $extensionAttributes
     * @return PackOption
     */
    public function setExtensionAttributes(\Nanobots\ProductPack\Api\Data\PackOptionExtensionInterface $extensionAttributes)
    {
        return $this->setData(self::EXTENSION_ATTRIBUTES_KEY, $extensionAttributes);
    }
}

