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

namespace Nanobots\ProductPack\Helper\Catalog\Product;

use Magento\Catalog\Helper\Product\Configuration\ConfigurationInterface;
use Magento\Catalog\Model\Product\Configuration\Item\ItemInterface;

class Configuration implements ConfigurationInterface
{
    /** @var \Magento\Catalog\Helper\Product\Configuration  */
    protected \Magento\Catalog\Helper\Product\Configuration $productConfiguration;

    /**
     * @param \Magento\Catalog\Helper\Product\Configuration $productConfiguration
     */
    public function __construct(
        \Magento\Catalog\Helper\Product\Configuration $productConfiguration
    ) {
        $this->productConfiguration = $productConfiguration;
    }

    /**
     * Get Options
     *
     * @param ItemInterface $item
     * @return string[]
     */
    public function getOptions(ItemInterface $item): array
    {
        $buyRequest = $item->getOptionByCode('info_buyRequest');
        $packOption = json_decode($buyRequest->getValue(), true);
        $unitAttribute = $item->getProduct()->getResource()->getAttribute('unit');

        return array_merge(
            [
                [
                    'label' => __('Package'),
                    'value' => $packOption['pack_option']['title']
                ],
                [
                    'label' => __('Units'),
                    'value' => $packOption['pack_option']['pack_size']
                ]
            ],
            $this->productConfiguration->getCustomOptions($item)
        );
    }
}
