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

namespace Nanobots\ProductPack\Helper;

use Magento\Catalog\Helper\Data as TaxHelper;
use Magento\Catalog\Model\Product;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Serialize\Serializer\Json as JsonSerializer;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManager;
use Magento\Tax\Model\Config;

class Price extends AbstractHelper
{
    /** @var string  */
    public const OPTION_DISPLAY_TYPE_XPATH = 'product_pack/settings/option_display_type';

    /** @var PriceCurrencyInterface  */
    protected PriceCurrencyInterface $priceCurrency;

    /** @var array|null  */
    protected ?array $priceConfig = null;

    /** @var StoreManager  */
    protected StoreManager $storeManager;

    /** @var TaxHelper  */
    protected TaxHelper $taxHelper;

    /** @var Config  */
    protected Config $taxConfig;

    protected JsonSerializer $jsonSerializer;

    /**
     * @param Context $context
     * @param PriceCurrencyInterface $priceCurrency
     * @param StoreManager $storeManager
     * @param TaxHelper $taxHelper
     * @param Config $taxConfig
     * @param JsonSerializer $jsonSerializer
     */
    public function __construct(
        Context $context,
        PriceCurrencyInterface $priceCurrency,
        StoreManager $storeManager,
        TaxHelper $taxHelper,
        Config $taxConfig,
        JsonSerializer $jsonSerializer
    ) {
        parent::__construct($context);
        $this->priceCurrency = $priceCurrency;
        $this->storeManager = $storeManager;
        $this->taxHelper = $taxHelper;
        $this->taxConfig = $taxConfig;
        $this->jsonSerializer = $jsonSerializer;
    }

    /**
     * @return string
     * @throws NoSuchEntityException
     */
    public function getOptionDisplayType(): string
    {
        return $this->scopeConfig->getValue(
            self::OPTION_DISPLAY_TYPE_XPATH,
            ScopeInterface::SCOPE_STORES,
            $this->storeManager->getStore()->getId()
        );
    }

    /**
     * @param $basePrice
     * @param $price
     * @param $discountType
     * @param $discountValue
     * @param $specialPrice
     * @return array
     */
    public function calculatePrices($basePrice, $price, $discountType, $discountValue, ?float $specialPrice = 0.00): array
    {
        if ($specialPrice) {
            return [
                'price' => $this->priceCurrency->format($basePrice, false),
                'base_price' => $this->priceCurrency->format($specialPrice, false)
            ];
        }

        if ($price) {
            $x = $basePrice / $price;
        } else {
            $x = 1;
        }

        if ($discountType === 'fixed') {
            $price -= $discountValue;
        } elseif ($discountType === 'percent') {
            $price *= (1 - ($discountValue / 100));
        }

        if ($price < 0) {
            $price = 0;
        }

        $basePrice = $price * $x;

        return [
            'price' => $this->priceCurrency->format($basePrice, false),
            'base_price' => $this->priceCurrency->format($price, false)
        ];
    }

    /**
     * @param Product $product
     * @return string
     */
    public function getPriceConfigJson(Product $product): string
    {
        $data = $this->getPriceConfig($product);
        return $this->jsonSerializer->serialize($data);
    }

    /**
     * Get Price Config
     *
     * @param Product $product
     * @return array
     */
    protected function getPriceConfig(Product $product): array
    {
        $store = $this->storeManager->getStore();
        if (is_null($this->priceConfig)) {
            $specialPrice = $product->getSpecialPrice();
            $price = $product->getPriceModel()->getFinalPrice(1, $product);
            $basePrice = $this->taxHelper->getTaxPrice(
                $product,
                $price,
                true,
                null,
                null,
                null,
                $store,
                $this->taxConfig->priceIncludesTax($store)
            );
            $packOptions = $product->getExtensionAttributes()->getPackOptions();

            $data = [
                '0' => [
                    'base_price' => $this->priceCurrency->format($price, false),
                    'price' => $this->priceCurrency->format($basePrice, false)
                ]
            ];

            foreach ($packOptions as $packOption) {
                $data[$packOption['packoption_id']] = $this->calculatePrices(
                    $basePrice,
                    $price,
                    $packOption['discount_type'],
                    $packOption['discount_value'],
                    (float)$specialPrice
                );
            }

            $this->priceConfig = $data;
        }

        return $this->priceConfig;
    }
}
