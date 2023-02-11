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

namespace Nanobots\ProductPack\ViewModel\Product;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Helper\Image;
use Magento\Catalog\Model\Product;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Request\Http as Request;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Locale\Resolver;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Registry;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Quote\Model\ResourceModel\Quote\Item\Option\Collection;
use Magento\Quote\Model\ResourceModel\Quote\Item\Option\CollectionFactory;
use Magento\Store\Model\ScopeInterface as StoreScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Nanobots\ProductPack\Api\Data\PackOptionInterface;
use Nanobots\ProductPack\Api\PackOptionRepositoryInterface;
use Nanobots\ProductPack\Model\PackOption;
use Nanobots\ProductPack\Model\PackOptionFactory;

class Pack implements ArgumentInterface
{
    /** @var Request */
    protected Request $request;

    /** @var PackOptionRepositoryInterface */
    protected PackOptionRepositoryInterface $packOptionRepository;

    /** @var SearchCriteriaBuilder */
    protected SearchCriteriaBuilder $searchCriteriaBuilder;

    /** @var PriceCurrencyInterface */
    protected PriceCurrencyInterface $priceCurrency;

    /** @var PackOptionFactory */
    protected PackOptionFactory $packOptionFactory;

    /** @var Registry */
    protected Registry $coreRegistry;

    /** @var ProductRepositoryInterface */
    protected ProductRepositoryInterface $productRepository;

    /** @var StoreManagerInterface */
    protected StoreManagerInterface $storeManager;

    /** @var Image */
    protected Image $image;

    /** @var CollectionFactory */
    protected CollectionFactory $itemOptionCollectionFactory;

    /** @var Resolver */
    protected Resolver $localeResolver;

    /** @var ScopeConfigInterface  */
    protected ScopeConfigInterface $scopeConfig;

    /**
     * @param CollectionFactory $itemOptionCollectionFactory
     * @param Request $request
     * @param PackOptionRepositoryInterface $packOptionRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param PriceCurrencyInterface $priceCurrency
     * @param PackOptionFactory $packOptionFactory
     * @param Registry $coreRegistry
     * @param ProductRepositoryInterface $productRepository
     * @param StoreManagerInterface $storeManager
     * @param Image $image
     * @param Resolver $localeResolver
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        CollectionFactory $itemOptionCollectionFactory,
        Request $request,
        PackOptionRepositoryInterface $packOptionRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        PriceCurrencyInterface $priceCurrency,
        PackOptionFactory $packOptionFactory,
        Registry $coreRegistry,
        ProductRepositoryInterface $productRepository,
        StoreManagerInterface $storeManager,
        Image $image,
        Resolver $localeResolver
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->itemOptionCollectionFactory = $itemOptionCollectionFactory;
        $this->request = $request;
        $this->packOptionRepository = $packOptionRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->priceCurrency = $priceCurrency;
        $this->packOptionFactory = $packOptionFactory;
        $this->coreRegistry = $coreRegistry;
        $this->productRepository = $productRepository;
        $this->storeManager = $storeManager;
        $this->image = $image;
        $this->localeResolver = $localeResolver;
    }

    public function getLocaleCode(): string
    {
        return $this->localeResolver->getLocale();
    }

    /**
     * Get Pack Options
     *
     * @return PackOptionInterface[]
     */
    public function getPackOptions(): array
    {
        $productId = $this->request->getParam('id', false);

        if ($productId) {
            $this->searchCriteriaBuilder->addFilter(PackOptionInterface::PRODUCT_ID, $productId);

            return $this->sortPackOptionsByPricePerUnit(
                $this->packOptionRepository->getList($this->searchCriteriaBuilder->create())->getItems()
            );
        }

        return [];
    }

    /**
     * @param PackOptionInterface[] $packOptions
     * @return PackOptionInterface[]
     */
    private function sortPackOptionsByPricePerUnit(array $packOptions): array
    {
        if (empty($packOptions)) {
            return [];
        }
        $packOptionPrices = $packOptionSorted = [];
        /** @var PackOption $packOption */
        foreach ($packOptions as $index => $packOption) {
            $packOptionPrices[$packOption->getId()] = $this->getPricePerUnit($packOption);
            $packOptionSorted[$packOption->getId()] = $packOption;
        }
        arsort($packOptionPrices);

        return array_replace($packOptionPrices, $packOptionSorted);
    }

    /**
     * @param PackOption $packOption
     * @return string
     */
    public function getDataAttributes(PackOption $packOption): string
    {
        $html = '';
        foreach ($packOption->getData() as $key => $value) {
            if ($key === PackOptionInterface::PRODUCT_ID || $key === PackOptionInterface::SORT_ORDER) {
                continue;
            } elseif ($key === PackOptionInterface::PACKOPTION_ID) {
                $html .= " value=\"$value\"";
            } else {
                $html .= " data-$key=\"$value\"";
            }
        }

        return $html;
    }

    /**
     * Render Discount
     *
     * @param PackOptionInterface $packOption
     * @return string
     */
    public function renderDiscount(PackOptionInterface $packOption): string
    {
        if ($packOption->getDiscountType() === 'fixed') {
            return __("%1 Off", $this->priceCurrency->format($packOption->getDiscountValue(), false))->render();
        } else {
            return __("%1 % Off", $packOption->getDiscountValue())->render();
        }
    }

    /**
     * Render From Array
     *
     * @param array $packOption
     * @return string
     */
    public function renderDiscountFromArray(array $packOption): string
    {
        /** @var PackOption $packOptionModel */
        $packOptionModel = $this->packOptionFactory->create();
        $packOptionModel->setData($packOption);

        return $this->renderDiscount($packOptionModel);
    }

    /**
     * Render Price
     *
     * @param PackOptionInterface $packOption
     * @return string
     * @throws NoSuchEntityException
     */
    public function renderPricePerUnit(PackOptionInterface $packOption): string
    {
        return $this->format($this->getPricePerUnit($packOption));
    }

    /**
     * @param float $amount
     * @return string
     */
    public function format(float $amount): string
    {
        return $this->priceCurrency->format($amount, false);
    }

    /**
     * Render Price
     *
     * @param PackOptionInterface $packOption
     * @return string
     * @throws NoSuchEntityException
     */
    public function getPricePerUnit(PackOptionInterface $packOption): float
    {
        if (!($product = $this->getProduct())) {
            $product = $this->productRepository->getById($this->request->getParam('id'))->getFinalPrice();
        }

        $price = $product->getFinalPrice();
        $specialPrice = $product->getSpecialPrice();

        if (!$specialPrice) {
            if ($packOption->getDiscountType() === 'fixed') {
                return $price - $packOption->getDiscountValue();
            }
            return (1 - ($packOption->getDiscountValue() / 100)) * $price;
        } else {
            switch ($this->scopeConfig->getValue(
                \Nanobots\ProductPack\Model\Product\Price::XPATH_PRODUCT_PACK_CONFIG_SPECIAL_PRICE,
                StoreScopeInterface::SCOPE_STORE
            )) {
                case \Nanobots\ProductPack\Model\Config\Source\SpecialPriceCalculationType::USE_MIN_PRICE: {
                    return (float)min(
                        (1 - ($packOption->getDiscountValue() / 100)) * $product->getPrice(),
                        (float)$specialPrice
                    );
                    break;
                }
                case \Nanobots\ProductPack\Model\Config\Source\SpecialPriceCalculationType::USE_SPECIAL_PRICE:
                default: {
                    return (float)$product->getSpecialPrice();
                }
            }
        }
    }


    /**
     * Get Current Product From Registry
     *
     * @return Product|null
     */
    public function getProduct(): ?Product
    {
        return $this->coreRegistry->registry('product');
    }

    /**
     * Can Show an Option
     *
     * @param PackOptionInterface $packOption
     * @return bool
     */
    public function canShowOption(PackOptionInterface $packOption): bool
    {
        $product = $this->getProduct();
        if ($product->isSaleable()) {
            $qty = $product->getQty();

            if (!$qty) {
                return true;
            }

            return $packOption->getPackSize() <= $qty;
        }

        return false;
    }

    /**
     * Get Product Item Options
     *
     * @param int $itemId
     * @return Collection
     */
    public function getProductItemOptions(int $itemId): Collection
    {
        $collection = $this->itemOptionCollectionFactory->create();
        $collection->addFieldToFilter('item_id', ['eq' => $itemId]);
        return $collection;
    }
}
