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

namespace Plugin\Product;

use Model\Product\Type\Pack;
use Magento\Catalog\Model\Product;

class HasOptions
{
    /**
     * @param Product $subject
     * @param callable $proceed
     * @param string $key
     * @param null $index
     * @return string
     */
    public function aroundGetData(Product $subject, callable $proceed, $key = '', $index = null)
    {
        if ($subject->getTypeId() === Pack::TYPE_CODE && ($key === 'has_options' || $key === 'required_options')) {
            return '1';
        }

        return $proceed($key, $index);
    }
}
