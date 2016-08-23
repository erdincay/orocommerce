<?php

namespace OroB2B\Bundle\ShoppingListBundle\Tests\Unit\Layout\DataProvider;

use Doctrine\Bundle\DoctrineBundle\Registry;

use Oro\Component\Testing\Unit\EntityTrait;

use OroB2B\Bundle\PricingBundle\Model\PriceListRequestHandler;
use OroB2B\Bundle\PricingBundle\Manager\UserCurrencyManager;
use OroB2B\Bundle\ProductBundle\Entity\Product;
use OroB2B\Bundle\ShoppingListBundle\Entity\LineItem;
use OroB2B\Bundle\ShoppingListBundle\Entity\ShoppingList;
use OroB2B\Bundle\ShoppingListBundle\Layout\DataProvider\FrontendShoppingListProductsUnitsProvider;

class FrontendShoppingListProductsUnitsProviderTest extends \PHPUnit_Framework_TestCase
{
    use EntityTrait;

    const TEST_CURRENCY = 'USD';

    /**
     * @var FrontendShoppingListProductsUnitsProvider
     */
    protected $provider;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|Registry
     */
    protected $registry;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|PriceListRequestHandler
     */
    protected $requestHandler;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|UserCurrencyManager
     */
    protected $userCurrencyManager;

    public function setUp()
    {
        $this->registry = $this->getMockBuilder('Doctrine\Bundle\DoctrineBundle\Registry')
            ->disableOriginalConstructor()
            ->getMock();

        $this->requestHandler = $this
            ->getMockBuilder('OroB2B\Bundle\PricingBundle\Model\PriceListRequestHandler')
            ->disableOriginalConstructor()
            ->getMock();

        $this->userCurrencyManager = $this->getMockBuilder('OroB2B\Bundle\PricingBundle\Manager\UserCurrencyManager')
            ->disableOriginalConstructor()
            ->getMock();

        $this->provider = new FrontendShoppingListProductsUnitsProvider(
            $this->registry,
            $this->requestHandler,
            $this->userCurrencyManager
        );
    }

    /**
     * @dataProvider getDataDataProvider
     * @param ShoppingList|null $shoppingList
     * @param array|null $expected
     */
    public function testGetData($shoppingList, $expected)
    {
        if ($shoppingList) {
            $repository = $this->getMockBuilder('OroB2B\Bundle\ProductBundle\Entity\Repository\ProductUnitRepository')
                ->disableOriginalConstructor()
                ->getMock();
            $repository->expects($this->once())
                ->method('getProductsUnits')
                ->willReturn($expected);

            $em = $this->getMock('Doctrine\Common\Persistence\ManagerRegistry');
            $em->expects($this->once())
                ->method('getRepository')
                ->with('OroB2BProductBundle:ProductUnit')
                ->willReturn($repository);

            $this->registry->expects($this->once())
                ->method('getManagerForClass')
                ->with('OroB2BProductBundle:ProductUnit')
                ->willReturn($em);
        }

        $actual = $this->provider->getProductsUnits($shoppingList);

        $this->assertEquals($expected, $actual);
    }

    /**
     * @return array
     */
    public function getDataDataProvider()
    {
        /** @var Product $product1 */
        $product1 = $this->getEntity('OroB2B\Bundle\ProductBundle\Entity\Product', ['id'=> 123]);
        /** @var Product $product2 */
        $product2 = $this->getEntity('OroB2B\Bundle\ProductBundle\Entity\Product', ['id'=> 321]);

        $lineItem1 = new LineItem();
        $lineItem1->setProduct($product1);

        $lineItem2 = new LineItem();
        $lineItem2->setProduct($product2);

        $shoppingList = new ShoppingList();
        $shoppingList->addLineItem($lineItem1);
        $shoppingList->addLineItem($lineItem2);

        return [
            [
                'entity' => $shoppingList,
                'expected' => [
                    '123' => ['liter', 'bottle'],
                    '321' => ['piece' ]
                ]
            ],
            [
                'entity' => null,
                'expected' => null
            ]
        ];
    }
}