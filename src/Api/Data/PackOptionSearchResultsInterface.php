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

namespace Api\Data;

interface PackOptionSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{

    /**
     * Get PackOption list.
     * @return \Api\Data\PackOptionInterface[]
     */
    public function getItems();

    /**
     * Set product_id list.
     * @param \Api\Data\PackOptionInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}

