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

namespace Model\Product\Type;

use Magento\Framework\DataObject;
use Api\Data\PackOptionInterface;
use Api\PackOptionRepositoryInterface;
use Model\ResourceModel\PackOption as PackOptionResource;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Option;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\Product\Type\Simple;
use Magento\Eav\Model\Config;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\File\UploaderFactory;
use Magento\Framework\Filesystem;
use Magento\Framework\Registry;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\MediaStorage\Helper\File\Storage\Database;
use Psr\Log\LoggerInterface;

class Pack extends Simple
{
    /** @var string  */
    public const TYPE_CODE = 'pack';

    /** @var PackOptionRepositoryInterface  */
    protected PackOptionRepositoryInterface $packOptionRepository;

    /** @var PackOptionResource  */
    protected PackOptionResource $packOptionResource;

    /** @var SearchCriteriaBuilder  */
    protected SearchCriteriaBuilder $searchCriteriaBuilder;

    /**
     * @param Option $catalogProductOption
     * @param Config $eavConfig
     * @param Type $catalogProductType
     * @param ManagerInterface $eventManager
     * @param Database $fileStorageDb
     * @param Filesystem $filesystem
     * @param Registry $coreRegistry
     * @param LoggerInterface $logger
     * @param ProductRepositoryInterface $productRepository
     * @param PackOptionRepositoryInterface $packOptionRepository
     * @param PackOptionResource $packOptionResource
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param Json|null $serializer
     * @param UploaderFactory|null $uploaderFactory
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Option                        $catalogProductOption,
        Config                        $eavConfig,
        Type                          $catalogProductType,
        ManagerInterface              $eventManager,
        Database                      $fileStorageDb,
        Filesystem                    $filesystem,
        Registry                      $coreRegistry,
        LoggerInterface               $logger,
        ProductRepositoryInterface    $productRepository,
        PackOptionRepositoryInterface $packOptionRepository,
        PackOptionResource            $packOptionResource,
        SearchCriteriaBuilder         $searchCriteriaBuilder,
        Json                          $serializer = null,
        UploaderFactory               $uploaderFactory = null
    ) {
        parent::__construct(
            $catalogProductOption,
            $eavConfig,
            $catalogProductType,
            $eventManager,
            $fileStorageDb,
            $filesystem,
            $coreRegistry,
            $logger,
            $productRepository,
            $serializer,
            $uploaderFactory
        );
        $this->packOptionRepository = $packOptionRepository;
        $this->packOptionResource = $packOptionResource;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteTypeSpecificData(Product $product)
    {
        $this->searchCriteriaBuilder->addFilter(PackOptionInterface::PRODUCT_ID, $product->getId());
        $packOptions = $this->packOptionRepository->getList($this->searchCriteriaBuilder->create())->getItems();

        foreach ($packOptions as $packOption) {
            $this->packOptionResource->delete($packOption);
        }
    }

    /**
     * Default action to get weight of product
     *
     * @param Product $product
     * @return float
     */
    public function getWeight($product)
    {
        if ($product->hasCustomOptions()) {
            $packOption = $product->getCustomOption('pack_option');
            if ($packOption) {
                $packOption = $this->serializer->unserialize($packOption->getValue());
                $qty = $packOption['pack_size'];
                return ($product->getData('weight') * $qty) + $packOption['extra_weight'];
            }
        }

        return parent::getWeight($product);
    }

    /**
     * Prepare Product
     *
     * @param DataObject $buyRequest
     * @param $product
     * @param $processMode
     * @return array
     */
    protected function _prepareProduct(DataObject $buyRequest, $product, $processMode): array
    {
        $product = parent::_prepareProduct($buyRequest, $product, $processMode);
        $product = array_shift($product);

        if ($buyRequest->getData('pack_option_hash')) {
            $product->addCustomOption('pack_option_hash', $buyRequest->getData('pack_option_hash'));
        }

        if ($buyRequest->getData('pack_option')) {
            $product->addCustomOption('pack_option', json_encode($buyRequest->getData('pack_option')));
        }

        return [$product];
    }
}
