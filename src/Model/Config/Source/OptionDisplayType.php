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

namespace Nanobots\ProductPack\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class OptionDisplayType implements OptionSourceInterface
{
    /**
     * @return array[]
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'discount', 'label' => __('Discount')],
            ['value' => 'price', 'label' => __('Price')]
        ];
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'discount' => __('Discount'),
            'price' => __('Price')
        ];
    }
}

