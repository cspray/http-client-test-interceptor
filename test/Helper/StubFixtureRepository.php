<?php declare(strict_types=1);

namespace Cspray\HttpClientTestInterceptor\Helper;

use Cspray\HttpClientTestInterceptor\Fixture\Fixture;
use Cspray\HttpClientTestInterceptor\Fixture\FixtureRepository;
use Generator;

final class StubFixtureRepository implements FixtureRepository {

    private array $fixtures = [];

    public function __construct(
        Fixture... $fixtures
    ) {
        foreach ($fixtures as $fixture) {
            $this->saveFixture($fixture);
        }
    }

    public function getFixtures() : Generator {
        yield from array_values($this->fixtures);
    }

    public function saveFixture(Fixture $fixture) : void {
        $this->fixtures[$fixture->getId()->toString()] = $fixture;
    }

    public function removeFixture(Fixture $fixture) : void {
        unset($this->fixtures[$fixture->getId()->toString()]);
    }
}