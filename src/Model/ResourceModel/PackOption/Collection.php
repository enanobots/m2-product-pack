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

namespace Nanobots\ProductPack\Model\ResourceModel\PackOption;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Nanobots\ProductPack\Model\ResourceModel\PackOption;

class Collection extends AbstractCollection
{
    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            \Nanobots\ProductPack\Model\PackOption::class,
            PackOption::class
        );
    }
}
