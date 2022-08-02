<?php

namespace Cspray\HttpClientTestInterceptor\Integration;

use Cspray\HttpClientTestInterceptor\Attribute\HttpFixture;
use Cspray\HttpClientTestInterceptor\Fixture\XmlFileBackedFixtureRepository;
use Cspray\HttpClientTestInterceptor\HttpFixtureTrait;
use Cspray\HttpClientTestInterceptor\RequestMatchingStrategy\CompositeMatcher;
use Cspray\HttpClientTestInterceptor\RequestMatchingStrategy\Matchers;
use org\bovigo\vfs\vfsStream as VirtualFilesystem;
use org\bovigo\vfs\vfsStreamDirectory as VirtualDirectory;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

#[HttpFixture('vfs://root')]
final class ClassAttributePhpUnitIntegrationTest extends TestCase {

    use HttpFixtureTrait;

    private VirtualDirectory $vfs;

    protected function setUp() : void {
        $this->vfs = VirtualFilesystem::setup();
    }

    public function testGetTestInterceptorReturnsInstance() : void {
        self::assertNotNull($this->getTestInterceptor());
    }

    public function testGetTestInterceptorReturnsCorrectFixtureRepository() : void {
        self::assertInstanceOf(
            XmlFileBackedFixtureRepository::class,
            $this->getTestInterceptor()->getFixtureRepository()
        );
    }

    public function testGetTestInterceptorReturnsCorrectFixtureRepositoryPath() : void {
        $fixtureRepoReflection = new ReflectionClass(XmlFileBackedFixtureRepository::class);
        $fixtureDirProperty = $fixtureRepoReflection->getProperty('fixtureDir');
        $value = $fixtureDirProperty->getValue($this->getTestInterceptor()->getFixtureRepository());

        self::assertSame('vfs://root', $value);
    }

    public function testGetTestInterceptorReturnsCorrectRequestMatchingStrategy() : void {
        $strategy = $this->getTestInterceptor()->getRequestMatchingStrategy();

        self::assertInstanceOf(CompositeMatcher::class, $strategy);
        self::assertSame([
            Matchers::Body->getStrategy(),
            Matchers::Headers->getStrategy(),
            Matchers::Method->getStrategy(),
            Matchers::ProtocolVersions->getStrategy(),
            Matchers::Uri->getStrategy()
        ], $strategy->getStrategies());
    }

}
