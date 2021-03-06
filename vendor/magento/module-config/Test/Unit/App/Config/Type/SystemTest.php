<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Config\Test\Unit\App\Config\Type;

use Magento\Config\App\Config\Type\System;
use Magento\Framework\App\Config\ConfigSourceInterface;
use Magento\Framework\App\Config\Spi\PostProcessorInterface;
use Magento\Framework\App\Config\Spi\PreProcessorInterface;
use Magento\Framework\Cache\FrontendInterface;
use Magento\Framework\Serialize\Serializer\Sensitive;
use Magento\Framework\Serialize\Serializer\SensitiveFactory;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Store\Model\Config\Processor\Fallback;
use Magento\Config\App\Config\Type\System\Reader;

/**
 * Test how Class process source, cache them and retrieve value by path
 * @package Magento\Config\Test\Unit\App\Config\Type
 */
class SystemTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ConfigSourceInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $source;

    /**
     * @var PostProcessorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $postProcessor;

    /**
     * @var PreProcessorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $preProcessor;

    /**
     * @var Fallback|\PHPUnit_Framework_MockObject_MockObject
     */
    private $fallback;

    /**
     * @var FrontendInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $cache;

    /**
     * @var System
     */
    private $configType;

    /**
     * @var SerializerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $serializer;

    /**
     * @var Reader|\PHPUnit_Framework_MockObject_MockObject
     */
    private $reader;

    public function setUp()
    {
        $this->source = $this->getMockBuilder(ConfigSourceInterface::class)
            ->getMockForAbstractClass();
        $this->postProcessor = $this->getMockBuilder(PostProcessorInterface::class)
            ->getMockForAbstractClass();
        $this->fallback = $this->getMockBuilder(Fallback::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->cache = $this->getMockBuilder(FrontendInterface::class)
            ->getMockForAbstractClass();
        $this->preProcessor = $this->getMockBuilder(PreProcessorInterface::class)
            ->getMockForAbstractClass();
        $this->serializer = $this->getMockBuilder(Sensitive::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->reader = $this->getMockBuilder(Reader::class)
            ->disableOriginalConstructor()
            ->getMock();
        $sensitiveFactory = $this->getMockBuilder(SensitiveFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $sensitiveFactory->expects($this->any())
            ->method('create')
            ->willReturn($this->serializer);
        /** @var SerializerInterface|\PHPUnit_Framework_MockObject_MockObject $serializerMock */
        $serializerMock = $this->getMockBuilder(SerializerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->configType = new System(
            $this->source,
            $this->postProcessor,
            $this->fallback,
            $this->cache,
            $serializerMock,
            $this->preProcessor,
            1,
            'system',
            $this->reader,
            $sensitiveFactory
        );
    }

    public function testGetCachedWithLoadDefaultScopeData()
    {
        $path = 'default/dev/unsecure/url';
        $url = 'http://magento.test/';
        $data = [
            'dev' => [
                'unsecure' => [
                    'url' => $url
                ]
            ]
        ];

        $this->cache->expects($this->any())
            ->method('load')
            ->willReturnOnConsecutiveCalls('1', serialize($data));
        $this->serializer->expects($this->once())
            ->method('unserialize')
            ->willReturn($data);
        $this->assertEquals($url, $this->configType->get($path));
    }

    public function testGetCachedWithLoadAllData()
    {
        $url = 'http://magento.test/';
        $data = [
            'dev' => [
                'unsecure' => [
                    'url' => $url
                ]
            ]
        ];

        $this->cache->expects($this->any())
            ->method('load')
            ->willReturnOnConsecutiveCalls('1', serialize($data));
        $this->serializer->expects($this->once())
            ->method('unserialize')
            ->willReturn($data);
        $this->assertEquals($data, $this->configType->get(''));
    }
}
