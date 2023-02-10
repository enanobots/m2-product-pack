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

namespace Plugin\GetItemsToCancelFromOrderItem;

use Magento\InventorySales\Model\GetItemsToCancelFromOrderItem;
use Magento\InventorySalesApi\Api\Data\ItemToSellInterface;
use Magento\InventorySalesApi\Api\Data\ItemToSellInterfaceFactory;
use Magento\Sales\Model\Order\Item as OrderItem;
use Model\Product\Type\Pack;

class Execute
{
    private ItemToSellInterfaceFactory $itemToSell;

    /**
     * @param ItemToSellInterfaceFactory $itemToSell
     */
    public function __construct(ItemToSellInterfaceFactory $itemToSell)
    {
        $this->itemToSell = $itemToSell;
    }

    /**
     * @param GetItemsToCancelFromOrderItem $subject
     * @param callable $proceed
     * @param OrderItem $orderItem
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundExecute(GetItemsToCancelFromOrderItem $subject, callable $proceed, OrderItem $orderItem): array
    {
        $itemsToCancel = $proceed($orderItem);

        if ($orderItem->getProductType() === Pack::TYPE_CODE) {
            $packOption = $orderItem->getBuyRequest()->getData('pack_option');
            $packSize = $packOption['pack_size'];
            if ($packSize > 1) {
                /** @var ItemToSellInterface $item */
                foreach ($itemsToCancel as $i => $item) {
                    if ($item->getSku() === $orderItem->getSku()) {
                        $itemsToCancel[$i] = $this->itemToSell->create([
                            'sku' => $item->getSku(),
                            'qty' => $item->getQuantity() * $packSize
                        ]);
                    }
                }
            }
        }

        return $itemsToCancel;
    }
}
