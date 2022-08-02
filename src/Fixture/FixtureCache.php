<?php declare(strict_types=1);

namespace Cspray\HttpClientTestInterceptor\Fixture;

use Ramsey\Uuid\UuidInterface;

interface FixtureCache {

    public function set(Fixture $fixture) : void;

    public function has(UuidInterface $uuid) : bool;

    public function get(UuidInterface $uuid) : Fixture;

    public function remove(UuidInterface $uuid) : void;

}