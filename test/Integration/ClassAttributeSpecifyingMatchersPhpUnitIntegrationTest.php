<?php

namespace Cspray\HttpClientTestInterceptor\Integration;

use Cspray\HttpClientTestInterceptor\Attribute\HttpFixture;
use Cspray\HttpClientTestInterceptor\Attribute\HttpRequestMatchers;
use Cspray\HttpClientTestInterceptor\HttpFixtureTrait;
use Cspray\HttpClientTestInterceptor\RequestMatchingStrategy\CompositeMatcher;
use Cspray\HttpClientTestInterceptor\RequestMatchingStrategy\Matchers;
use PHPUnit\Framework\TestCase;
use org\bovigo\vfs\vfsStream as VirtualFilesystem;

#[HttpFixture('vfs://root')]
#[HttpRequestMatchers(
    Matchers::Uri
)]
final class ClassAttributeSpecifyingMatchersPhpUnitIntegrationTest extends TestCase {

    use HttpFixtureTrait;

    protected function setUp() : void {
        parent::setUp();
        VirtualFilesystem::setup();
    }

    public function testGetTestInterceptorRespectsRequestMatchers() : void {
        $strategy = $this->getTestInterceptor()->getRequestMatchingStrategy();
        self::assertInstanceOf(
            CompositeMatcher::class,
            $strategy
        );
        self::assertSame([
            Matchers::Uri->getStrategy()
        ], $strategy->getStrategies());
    }


}