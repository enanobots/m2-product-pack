<?php
/**
 * Created by Q-Solutions Studio
 *
 * @category    Nanobots
 * @package     Nanobots_ProductPack
 * @author      Wojciech M. Wnuk <wojtek@qsolutionsstudio.com>
 */

\Magento\TestFramework\Helper\Bootstrap::getInstance()->reinitialize();

/** @var \Magento\TestFramework\ObjectManager $objectManager */
$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

/** @var \Magento\Catalog\Api\CategoryLinkManagementInterface $categoryLinkManagement */
$categoryLinkManagement = $objectManager->create(\Magento\Catalog\Api\CategoryLinkManagementInterface::class);

/** @var $product \Magento\Catalog\Model\Product */
$product = $objectManager->create(\Magento\Catalog\Model\Product::class);
$product->isObjectNew(true);
$product->setTypeId(\Magento\Catalog\Model\Product\Type::TYPE_SIMPLE)
    ->setAttributeSetId(4)
    ->setWebsiteIds([1])
    ->setName('Pack Product')
    ->setSku('simple_with_pack_options')
    ->setPrice(250)
    ->setWeight(1)
    ->setShortDescription("Short description")
    ->setTaxClassId(0)
    ->setDescription('Description with <b>html tag</b>')
    ->setMetaTitle('meta title')
    ->setMetaKeyword('meta keyword')
    ->setMetaDescription('meta description')
    ->setVisibility(\Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH)
    ->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
    ->setStockData(
        [
            'use_config_manage_stock' => 1,
            'qty' => 300,
            'is_qty_decimal' => 0,
            'is_in_stock' => 1,
        ]
    )->setPackOptions([
        [
            'title' => 'Box',
            'discount_type' => 'fixed',
            'discount_value' => 2,
            'pack_size' => 10,
            'extra_weight' => 0.5,
            'sort_order' => 1
        ],
        [
            'title' => 'Big Box',
            'discount_type' => 'percent',
            'discount_value' => 10,
            'pack_size' => 50,
            'extra_weight' => 2,
            'sort_order' => 2
        ],
        [
            'title' => 'Huge Box',
            'discount_type' => 'percent',
            'discount_value' => 20,
            'pack_size' => 100,
            'extra_weight' => 5,
            'sort_order' => 3
        ],
    ]);

/** @var \Magento\Catalog\Api\ProductRepositoryInterface $productRepositoryFactory */
$productRepositoryFactory = $objectManager->create(\Magento\Catalog\Api\ProductRepositoryInterface::class);
$productRepositoryFactory->save($product);

$categoryLinkManagement->assignProductToCategories(
    $product->getSku(),
    [2]
);
