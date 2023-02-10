<?php
/**
 * Created by Q-Solutions Studio
 *
 * @category    Nanobots
 * @package     Nanobots_ProductPack
 * @author      Wojciech M. Wnuk <wojtek@qsolutionsstudio.com>
 */

namespace Test\Integration;

use Model\Product\Type\Pack;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Tests product model:
 * - general behaviour is tested (external interaction and pricing is not tested there)
 *
 * @see \Magento\Catalog\Model\ProductExternalTest
 * @see \Magento\Catalog\Model\ProductPriceTest
 * @magentoDataFixture Nanobots_ProductPack::Test/Integration/_files/product_with_pack_options.php
 * @magentoDbIsolation enabled
 * @magentoAppIsolation enabled
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class ProductPackTest extends TestCase
{
    protected ProductRepositoryInterface $productRepository;
    protected Product $_model;
    protected ObjectManagerInterface $objectManager;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->productRepository = $this->objectManager->create(ProductRepositoryInterface::class);
        $this->_model = $this->objectManager->create(Product::class);
    }

    /**
     * @throws NoSuchEntityException
     */
    public function testProductType(): void
    {
        $product = $this->productRepository->get('simple_with_pack_options');
        $this->assertEquals(Pack::TYPE_CODE, $product->getTypeId());
    }

    /**
     * @throws NoSuchEntityException
     */
    public function testPackOptions(): void
    {
        $product = $this->_getProduct();
        $packOptions = $product->getExtensionAttributes()->getPackOptions();

        $this->assertEquals(3, count($packOptions));
    }

    /**
     * @throws NoSuchEntityException
     */
    public function testPriceWithoutOptions(): void
    {
        $product = $this->_getProduct();

        $this->assertEquals(250, $product->getPrice());
    }

    public function testPriceWithOptionPercent(): void
    {
        /** @var Product $product */
        $product = $this->_getProduct();
        $product->addCustomOption('info_buyRequest', [
            'item' => $product->getId(),
            'product' => $product->getId(),
            'pack_option' => [
                'title' => 'Big Box',
                'discount_type' => 'percent',
                'discount_value' => 10,
                'extra_weight' => 2,
            ],
            'qty' => 50
        ]);
        $multiplier = 1 - (10/100);

        $this->assertEquals(250 * $multiplier, $product->getPrice());
    }

    public function testPriceWithOptionFixed(): void
    {
        /** @var Product $product */
        $product = $this->_getProduct();
        $product->addCustomOption('info_buyRequest', [
            'item' => $product->getId(),
            'product' => $product->getId(),
            'pack_option' => [
                'title' => 'Box',
                'discount_type' => 'fixed',
                'discount_value' => 2,
                'extra_weight' => 0.5,
            ],
            'qty' => 10
        ]);

        $this->assertEquals(250 - 2, $product->getPrice());
    }

    public function testExtraWeight(): void
    {
        /** @var Product $product */
        $product = $this->_getProduct();
        $product->addCustomOption('info_buyRequest', [
            'item' => $product->getId(),
            'product' => $product->getId(),
            'pack_option' => [
                'title' => 'Box',
                'discount_type' => 'fixed',
                'discount_value' => 2,
                'extra_weight' => 0.5,
            ],
            'qty' => 10
        ]);

        $this->assertEquals(10.5, $product->getWeight() * 10);
    }



    /**
     * @return ProductInterface
     * @throws NoSuchEntityException
     */
    protected function _getProduct(): ProductInterface
    {
        return $this->productRepository->get('simple_with_pack_options');
    }
}
