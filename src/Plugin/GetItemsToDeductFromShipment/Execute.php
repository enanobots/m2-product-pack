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

namespace Nanobots\ProductPack\Plugin\GetItemsToDeductFromShipment;

use Magento\InventoryShipping\Model\GetItemsToDeductFromShipment;
use Magento\InventorySourceDeductionApi\Model\ItemToDeductInterface;
use Magento\InventorySourceDeductionApi\Model\ItemToDeductInterfaceFactory;
use Magento\Sales\Model\Order\Shipment;
use Nanobots\ProductPack\Model\Product\Type\Pack;

class Execute
{
    private ItemToDeductInterfaceFactory $itemToDeduct;

    /**
     * @param ItemToDeductInterfaceFactory $itemToDeduct
     */
    public function __construct(ItemToDeductInterfaceFactory $itemToDeduct)
    {
        $this->itemToDeduct = $itemToDeduct;
    }

    /**
     * @param GetItemsToDeductFromShipment $subject
     * @param callable $proceed
     * @param Shipment $shipment
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundExecute(GetItemsToDeductFromShipment $subject, callable $proceed, Shipment $shipment): array
    {
        $itemsToDeduct = $proceed($shipment);
        $packSizes = [];

        /** @var Shipment\Item $shipmentItem */
        foreach ($shipment->getAllItems() as $shipmentItem) {
            $orderItem = $shipmentItem->getOrderItem();
            if ($orderItem->getProductType() === Pack::TYPE_CODE) {
                $packOption = $orderItem->getBuyRequest()->getData('pack_option');
                $packSize = $packOption['pack_size'];
                if ($packSize > 1) {
                    $packSizes[$orderItem->getSku()] = $packSize;
                }
            }
        }

        foreach ($packSizes as $sku => $qty) {
            /** @var ItemToDeductInterface $item */
            foreach ($itemsToDeduct as $i => $item) {
                if ($item->getSku() === $sku) {
                    $itemsToDeduct[$i] = $this->itemToDeduct->create([
                        'sku' => $sku,
                        'qty' => $item->getQty() * $qty
                    ]);
                }
            }
        }

        return $itemsToDeduct;
    }
}
