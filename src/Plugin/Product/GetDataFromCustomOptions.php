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

namespace Plugin\Product;

use Magento\Catalog\Model\Product;
use Magento\Framework\Serialize\Serializer\Json;
use Model\Product\Type\Pack;

class GetDataFromCustomOptions
{
    protected Json $serializer;

    public function __construct(Json $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * @param Product $product
     * @param $result
     * @param $key
     * @param $index
     * @return mixed
     */
    public function afterGetData(
        Product $product,
        $result,
        $key = '',
        $index = null
    ) {
        if ($product->getTypeId() !== Pack::TYPE_CODE
            || in_array($key, ['value', 'title'])) {
            return $result;
        }

        $customOption = $product->getCustomOption('pack_option');
        if (empty($customOption) || empty($customOption->getValue())) {
            return $result;
        }

        $packOption = $this->serializer->unserialize($customOption->getValue());

        return isset($packOption[$key]) && !empty($packOption[$key]) ?
            $packOption[$key] : $result;
    }
}
