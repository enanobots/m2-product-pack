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

namespace Nanobots\ProductPack\Model\Product;

use Magento\Catalog\Api\Data\ProductTierPriceInterfaceFactory;
use Magento\Catalog\Api\Data\ProductTierPriceExtensionFactory;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Type\Price as PriceModel;
use Magento\CatalogRule\Model\ResourceModel\RuleFactory;
use Magento\Customer\Api\GroupManagementInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Store\Model\StoreManagerInterface;

class Price extends PriceModel
{
    /**
     * @var Json|mixed
     */
    protected $serializer;

    /**
     * @param RuleFactory $ruleFactory
     * @param StoreManagerInterface $storeManager
     * @param TimezoneInterface $localeDate
     * @param Session $customerSession
     * @param ManagerInterface $eventManager
     * @param PriceCurrencyInterface $priceCurrency
     * @param GroupManagementInterface $groupManagement
     * @param ProductTierPriceInterfaceFactory $tierPriceFactory
     * @param ScopeConfigInterface $config
     * @param Json|null $serializer
     * @param ProductTierPriceExtensionFactory|null $tierPriceExtensionFactory
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        RuleFactory $ruleFactory,
        StoreManagerInterface $storeManager,
        TimezoneInterface $localeDate,
        Session $customerSession,
        ManagerInterface $eventManager,
        PriceCurrencyInterface $priceCurrency,
        GroupManagementInterface $groupManagement,
        ProductTierPriceInterfaceFactory $tierPriceFactory,
        ScopeConfigInterface $config,
        Json $serializer = null,
        ProductTierPriceExtensionFactory $tierPriceExtensionFactory = null
    ) {
        parent::__construct(
            $ruleFactory,
            $storeManager,
            $localeDate,
            $customerSession,
            $eventManager,
            $priceCurrency,
            $groupManagement,
            $tierPriceFactory,
            $config,
            $tierPriceExtensionFactory
        );
        $this->serializer = $serializer ?: ObjectManager::getInstance()
            ->get(Json::class);
    }

    /**
     * Calculate Single Unit Price
     *
     * @param $product
     * @return float|mixed
     */
    public function getPrice($product)
    {
        $price = parent::getPrice($product);
        $customOption = $product->getCustomOption('info_buyRequest');
        if ($customOption) {
            $buyRequest = $this->serializer->unserialize($customOption->getValue());
            $packOption = $buyRequest['pack_option'] ?? ['discount_type' => 'percent', 'discount_value' => 0, 'pack_size' => 1];
            $discountType = $packOption['discount_type'];
            $discountValue = $packOption['discount_value'];

            switch ($discountType) {
                case 'fixed':
                    $price -= $discountValue;
                    break;
                case 'percent':
                    $price *= round((1 - ($discountValue / 100)), 2);
                    break;
            }
        }

        return $price;
    }

    /**
     * Get Final Price for Product Pack (Price * Qty of a pack)
     *
     * @param $qty
     * @param $product
     * @return mixed
     */
    public function getFinalPrice($qty, $product)
    {
        $finalPrice = parent::getFinalPrice($qty, $product);
        $customOption = $product->getCustomOption('info_buyRequest');
        if ($customOption) {
            $buyRequest = $this->serializer->unserialize($customOption->getValue());
            $packOption = $buyRequest['pack_option'];
            if ($packOption) {
                if ($product->getSpecialPrice()) {
                    $finalPrice = round($product->getSpecialPrice(), 2) * $packOption['pack_size'];
                } else {
                    $finalPrice *= $packOption['pack_size'];
                }
            }
        }

        return $finalPrice;
    }

    /**
     * Get product tier price by qty
     *
     * @param   float $qty
     * @param   Product $product
     * @return  float|array
     */
    public function getTierPrice($qty, $product)
    {
        $customOption = $product->getCustomOption('info_buyRequest');

        if ($customOption) {
            $buyRequest = $this->serializer->unserialize($customOption->getValue());
            $packOption = $buyRequest['pack_option'];

            // force tier price calculation if pack size is 1
            if ((int)$packOption['pack_size'] === 1) {
                $allGroupsId = $this->getAllCustomerGroupsId();

                $prices = $this->getExistingPrices($product, 'tier_price', true);
                if ($prices === null || !is_array($prices)) {
                    if ($qty !== null) {
                        return parent::getPrice($product);
                    } else {
                        return [
                            [
                                'price' => parent::getPrice($product),
                                'website_price' => parent::getPrice($product),
                                'price_qty' => 1,
                                'cust_group' => $allGroupsId,
                            ]
                        ];
                    }
                }

                $custGroup = $this->_getCustomerGroupId($product);
                if ($qty) {
                    $prevQty = 1;
                    $prevPrice = parent::getPrice($product);
                    $prevGroup = $allGroupsId;

                    foreach ($prices as $price) {
                        if ($price['cust_group'] != $custGroup && $price['cust_group'] != $allGroupsId) {
                            // tier not for current customer group nor is for all groups
                            continue;
                        }
                        if ($qty < $price['price_qty']) {
                            // tier is higher than product qty
                            continue;
                        }
                        if ($price['price_qty'] < $prevQty) {
                            // higher tier qty already found
                            continue;
                        }
                        if ($price['price_qty'] == $prevQty &&
                            $prevGroup != $allGroupsId &&
                            $price['cust_group'] == $allGroupsId) {
                            // found tier qty is same as current tier qty but current tier group is ALL_GROUPS
                            continue;
                        }
                        if ($price['website_price'] < $prevPrice) {
                            $prevPrice = $price['website_price'];
                            $prevQty = $price['price_qty'];
                            $prevGroup = $price['cust_group'];
                        }
                    }
                    return $prevPrice;
                } else {
                    $qtyCache = [];
                    foreach ($prices as $priceKey => $price) {
                        if ($price['cust_group'] != $custGroup && $price['cust_group'] != $allGroupsId) {
                            unset($prices[$priceKey]);
                        } elseif (isset($qtyCache[$price['price_qty']])) {
                            $priceQty = $qtyCache[$price['price_qty']];
                            if ($prices[$priceQty]['website_price'] > $price['website_price']) {
                                unset($prices[$priceQty]);
                                $qtyCache[$price['price_qty']] = $priceKey;
                            } else {
                                unset($prices[$priceKey]);
                            }
                        } else {
                            $qtyCache[$price['price_qty']] = $priceKey;
                        }
                    }
                }

                return $prices ?: [];
            }
        }

        return  [];
    }
}
