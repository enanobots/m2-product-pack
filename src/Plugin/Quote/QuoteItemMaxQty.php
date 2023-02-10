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

declare(strict_types = 1);

namespace Nanobots\ProductPack\Plugin\Quote;

use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\CatalogInventory\Model\Quote\Item\QuantityValidator\Initializer\StockItem as Subject;
use Magento\Quote\Model\Quote\Item;
use Nanobots\ProductPack\Model\Product\Type\Pack;

class QuoteItemMaxQty
{
    public function beforeInitialize(
        Subject            $subject,
        StockItemInterface $stockItem,
        Item               $quoteItem,
                           $qty
    ): array {
        if (is_a($quoteItem->getProduct()->getTypeInstance(), Pack::class)) {
            $multiplier = max(1, (int)$quoteItem->getBuyRequest()
                ->getDataByPath('pack_option/pack_size'));
            $qty = $multiplier * $qty;
        }

        return [$stockItem, $quoteItem, $qty];
    }
}
