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

namespace Nanobots\ProductPack\Observer\Product;

use Nanobots\ProductPack\Api\Data\PackOptionInterface;
use Nanobots\ProductPack\Api\PackOptionRepositoryInterface;
use Nanobots\ProductPack\Model\PackOption;
use Nanobots\ProductPack\Model\PackOptionFactory;
use Nanobots\ProductPack\Model\ResourceModel\PackOption as PackOptionResource;
use Magento\Catalog\Model\Product;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class SaveCommitAfter implements ObserverInterface
{
    protected PackOptionFactory $packOptionFactory;
    protected PackOptionResource $packOptionResource;
    protected PackOptionRepositoryInterface $packOptionRepository;
    private SearchCriteriaBuilder $searchCriteriaBuilder;

    /**
     * @param PackOptionFactory $packOptionFactory
     * @param PackOptionResource $packOptionResource
     * @param PackOptionRepositoryInterface $packOptionRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        PackOptionFactory $packOptionFactory,
        PackOptionResource $packOptionResource,
        PackOptionRepositoryInterface $packOptionRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {

        $this->packOptionFactory = $packOptionFactory;
        $this->packOptionResource = $packOptionResource;
        $this->packOptionRepository = $packOptionRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * @inheritDoc
     */
    public function execute(Observer $observer)
    {
        /** @var $product Product */
        $product = $observer->getEvent()->getProduct();

        $packOptions = $product->getPackOptions();

        if (empty($packOptions)) {
            $packOptions = $product->getExtensionAttributes()->getPackOptions();
        }

        $savedIds = [];

        if (!empty($packOptions)) {
            foreach ($packOptions as $packOption) {
                unset($packOption['record_id']);
                if ($packOption[PackOptionInterface::PACKOPTION_ID] === '') {
                    $packOption[PackOptionInterface::PACKOPTION_ID] = null;
                }
                $packOption[PackOptionInterface::PRODUCT_ID] = $product->getId();
                $packOptionModel = $this->packOptionFactory->create();
                $packOptionModel->setData($packOption);
                $this->packOptionResource->save($packOptionModel);
                $savedIds[] = $packOptionModel->getId();
            }

            $this->searchCriteriaBuilder
                ->addFilter(PackOptionInterface::PRODUCT_ID, $product->getId())
                ->addFilter(PackOptionInterface::PACKOPTION_ID, $savedIds, 'nin');
            $optionsToRemove = $this->packOptionRepository->getList($this->searchCriteriaBuilder->create())->getItems();

            foreach ($optionsToRemove as $option) {
                $this->packOptionResource->delete($option);
            }
        }
    }
}
