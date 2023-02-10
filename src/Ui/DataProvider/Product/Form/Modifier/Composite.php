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

namespace Nanobots\ProductPack\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Catalog\Model\Product\Type as CatalogType;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\Downloadable\Model\Product\Type as DownloadableType;
use Magento\Ui\DataProvider\Modifier\ModifierFactory;
use Magento\Ui\DataProvider\Modifier\ModifierInterface;

class Composite extends AbstractModifier
{
    /** @var string  */
    const CHILDREN_PATH = 'product_attachment/children';

    /** @var string  */
    const CONTAINER_ATTACHMENTS = 'container_attachments';

    /** @var string  */
    const CONFIGURABLE_TYPE_CODE = 'configurable';

    /** @var string  */
    const GROUPED_TYPE_CODE = 'grouped';

    /** @var array  */
    private array $modifiers;

    /** @var LocatorInterface  */
    private LocatorInterface $locator;

    /** @var ModifierFactory  */
    private ModifierFactory $modifierFactory;

    /** @var array  */
    private array $modifiersInstances = [];

    /**
     * @param LocatorInterface $locator
     * @param ModifierFactory $modifierFactory
     * @param array $modifiers
     */
    public function __construct(
        LocatorInterface $locator,
        ModifierFactory $modifierFactory,
        array $modifiers = []
    ) {
        $this->locator = $locator;
        $this->modifierFactory = $modifierFactory;
        $this->modifiers = $modifiers;
    }

    /**
     * @param array $data
     * @return array $data
     */
    public function modifyData(array $data) : array
    {
        if ($this->canShowPackPanel()) {
            foreach ($this->getModifiers() as $modifier) {
                $data = $modifier->modifyData($data);
            }
        }

        return $data;
    }


    /**
     * @param array $meta
     * @return array $meta
     */
    public function modifyMeta(array $meta) : array
    {
        if ($this->canShowPackPanel()) {
            foreach ($this->getModifiers() as $modifier) {
                $meta = $modifier->modifyMeta($meta);
            }
        }

        return $meta;
    }


    /**
     * @return ModifierInterface[]
     */
    private function getModifiers() : array
    {
        if (empty($this->modifiersInstances)) {
            foreach ($this->modifiers as $modifierClass) {
                $this->modifiersInstances[$modifierClass] = $this->modifierFactory->create($modifierClass);
            }
        }

        return $this->modifiersInstances;
    }

    /**
     * @return bool
     */
    private function canShowPackPanel() : bool
    {
        $productTypes = [
            CatalogType::TYPE_SIMPLE,
            \Nanobots\ProductPack\Model\Product\Type\Pack::TYPE_CODE,
        ];

        return in_array((string) $this->locator->getProduct()->getTypeId(), $productTypes, true);
    }
}
