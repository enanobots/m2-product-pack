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

namespace Nanobots\ProductPack\Api;

interface PackOptionRepositoryInterface
{

    /**
     * Save PackOption
     * @param \Nanobots\ProductPack\Api\Data\PackOptionInterface $packOption
     * @return \Nanobots\ProductPack\Api\Data\PackOptionInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(
        \Nanobots\ProductPack\Api\Data\PackOptionInterface $packOption
    );

    /**
     * Retrieve PackOption
     * @param string $packoptionId
     * @return \Nanobots\ProductPack\Api\Data\PackOptionInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function get($packOptionId);

    /**
     * Retrieve PackOption matching the specified criteria.
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Nanobots\ProductPack\Api\Data\PackOptionSearchResultsInterface
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
    );

    /**
     * Delete PackOption
     * @param \Nanobots\ProductPack\Api\Data\PackOptionInterface $packOption
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(
        \Nanobots\ProductPack\Api\Data\PackOptionInterface $packOption
    );

    /**
     * Delete PackOption by ID
     * @param string $packoptionId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($packoptionId);
}

