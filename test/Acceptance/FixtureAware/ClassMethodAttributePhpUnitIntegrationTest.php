<?php

namespace Cspray\HttpClientTestInterceptor\Acceptance\FixtureAware;

use Cspray\HttpClientTestInterceptor\Attribute\HttpFixture;
use Cspray\HttpClientTestInterceptor\Attribute\HttpRequestMatchers;
use Cspray\HttpClientTestInterceptor\Fixture\XmlFileBackedFixtureRepository;
use Cspray\HttpClientTestInterceptor\HttpFixtureAwareTestTrait;
use Cspray\HttpClientTestInterceptor\RequestMatchingStrategy\CompositeMatcher;
use Cspray\HttpClientTestInterceptor\RequestMatchingStrategy\Matchers;
use org\bovigo\vfs\vfsStream as VirtualFilesystem;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * @covers \Cspray\HttpClientTestInterceptor\Fixture\XmlFileBackedFixtureRepository
 * @covers \Cspray\HttpClientTestInterceptor\Attribute\HttpFixture
 * @covers \Cspray\HttpClientTestInterceptor\RequestMatchingStrategy\CompositeMatcher
 * @covers \Cspray\HttpClientTestInterceptor\RequestMatchingStrategy\Matchers
 * @covers \Cspray\HttpClientTestInterceptor\FixtureAwareInterceptor
 * @covers \Cspray\HttpClientTestInterceptor\Attribute\HttpRequestMatchers
 * @covers \Cspray\HttpClientTestInterceptor\HttpFixtureAwareTestTrait::getFixtureAwareInterceptor
 */
final class ClassMethodAttributePhpUnitIntegrationTest extends TestCase {

    use HttpFixtureAwareTestTrait;

    protected function setUp() : void {
        $root = VirtualFilesystem::setup();
    }

    #[HttpFixture('vfs://root')]
    public function testFixturePathRespected() : void {
        $fixtureRepoReflection = new ReflectionClass(XmlFileBackedFixtureRepository::class);
        $fixtureDirProperty = $fixtureRepoReflection->getProperty('fixtureDir');
        $value = $fixtureDirProperty->getValue($this->getFixtureAwareInterceptor()->getFixtureRepository());

        self::assertSame('vfs://root', $value);
    }

    #[HttpFixture('vfs://root')]
    #[HttpRequestMatchers(Matchers::Body)]
    public function testRequestMatchersRespected() : void {
        $matchingStrategy = $this->getFixtureAwareInterceptor()->getRequestMatchingStrategy();
        self::assertInstanceOf(
            CompositeMatcher::class,
            $matchingStrategy
        );
        self::assertSame([
            Matchers::Body->getStrategy()
        ], $matchingStrategy->getStrategies());
    }

}