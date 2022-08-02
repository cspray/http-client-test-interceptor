<?php declare(strict_types=1);

namespace Cspray\HttpClientTestInterceptor\Fixture;

use Cspray\HttpClientTestInterceptor\Exception\InvalidCacheHit;
use Ramsey\Uuid\UuidInterface;

final class InMemoryFixtureCache implements FixtureCache {

    private array $cache = [];

    public function set(Fixture $fixture) : void {
        $this->cache[$fixture->getId()->toString()] = $fixture;
    }

    public function has(UuidInterface $uuid) : bool {
        return array_key_exists($uuid->toString(), $this->cache);
    }

    public function get(UuidInterface $uuid) : Fixture {
        if (!$this->has($uuid)) {
            throw InvalidCacheHit::fromMissingUuid($uuid);
        }

        return $this->cache[$uuid->toString()];
    }

    public function remove(UuidInterface $uuid) : void {
        unset($this->cache[$uuid->toString()]);
    }
}