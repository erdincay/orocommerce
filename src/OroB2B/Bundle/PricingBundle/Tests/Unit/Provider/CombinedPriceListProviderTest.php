<?php

namespace OroB2B\Bundle\PricingBundle\Tests\Unit\Provider;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Persistence\ObjectRepository;

use OroB2B\Bundle\PricingBundle\Provider\CombinedPriceListProvider;
use OroB2B\Bundle\PricingBundle\Resolver\CombinedProductPriceResolver;

class CombinedPriceListProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var CombinedPriceListProvider
     */
    protected $provider;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|CombinedProductPriceResolver
     */
    protected $resolver;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|ManagerRegistry
     */
    protected $registry;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|ObjectRepository
     */
    protected $repository;

    protected function setUp()
    {
        $this->registry = $this->getRegistryMockWithRepository();
        $this->resolver = $this->getMockBuilder('OroB2B\Bundle\PricingBundle\Resolver\CombinedProductPriceResolver')
            ->disableOriginalConstructor()
            ->getMock();

        $this->provider = new CombinedPriceListProvider($this->registry);
        $this->provider->setClassName('OroB2B\Bundle\PricingBundle\Entity\CombinedPriceList');
        $this->provider->setResolver($this->resolver);
    }

    protected function tearDown()
    {
        unset($this->provider, $this->registry, $this->resolver);
    }

    /**
     * @dataProvider getCombinedPriceListDataProvider
     * @param array $data
     * @param boolean $force
     * @param array $expected
     */
    public function testGetCombinedPriceList(array $data, $force, array $expected)
    {
        $this->repository->expects($this->any())
            ->method('findOneBy')
            ->willReturn($data['priceListFromRepository']);

        $this->resolver->expects($this->exactly($expected['combineCallsCount']))->method('combinePrices');

        $priceListsRelations = $this->getPriceListsRelationMocks($data['priceListsRelationsData']);
        $combinedPriceList = $this->provider->getCombinedPriceList($priceListsRelations);
        $this->assertInstanceOf(
            'OroB2B\Bundle\PricingBundle\Entity\CombinedPriceList',
            $combinedPriceList
        );
        $this->assertEquals($expected['name'], $combinedPriceList->getName());
        $this->assertEquals($expected['currencies'], $combinedPriceList->getCurrencies());

        $this->provider->getCombinedPriceList($priceListsRelations, $force);
    }

    /**
     * @return array
     */
    public function getCombinedPriceListDataProvider()
    {
        $priceList = $this->getMock('OroB2B\Bundle\PricingBundle\Entity\CombinedPriceList');
        $priceList->expects($this->any())->method('getName')->willReturn('');
        $priceList->expects($this->any())->method('getCurrencies')->willReturn([]);

        return [
            'duplicate price lists force call' => [
                'data' => [
                    'priceListsRelationsData' => [
                        [
                            'price_list_id' => 1,
                            'currencies' => ['USD'],
                            'mergeAllowed' => true,
                        ],
                        [
                            'price_list_id' => 1,
                            'currencies' => ['USD'],
                            'mergeAllowed' => false,
                        ],
                        [
                            'price_list_id' => 2,
                            'currencies' => ['USD', 'EUR'],
                            'mergeAllowed' => false,
                        ],
                        [
                            'price_list_id' => 2,
                            'currencies' => ['USD', 'EUR'],
                            'mergeAllowed' => true,
                        ],
                    ],
                    'priceListFromRepository' => null,
                ],
                'force' => false,
                'expected' => [
                    'name' => '1t_2f_2t',
                    'currencies' => ['EUR', 'USD'],
                    'combineCallsCount' => 2,
                ]
            ],
            'empty price lists normal call' => [
                'data' => [
                    'priceListsRelationsData' => [],
                    'priceListFromRepository' => $priceList,
                ],
                'force' => true,
                'expected' => [
                    'name' => '',
                    'currencies' => [],
                    'combineCallsCount' => 1,
                ]
            ],
        ];
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Symfony\Bridge\Doctrine\RegistryInterface
     */
    protected function getRegistryMockWithRepository()
    {
        $this->repository = $this->getMockBuilder('\Doctrine\Common\Persistence\ObjectRepository')
            ->disableOriginalConstructor()
            ->getMock();

        $manager = $this->getMockBuilder('\Doctrine\Common\Persistence\ObjectManager')
            ->disableOriginalConstructor()
            ->getMock();

        $manager->expects($this->any())
            ->method('getRepository')
            ->willReturn($this->repository);

        $registry = $this->getRegistryMock();

        $registry->expects($this->any())
            ->method('getManagerForClass')
            ->willReturn($manager);

        return $registry;
    }

    /**
     * @param array $relations
     * @return array
     */
    protected function getPriceListsRelationMocks(array $relations)
    {
        $priceListsRelations = [];
        foreach ($relations as $priceListData) {
            $priceList = $this->getMock('OroB2B\Bundle\PricingBundle\Entity\PriceList');
            $priceList->expects($this->any())
                ->method('getId')
                ->willReturn($priceListData['price_list_id']);
            $priceList->expects($this->any())
                ->method('getCurrencies')
                ->willReturn($priceListData['currencies']);

            $priceListRelation = $this->getMockBuilder('OroB2B\Bundle\PricingBundle\Entity\BasePriceListRelation')
                ->disableOriginalConstructor()
                ->getMock();
            $priceListRelation->expects($this->any())
                ->method('getPriceList')
                ->willReturn($priceList);
            $priceListRelation->expects($this->any())
                ->method('isMergeAllowed')
                ->willReturn($priceListData['mergeAllowed']);

            $priceListsRelations[] = $priceListRelation;
        }

        return $priceListsRelations;
    }


    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Symfony\Bridge\Doctrine\RegistryInterface
     */
    protected function getRegistryMock()
    {
        return $this->getMock('Symfony\Bridge\Doctrine\RegistryInterface');
    }
}