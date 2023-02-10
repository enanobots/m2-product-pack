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

namespace Plugin\Quote;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Type\AbstractType;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Message\Manager as MessageManager;
use Magento\InventorySales\Model\GetProductSalableQty;
use Magento\InventorySalesApi\Api\Data\SalesChannelInterface;
use Magento\InventorySalesApi\Api\StockResolverInterface;
use Magento\Quote\Model\Quote;
use Magento\Store\Model\StoreManager;
use Model\Product\Type\Pack;
use function Nanobots\ProductPack\Plugin\Quote\__;

class AddProduct
{
    /** @var MessageManager  */
    private MessageManager $messageManager;

    /** @var StoreManager  */
    private StoreManager $storeManager;

    /** @var StockResolverInterface  */
    private StockResolverInterface $stockResolver;

    /** @var GetProductSalableQty  */
    private GetProductSalableQty $productSalableQty;

    /**
     * @param MessageManager $messageManager
     * @param StoreManager $storeManager
     * @param StockResolverInterface $stockResolver
     * @param GetProductSalableQty $productSalableQty
     */
    public function __construct(
        MessageManager $messageManager,
        StoreManager $storeManager,
        StockResolverInterface $stockResolver,
        GetProductSalableQty $productSalableQty
    ) {
        $this->messageManager = $messageManager;
        $this->storeManager = $storeManager;
        $this->stockResolver = $stockResolver;
        $this->productSalableQty = $productSalableQty;
    }

    /**
     * @param Quote $subject
     * @param callable $proceed
     * @param Product $product
     * @param null $request
     * @param string $processMode
     * @return array
     * @throws LocalizedException
     */
    public function aroundAddProduct(
        Quote $subject,
        callable $proceed,
        Product $product,
        $request = null,
        $processMode = AbstractType::PROCESS_MODE_FULL
    ) {
        if ($product->getTypeId() === Pack::TYPE_CODE && $salableQty = $this->getSalableQty($product)) {
            $packSize = $this->getPackSize($request);
            if ($packSize > 1) {
                $qty = $request->getData('qty') ?? 1;
                if (!$this->canAddPackToCart($subject, $product, $packSize, $qty, $salableQty)) {
                    $errorMessage = __('The requested qty is not available');
                    $this->messageManager->addErrorMessage($errorMessage);
                    throw new LocalizedException($errorMessage);
                }
            }
        }

        return $proceed($product, $request, $processMode);
    }

    /**
     * @param Quote $quote
     * @param Product $product
     * @param $packSize
     * @param $qty
     * @param $salableQty
     * @return bool
     */
    protected function canAddPackToCart(Quote $quote, Product $product, $packSize, $qty, $salableQty): bool
    {
        // temporary fix for dropshippers;
        return true;
        $packQty = $qty * $packSize;
        $availableQty = $salableQty;

        if ($itemByProduct = $quote->getItemByProduct($product)) {
            $availableQty -= $itemByProduct->getQty() * $this->getPackSize($itemByProduct->getBuyRequest());
        }

        return $packQty <= $availableQty;
    }

    /**
     * @param Product $product
     * @return float
     * @throws LocalizedException|InputException|NoSuchEntityException
     */
    protected function getSalableQty(Product $product): float
    {
        $websiteCode = $this->storeManager->getWebsite()->getCode();
        $stock = $this->stockResolver->execute(SalesChannelInterface::TYPE_WEBSITE, $websiteCode);
        $stockId = $stock->getStockId();

        return $this->productSalableQty->execute($product->getSku(), $stockId);
    }

    /**
     * @param DataObject|null $request
     * @return int|mixed
     */
    protected function getPackSize(?DataObject $request)
    {
        if ($request instanceof DataObject) {
            $packOption = $request->getData('pack_option') ?? [];
            return $packOption['pack_size'] ?? 1;
        }

        return 1;
    }
}
