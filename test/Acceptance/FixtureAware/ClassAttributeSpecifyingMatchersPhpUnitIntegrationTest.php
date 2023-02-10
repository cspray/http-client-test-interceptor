<?php

namespace Cspray\HttpClientTestInterceptor\Acceptance\FixtureAware;

use Cspray\HttpClientTestInterceptor\Attribute\HttpFixture;
use Cspray\HttpClientTestInterceptor\Attribute\HttpRequestMatchers;
use Cspray\HttpClientTestInterceptor\HttpFixtureAwareTestTrait;
use Cspray\HttpClientTestInterceptor\Matcher\Matcher;
use Cspray\HttpClientTestInterceptor\Matcher\Strategy\CompositeMatcherStrategy;
use org\bovigo\vfs\vfsStream as VirtualFilesystem;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Cspray\HttpClientTestInterceptor\Interceptor\FixtureAwareInterceptor
 * @covers \Cspray\HttpClientTestInterceptor\Attribute\HttpFixture
 * @covers \Cspray\HttpClientTestInterceptor\Attribute\HttpRequestMatchers
 * @covers \Cspray\HttpClientTestInterceptor\Fixture\XmlFileBackedFixtureRepository
 * @covers \Cspray\HttpClientTestInterceptor\Matcher\Strategy\CompositeMatcherStrategy
 * @covers \Cspray\HttpClientTestInterceptor\Matcher\Matcher
 * @covers \Cspray\HttpClientTestInterceptor\HttpFixtureAwareTestTrait::getFixtureAwareInterceptor
 */
#[HttpFixture('vfs://root')]
#[HttpRequestMatchers(
    Matcher::Uri
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
            CompositeMatcherStrategy::class,
            $strategy
        );
        self::assertSame([
            Matcher::Uri->getStrategy()
        ], $strategy->getStrategies());
    }


}