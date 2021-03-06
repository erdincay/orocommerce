<?php

namespace Oro\Bundle\CheckoutBundle\Tests\Unit\Provider;

use Doctrine\Common\Collections\ArrayCollection;
use Oro\Bundle\CheckoutBundle\DataProvider\Manager\CheckoutLineItemsManager;
use Oro\Bundle\CheckoutBundle\Entity\Checkout;
use Oro\Bundle\CheckoutBundle\Mapper\MapperInterface;
use Oro\Bundle\CheckoutBundle\Provider\CheckoutTotalsProvider;
use Oro\Bundle\CheckoutBundle\Shipping\Method\CheckoutShippingMethodsProviderInterface;
use Oro\Bundle\CurrencyBundle\Entity\Price;
use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\OrderBundle\Entity\Order;
use Oro\Bundle\OrderBundle\Entity\OrderAddress;
use Oro\Bundle\OrderBundle\Entity\OrderLineItem;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\PricingBundle\SubtotalProcessor\TotalProcessorProvider;
use Oro\Bundle\WebsiteBundle\Entity\Website;
use Oro\Component\Testing\Unit\EntityTrait;

class CheckoutTotalsProviderTest extends \PHPUnit_Framework_TestCase
{
    use EntityTrait;

    /**
     * @var CheckoutLineItemsManager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $checkoutLineItemsManager;

    /**
     * @var TotalProcessorProvider|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $totalsProvider;

    /**
     * @var CheckoutTotalsProvider
     */
    protected $provider;

    /**
     * @var MapperInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $mapper;

    /**
     * @var CheckoutShippingMethodsProviderInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $checkoutShippingMethodsProvider;

    public function setUp()
    {
        $this->checkoutLineItemsManager = $this
            ->getMockBuilder(CheckoutLineItemsManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->totalsProvider = $this
            ->getMockBuilder(TotalProcessorProvider::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->mapper = $this->createMock(MapperInterface::class);
        $this->checkoutShippingMethodsProvider = $this->createMock(CheckoutShippingMethodsProviderInterface::class);

        $this->provider = new CheckoutTotalsProvider(
            $this->checkoutLineItemsManager,
            $this->totalsProvider,
            $this->mapper,
            $this->checkoutShippingMethodsProvider
        );
    }

    public function testGetTotalsArray()
    {
        $lineItems = new ArrayCollection([new OrderLineItem()]);
        $website = new Website();
        $organization = new Organization();
        $price = Price::create(10, 'USD');
        $address = new OrderAddress();
        $address->setLabel('order address');
        $customer = new Customer();
        $customer->setName('order customer');

        $checkout = new Checkout();

        /** @var Order $order */
        $order = $this->getEntity(
            Order::class,
            [
                'estimatedShippingCostAmount' => $price->getValue(),
                'currency' => $price->getCurrency(),
                'shippingAddress' => $address,
                'billingAddress' => $address,
                'customer' => $customer,
                'website' => $website,
                'organization' => $organization,
                'lineItems' => $lineItems,
            ]
        );

        $this->checkoutShippingMethodsProvider
            ->expects(static::once())
            ->method('getPrice')
            ->with($checkout)
            ->willReturn($price);

        $this->mapper
            ->expects(static::once())
            ->method('map')
            ->with($checkout, ['lineItems' => $lineItems])
            ->willReturn($order);

        $this->checkoutLineItemsManager
            ->expects(static::once())
            ->method('getData')
            ->with($checkout)
            ->willReturn($lineItems);

        $this->totalsProvider
            ->expects(static::once())
            ->method('enableRecalculation');

        $this->totalsProvider
            ->expects(static::once())
            ->method('getTotalWithSubtotalsAsArray')
            ->with($order)
            ->will(
                $this->returnCallback(
                    function (Order $order) use ($lineItems, $price, $address, $customer, $website, $organization) {
                        static::assertEquals($lineItems, $order->getLineItems());
                        static::assertEquals($price, $order->getShippingCost());
                        static::assertSame($address, $order->getBillingAddress());
                        static::assertSame($address, $order->getShippingAddress());
                        static::assertSame($customer, $order->getCustomer());
                        static::assertSame($website, $order->getWebsite());
                        static::assertSame($organization, $order->getOrganization());
                    }
                )
            );

        $this->provider->getTotalsArray($checkout);
    }
}
