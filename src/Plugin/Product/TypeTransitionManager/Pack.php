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

namespace Nanobots\ProductPack\Plugin\Product\TypeTransitionManager;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\TypeTransitionManager;

class Pack
{
    /**
     * @param TypeTransitionManager $subject
     * @param callable $proceed
     * @param Product $product
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundProcessProduct(
        TypeTransitionManager $subject,
        callable $proceed,
        Product $product
    ) {
        $packOptions = $product->getPackOptions();
        if (!empty($packOptions)) {
            $product->setTypeId(\Nanobots\ProductPack\Model\Product\Type\Pack::TYPE_CODE);
            return;
        }
        $proceed($product);
    }
}
