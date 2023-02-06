<?php

namespace Cspray\HttpClientTestInterceptor\Acceptance\FixtureAware;

use Cspray\HttpClientTestInterceptor\Attribute\HttpFixture;
use Cspray\HttpClientTestInterceptor\Fixture\XmlFileBackedFixtureRepository;
use Cspray\HttpClientTestInterceptor\HttpFixtureAwareTestTrait;
use Cspray\HttpClientTestInterceptor\Matcher;
use Cspray\HttpClientTestInterceptor\RequestMatcherStrategy\CompositeMatch;
use org\bovigo\vfs\vfsStream as VirtualFilesystem;
use org\bovigo\vfs\vfsStreamDirectory as VirtualDirectory;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * @covers \Cspray\HttpClientTestInterceptor\FixtureAwareInterceptor
 * @covers \Cspray\HttpClientTestInterceptor\Attribute\HttpFixture
 * @covers \Cspray\HttpClientTestInterceptor\Fixture\XmlFileBackedFixtureRepository
 * @covers \Cspray\HttpClientTestInterceptor\RequestMatcherStrategy\CompositeMatch
 * @covers \Cspray\HttpClientTestInterceptor\Matcher
 * @covers \Cspray\HttpClientTestInterceptor\HttpFixtureAwareTestTrait::getFixtureAwareInterceptor
 */
#[HttpFixture('vfs://root')]
final class ClassAttributePhpUnitIntegrationTest extends TestCase {

    use HttpFixtureAwareTestTrait;

    private VirtualDirectory $vfs;

    protected function setUp() : void {
        $this->vfs = VirtualFilesystem::setup();
    }

    public function testGetTestInterceptorReturnsInstance() : void {
        self::assertNotNull($this->getFixtureAwareInterceptor());
    }

    public function testGetInterceptorReturnsSameObject() : void {
        self::assertSame($this->getFixtureAwareInterceptor(), $this->getFixtureAwareInterceptor());
    }

    public function testGetTestInterceptorReturnsCorrectFixtureRepository() : void {
        self::assertInstanceOf(
            XmlFileBackedFixtureRepository::class,
            $this->getFixtureAwareInterceptor()->getFixtureRepository()
        );
    }

    public function testGetTestInterceptorReturnsCorrectFixtureRepositoryPath() : void {
        $fixtureRepoReflection = new ReflectionClass(XmlFileBackedFixtureRepository::class);
        $fixtureDirProperty = $fixtureRepoReflection->getProperty('fixtureDir');
        $value = $fixtureDirProperty->getValue($this->getFixtureAwareInterceptor()->getFixtureRepository());

        self::assertSame('vfs://root', $value);
    }

    public function testGetTestInterceptorReturnsCorrectRequestMatchingStrategy() : void {
        $strategy = $this->getFixtureAwareInterceptor()->getRequestMatchingStrategy();

        self::assertInstanceOf(CompositeMatch::class, $strategy);
        self::assertSame([
            Matcher::Body->getStrategy(),
            Matcher::Headers->getStrategy(),
            Matcher::Method->getStrategy(),
            Matcher::ProtocolVersions->getStrategy(),
            Matcher::Uri->getStrategy()
        ], $strategy->getStrategies());
    }

}
