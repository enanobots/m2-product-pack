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

namespace Plugin;

use Magento\Catalog\Api\Data\ProductExtensionFactory;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchResults;
use Api\Data\PackOptionInterface;
use Api\PackOptionRepositoryInterface;

class PackOptions
{
    protected ProductExtensionFactory $extensionFactory;
    protected PackOptionRepositoryInterface $packOptionRepository;
    private SearchCriteriaBuilder $searchCriteriaBuilder;

    /**
     * ParentIds constructor.
     * @param ProductExtensionFactory $extensionFactory
     * @param PackOptionRepositoryInterface $packOptionRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        ProductExtensionFactory $extensionFactory,
        PackOptionRepositoryInterface $packOptionRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->extensionFactory = $extensionFactory;
        $this->packOptionRepository = $packOptionRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * @param ProductRepository $subject
     * @param Product $product
     * @return Product
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGet(
        ProductRepository $subject,
        Product $product
    ): Product {
        return $this->setExtensionAttribute($product);
    }

    /**
     * @param ProductRepository $subject
     * @param Product $product
     * @return Product
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetById(
        ProductRepository $subject,
        Product $product
    ): Product {
        return $this->setExtensionAttribute($product);
    }

    /**
     * @param ProductRepository $subject
     * @param SearchResults $searchResults
     * @return SearchResults
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetList(
        ProductRepository $subject,
        SearchResults $searchResults
    ): SearchResults {
        $products = $searchResults->getItems();

        /** @var Product $product */
        foreach ($products as $product) {
            $this->setExtensionAttribute($product);
        }
        return $searchResults;
    }

    /**
     * @param Product $product
     * @return Product
     */
    protected function setExtensionAttribute(Product $product): Product
    {
        $extensionAttributes = $product->getExtensionAttributes();
        $extensionAttributes = $extensionAttributes ?? $this->extensionFactory->create();
        $extensionAttributes->setPackOptions($this->getExtensionData($product));
        $product->setExtensionAttributes($extensionAttributes);

        return $product;
    }

    /**
     * Get Extension Data
     *
     * @param Product $product
     * @return array
     */
    protected function getExtensionData(Product $product): array
    {
        $this->searchCriteriaBuilder->addFilter(PackOptionInterface::PRODUCT_ID, $product->getId());
        $options = $this->packOptionRepository->getList($this->searchCriteriaBuilder->create())->getItems();

        return array_map(
            static function ($option) {
                return [
                    PackOptionInterface::PACKOPTION_ID => $option->getPackoptionId(),
                    PackOptionInterface::PACKAGE_NAME => $option->getPackageName(),
                    PackOptionInterface::PRODUCT_ID => $option->getProductId(),
                    PackOptionInterface::DISCOUNT_TYPE => $option->getDiscountType(),
                    PackOptionInterface::DISCOUNT_VALUE => $option->getDiscountValue(),
                    PackOptionInterface::EXTRA_WEIGHT => $option->getExtraWeight(),
                    PackOptionInterface::PACK_SIZE => $option->getPackSize(),
                    PackOptionInterface::SORT_ORDER => $option->getSortOrder()
                ];
            },
            $options
        );
    }
}
