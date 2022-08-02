<?php declare(strict_types=1);

namespace Cspray\HttpClientTestInterceptor\Unit\Fixture;

use Cspray\HttpClientTestInterceptor\Exception\InvalidCacheHit;
use Cspray\HttpClientTestInterceptor\Fixture\Fixture;
use Cspray\HttpClientTestInterceptor\Fixture\InMemoryFixtureCache;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

/**
 * @covers \Cspray\HttpClientTestInterceptor\Fixture\InMemoryFixtureCache
 * @covers \Cspray\HttpClientTestInterceptor\Exception\Exception
 * @covers \Cspray\HttpClientTestInterceptor\Exception\InvalidCacheHit
 */
final class InMemoryFixtureCacheTest extends TestCase {

    public function testIsCachedEmptyReturnsFalse() : void {
        $subject = new InMemoryFixtureCache();
        self::assertFalse($subject->has(Uuid::uuid4()));
    }

    public function testGetWithoutHasThrowsException() : void {
        $id = Uuid::uuid4();
        $subject = new InMemoryFixtureCache();
        self::expectException(InvalidCacheHit::class);
        self::expectExceptionMessage(
            sprintf('Attempted to get a cached Fixture using an ID "%s" that is not present.', $id->toString())
        );

        $subject->get($id);
    }

    public function testIsCachedAfterCacheReturnsTrue() : void {
        $fixture = $this->getMockBuilder(Fixture::class)->getMock();
        $fixture->expects($this->once())
            ->method('getId')
            ->willReturn($uuid = Uuid::uuid4());

        $subject = new InMemoryFixtureCache();
        $subject->set($fixture);

        self::assertTrue($subject->has($uuid));
    }

    public function testGetAfterCache() : void {
        $fixture = $this->getMockBuilder(Fixture::class)->getMock();
        $fixture->expects($this->once())
            ->method('getId')
            ->willReturn($uuid = Uuid::uuid4());

        $subject = new InMemoryFixtureCache();
        $subject->set($fixture);

        self::assertSame($fixture, $subject->get($uuid));
    }

    public function testRemoveFromCache() : void {
        $fixture = $this->getMockBuilder(Fixture::class)->getMock();
        $fixture->expects($this->once())
            ->method('getId')
            ->willReturn($uuid = Uuid::uuid4());

        $subject = new InMemoryFixtureCache();
        $subject->set($fixture);

        self::assertTrue($subject->has($uuid));

        $subject->remove($uuid);

        self::assertFalse($subject->has($uuid));

    }

}