<?php

namespace Cspray\HttpClientTestInterceptor\Acceptance\FixtureAware;

use Cspray\HttpClientTestInterceptor\Attribute\HttpFixture;
use Cspray\HttpClientTestInterceptor\Attribute\HttpRequestMatchers;
use Cspray\HttpClientTestInterceptor\HttpFixtureAwareTestTrait;
use Cspray\HttpClientTestInterceptor\RequestMatchingStrategy\CompositeMatcher;
use Cspray\HttpClientTestInterceptor\RequestMatchingStrategy\Matchers;
use org\bovigo\vfs\vfsStream as VirtualFilesystem;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Cspray\HttpClientTestInterceptor\FixtureAwareInterceptor
 * @covers \Cspray\HttpClientTestInterceptor\Attribute\HttpFixture
 * @covers \Cspray\HttpClientTestInterceptor\Attribute\HttpRequestMatchers
 * @covers \Cspray\HttpClientTestInterceptor\Fixture\XmlFileBackedFixtureRepository
 * @covers \Cspray\HttpClientTestInterceptor\RequestMatchingStrategy\CompositeMatcher
 * @covers \Cspray\HttpClientTestInterceptor\RequestMatchingStrategy\Matchers
 * @covers \Cspray\HttpClientTestInterceptor\HttpFixtureAwareTestTrait::getFixtureAwareInterceptor
 */
#[HttpFixture('vfs://root')]
#[HttpRequestMatchers(
    Matchers::Uri
)]
final class ClassAttributeSpecifyingMatchersPhpUnitIntegrationTest extends TestCase {

    use HttpFixtureAwareTestTrait;

    protected function setUp() : void {
        parent::setUp();
        VirtualFilesystem::setup();
    }

    public function testGetTestInterceptorRespectsRequestMatchers() : void {
        $strategy = $this->getFixtureAwareInterceptor()->getRequestMatchingStrategy();
        self::assertInstanceOf(
            CompositeMatcher::class,
            $strategy
        );
        self::assertSame([
            Matchers::Uri->getStrategy()
        ], $strategy->getStrategies());
    }


}