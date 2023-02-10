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

namespace Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\Ui\Component\Container;
use Magento\Ui\Component\DynamicRows;
use Magento\Ui\Component\Form\Element\DataType\Number;
use Magento\Ui\Component\Form\Element\DataType\Text;
use Magento\Ui\Component\Form\Element\Hidden;
use Magento\Ui\Component\Form\Element\Input;
use Magento\Ui\Component\Form\Element\Select;
use Magento\Ui\Component\Form\Field;
use Magento\Ui\Component\Form\Fieldset;
use Helper\Type;
use function Nanobots\ProductPack\Ui\DataProvider\Product\Form\Modifier\__;

class PackOptions extends AbstractModifier
{
    public const GROUP_PACK_OPTIONS_NAME = 'pack_options';
    public const GROUP_PACK_OPTIONS_SCOPE = 'data.product';
    public const GROUP_PACK_OPTIONS_PREVIOUS_NAME = 'custom_options';
    public const GROUP_PACK_OPTIONS_DEFAULT_SORT_ORDER = 32;

    public const CONTAINER_HEADER_NAME = 'container_header';
    public const CONTAINER_OPTION = 'container_option';
    public const CONTAINER_COMMON_NAME = 'container_common';

    public const BUTTON_ADD = 'button_add';

    public const GRID_OPTIONS_NAME = 'pack_options';

    public const FIELD_ENABLE = 'affect_product_pack_options';
    public const FIELD_PACK_OPTION_ID = 'packoption_id';
    public const FIELD_PACKAGE_NAME = 'package_name';
    public const FIELD_DISCOUNT_TYPE_NAME = 'discount_type';
    public const FIELD_DISCOUNT_VALUE_NAME = 'discount_value';
    public const FIELD_PACK_SIZE_NAME = 'pack_size';
    public const FIELD_EXTRA_WEIGHT_NAME = 'extra_weight';
    public const FIELD_SORT_ORDER_NAME = 'sort_order';
    public const FIELD_IS_DELETE = 'is_delete';

    /**
     * @var array
     * @since 101.0.0
     */
    protected array $meta = [];

    /** @var LocatorInterface  */
    protected LocatorInterface $locator;

    /** @var Type  */
    protected Type $type;

    /**
     * @param LocatorInterface $locator
     * @param Type $type
     */
    public function __construct(
        LocatorInterface $locator,
        Type $type
    ) {
        $this->locator = $locator;
        $this->type = $type;
    }

    /**
     * @inheritdoc
     * @since 101.0.0
     */
    public function modifyMeta(array $meta)
    {
        $this->meta = $meta;

        $this->createPackOptionsPanel();

        return $this->meta;
    }

    /**
     * @param array $data
     * @return array
     */
    public function modifyData(array $data)
    {
        $productOptions = $this->locator->getProduct()->getExtensionAttributes()->getPackOptions() ?: [];

        return array_replace_recursive(
            $data,
            [
                $this->locator->getProduct()->getId() => [
                    static::DATA_SOURCE_DEFAULT => [
                        static::FIELD_ENABLE => 1,
                        static::GRID_OPTIONS_NAME => $productOptions
                    ]
                ]
            ]
        );
    }

    /**
     * @return $this
     */
    protected function createPackOptionsPanel(): PackOptions
    {
        $this->meta = array_replace_recursive(
            $this->meta,
            [
                static::GROUP_PACK_OPTIONS_NAME => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'label' => __('Pack Options'),
                                'componentType' => Fieldset::NAME,
                                'dataScope' => static::GROUP_PACK_OPTIONS_SCOPE,
                                'collapsible' => true,
                                'sortOrder' => $this->getNextGroupSortOrder(
                                    $this->meta,
                                    static::GROUP_PACK_OPTIONS_PREVIOUS_NAME,
                                    static::GROUP_PACK_OPTIONS_DEFAULT_SORT_ORDER
                                ),
                            ],
                        ],
                    ],
                    'children' => [
                        static::CONTAINER_HEADER_NAME => $this->getHeaderContainerConfig(10),
                        static::FIELD_ENABLE => $this->getEnableFieldConfig(20),
                        static::GRID_OPTIONS_NAME => $this->getOptionsGridConfig(30)
                    ]
                ]
            ]
        );

        return $this;
    }

    /**
     * @param int $sortOrder
     * @return array
     */
    protected function getHeaderContainerConfig(int $sortOrder): array
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label' => null,
                        'formElement' => Container::NAME,
                        'componentType' => Container::NAME,
                        'template' => 'ui/form/components/complex',
                        'sortOrder' => $sortOrder,
                        'content' => __('Pack options let customers choose the products in packets.'),
                    ],
                ],
            ],
            'children' => [
                static::BUTTON_ADD => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'title' => __('Add Package Option'),
                                'formElement' => Container::NAME,
                                'componentType' => Container::NAME,
                                'component' => 'Magento_Ui/js/form/components/button',
                                'sortOrder' => 20,
                                'actions' => [
                                    [
                                        'targetName' => '${ $.ns }.${ $.ns }.' . static::GROUP_PACK_OPTIONS_NAME
                                            . '.' . static::GRID_OPTIONS_NAME,
                                        '__disableTmpl' => ['targetName' => false],
                                        'actionName' => 'processingAddChild',
                                    ]
                                ]
                            ]
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @param int $sortOrder
     * @return array
     */
    protected function getOptionsGridConfig(int $sortOrder): array
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'addButtonLabel' => __('Add Option'),
                        'componentType' => DynamicRows::NAME,
                        'component' => 'Nanobots_ProductPack/js/components/dynamic-rows-pack-options',
                        'template' => 'ui/dynamic-rows/templates/collapsible',
                        'additionalClasses' => 'admin__field-wide',
                        'deleteProperty' => static::FIELD_IS_DELETE,
                        'deleteValue' => '1',
                        'addButton' => false,
                        'renderDefaultRecord' => false,
                        'columnsHeader' => false,
                        'collapsibleHeader' => true,
                        'sortOrder' => $sortOrder,
                        'imports' => [
                            'insertData' => '${ $.provider }:${ $.dataProvider }',
                            '__disableTmpl' => ['insertData' => false],
                        ],
                    ],
                ],
            ],
            'children' => [
                'record' => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'headerLabel' => __('New Pack Option'),
                                'componentType' => Container::NAME,
                                'component' => 'Magento_Ui/js/dynamic-rows/record',
                                'positionProvider' => static::CONTAINER_OPTION . '.' . static::FIELD_SORT_ORDER_NAME,
                                'isTemplate' => true,
                                'is_collection' => true,
                            ],
                        ],
                    ],
                    'children' => [
                        static::CONTAINER_OPTION => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'componentType' => Fieldset::NAME,
                                        'collapsible' => true,
                                        'label' => null,
                                        'sortOrder' => 10,
                                        'opened' => true,
                                    ],
                                ],
                            ],
                            'children' => [
                                static::FIELD_SORT_ORDER_NAME => $this->getPositionFieldConfig(40),
                                static::CONTAINER_COMMON_NAME => $this->getCommonContainerConfig(10)
                            ]
                        ],
                    ]
                ]
            ]
        ];
    }

    /**
     * @param int $sortOrder
     * @return \array[][][]
     */
    protected function getEnableFieldConfig(int $sortOrder): array
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'formElement' => Field::NAME,
                        'componentType' => Input::NAME,
                        'dataScope' => static::FIELD_ENABLE,
                        'dataType' => Number::NAME,
                        'visible' => false,
                        'sortOrder' => $sortOrder,
                    ],
                ],
            ],
        ];
    }

    /**
     * @param int $sortOrder
     * @return array
     */
    protected function getCommonContainerConfig(int $sortOrder): array
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'componentType' => Container::NAME,
                        'formElement' => Container::NAME,
                        'component' => 'Magento_Ui/js/form/components/group',
                        'breakLine' => false,
                        'showLabel' => false,
                        'additionalClasses' => 'admin__field-group-columns admin__control-group-equal',
                        'sortOrder' => $sortOrder,
                    ],
                ],
            ],
            'children' => [
                static::FIELD_PACK_OPTION_ID => $this->getOptionIdFieldConfig(10),
                static::FIELD_PACKAGE_NAME => $this->getPackageNameConfig(15),
                static::FIELD_DISCOUNT_TYPE_NAME => $this->getDiscountTypeFieldConfig(30),
                static::FIELD_DISCOUNT_VALUE_NAME => $this->getDiscountValueFieldConfig(40),
                static::FIELD_PACK_SIZE_NAME => $this->getPackSizeFieldConfig(45),
                static::FIELD_EXTRA_WEIGHT_NAME => $this->getExtraWeightFieldConfig(50),
            ]
        ];
    }

    /**
     * @param int $sortOrder
     * @return \array[][][]
     */
    protected function getOptionIdFieldConfig(int $sortOrder): array
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'formElement' => Input::NAME,
                        'componentType' => Field::NAME,
                        'dataScope' => static::FIELD_PACK_OPTION_ID,
                        'sortOrder' => $sortOrder,
                        'visible' => false,
                    ],
                ],
            ],
        ];
    }

    /**
     * @param int $sortOrder
     * @return \array[][][]
     */
    protected function getPackageNameConfig(int $sortOrder): array
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label' => __('Package Name'),
                        'componentType' => Field::NAME,
                        'formElement' => Input::NAME,
                        'dataScope' => static::FIELD_PACKAGE_NAME,
                        'dataType' => Text::NAME,
                        'sortOrder' => $sortOrder,
                        'validation' => [
                            'required-entry' => true
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @param int $sortOrder
     * @return \array[][][]
     */
    protected function getDiscountTypeFieldConfig(int $sortOrder): array
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'value' => 'percent',
                        'label' => __('Discount Type'),
                        'componentType' => Field::NAME,
                        'formElement' => Select::NAME,
                        'component' => 'Magento_Catalog/js/custom-options-type',
                        'elementTmpl' => 'ui/grid/filters/elements/ui-select',
                        'dataScope' => static::FIELD_DISCOUNT_TYPE_NAME,
                        'dataType' => Text::NAME,
                        'sortOrder' => $sortOrder,
                        'options' => $this->getDiscountTypes(),
                        'disableLabel' => true,
                        'multiple' => false,
                        'selectedPlaceholders' => [
                            'defaultPlaceholder' => __('-- Please select --'),
                        ],
                        'validation' => [
                            'required-entry' => true
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @param int $sortOrder
     * @return \array[][][]
     */
    protected function getDiscountValueFieldConfig(int $sortOrder): array
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label' => __('Discount Value'),
                        'componentType' => Field::NAME,
                        'formElement' => Input::NAME,
                        'dataScope' => static::FIELD_DISCOUNT_VALUE_NAME,
                        'dataType' => Number::NAME,
                        'sortOrder' => $sortOrder,
                        'value' => '0',
                    ],
                ],
            ],
        ];
    }

    /**
     * @param int $sortOrder
     * @return \array[][][]
     */
    protected function getPositionFieldConfig(int $sortOrder): array
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'componentType' => Field::NAME,
                        'formElement' => Hidden::NAME,
                        'dataScope' => static::FIELD_SORT_ORDER_NAME,
                        'dataType' => Number::NAME,
                        'visible' => false,
                        'sortOrder' => $sortOrder,
                    ],
                ],
            ],
        ];
    }

    /**
     * @param int $sortOrder
     * @return \array[][][]
     */
    protected function getPackSizeFieldConfig(int $sortOrder): array
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label' => __('Qty in Pack'),
                        'componentType' => Field::NAME,
                        'formElement' => Input::NAME,
                        'dataScope' => static::FIELD_PACK_SIZE_NAME,
                        'dataType' => Number::NAME,
                        'sortOrder' => $sortOrder,
                    ],
                ],
            ],
        ];
    }
    /**
     * @param int $sortOrder
     * @return \array[][][]
     */
    protected function getExtraWeightFieldConfig(int $sortOrder): array
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label' => __('Extra Weight'),
                        'componentType' => Field::NAME,
                        'formElement' => Input::NAME,
                        'dataScope' => static::FIELD_EXTRA_WEIGHT_NAME,
                        'dataType' => Number::NAME,
                        'sortOrder' => $sortOrder,
                    ],
                ],
            ],
        ];
    }


    /**
     * @return array[]
     */
    protected function getDiscountTypes(): array
    {
        return [
            [
                'label' => __('Percent'),
                'value' => 'percent'
            ],
            [
                'label' => __('Fixed'),
                'value' => 'fixed'
            ],
        ];
    }
}
