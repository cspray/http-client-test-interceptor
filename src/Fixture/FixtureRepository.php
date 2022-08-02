<?php declare(strict_types=1);

namespace Cspray\HttpClientTestInterceptor\Fixture;

use Generator;

interface FixtureRepository {

    public function getFixtures() : Generator;

    public function saveFixture(Fixture $fixture) : void;

    public function removeFixture(Fixture $fixture) : void;

}