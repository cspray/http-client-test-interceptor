<?php

namespace Cspray\HttpClientTestInterceptor;

use Cspray\HttpClientTestInterceptor\Attribute\HttpFixture;
use Cspray\HttpClientTestInterceptor\Attribute\HttpRequestMatchers;
use Cspray\HttpClientTestInterceptor\Exception\MissingFixtureAttribute;
use Cspray\HttpClientTestInterceptor\Fixture\InMemoryFixtureCache;
use Cspray\HttpClientTestInterceptor\Fixture\XmlFileBackedFixtureRepository;
use Cspray\HttpClientTestInterceptor\RequestMatchingStrategy\CompositeMatcher;
use Cspray\HttpClientTestInterceptor\RequestMatchingStrategy\Matchers;
use ReflectionClass;

trait HttpFixtureTrait {

    private ?TestInterceptor $testInterceptor = null;

    abstract public function getName(bool $withDataSet = true) : string;

    private function getTestInterceptor() : TestInterceptor {
        $reflection = new ReflectionClass($this::class);
        $reflectionMethod = $reflection->getMethod($this->getName(false));

        $testAttributes = $reflectionMethod->getAttributes(HttpFixture::class);
        $testCaseAttributes = $reflection->getAttributes(HttpFixture::class);

        /** @var HttpFixture $httpFixture */
        if (count($testAttributes) === 0 && count($testCaseAttributes) === 0) {
            throw MissingFixtureAttribute::fromMissingHttpFixtureAttribute(
                $this::class,
                $this->getName(false)
            );
        } else if (count($testAttributes) > 0) {
            $httpFixture = $testAttributes[0]->newInstance();
        } else {
            $httpFixture = $testCaseAttributes[0]->newInstance();
        }

        $testCaseMatchersAttributes = $reflection->getAttributes(HttpRequestMatchers::class);
        $testMatchersAttributes = $reflectionMethod->getAttributes(HttpRequestMatchers::class);
        if (count($testCaseMatchersAttributes) === 0 && count($testMatchersAttributes) === 0) {
            $matcherStrategies = [
                Matchers::Body->getStrategy(),
                Matchers::Headers->getStrategy(),
                Matchers::Method->getStrategy(),
                Matchers::ProtocolVersions->getStrategy(),
                Matchers::Uri->getStrategy()
            ];
        } else {
            /** @var HttpRequestMatchers $httpRequestMatchers */
            if (count($testMatchersAttributes) > 0) {
                $httpRequestMatchers = $testMatchersAttributes[0]->newInstance();
            } else {
                $httpRequestMatchers = $testCaseMatchersAttributes[0]->newInstance();
            }
            $matcherStrategies = [];
            /** @var Matchers $matcher */
            foreach ($httpRequestMatchers->matchers as $matcher) {
                $matcherStrategies[] = $matcher->getStrategy();
            }
        }

        if ($this->testInterceptor === null) {
            $this->testInterceptor = new TestInterceptor(
                new XmlFileBackedFixtureRepository($httpFixture->path, new InMemoryFixtureCache()),
                new CompositeMatcher(...$matcherStrategies)
            );
        }

        return $this->testInterceptor;
    }

}
