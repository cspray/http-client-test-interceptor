<?php

namespace Cspray\HttpClientTestInterceptor;

use Cspray\HttpClientTestInterceptor\Attribute\HttpFixture;
use Cspray\HttpClientTestInterceptor\Attribute\HttpRequestMatchers;
use Cspray\HttpClientTestInterceptor\Exception\MissingFixtureAttribute;
use Cspray\HttpClientTestInterceptor\Fixture\InMemoryFixtureCache;
use Cspray\HttpClientTestInterceptor\Fixture\XmlFileBackedFixtureRepository;
use Cspray\HttpClientTestInterceptor\Interceptor\FixtureAwareInterceptor;
use Cspray\HttpClientTestInterceptor\Matcher\Matcher;
use Cspray\HttpClientTestInterceptor\Matcher\Strategy\CompositeMatcherStrategy;
use ReflectionClass;

trait HttpFixtureAwareTestTrait {

    private ?FixtureAwareInterceptor $testInterceptor = null;

    abstract public function name() : string;

    private function getFixtureAwareInterceptor() : FixtureAwareInterceptor {
        $reflection = new ReflectionClass($this::class);
        $reflectionMethod = $reflection->getMethod($this->name());

        $testAttributes = $reflectionMethod->getAttributes(HttpFixture::class);
        $testCaseAttributes = $reflection->getAttributes(HttpFixture::class);

        /** @var HttpFixture $httpFixture */
        if (count($testAttributes) === 0 && count($testCaseAttributes) === 0) {
            throw MissingFixtureAttribute::fromMissingHttpFixtureAttribute(
                $this::class,
                $this->name()
            );
        }

        if (count($testAttributes) > 0) {
            $httpFixture = $testAttributes[0]->newInstance();
        } else {
            $httpFixture = $testCaseAttributes[0]->newInstance();
        }

        $testCaseMatchersAttributes = $reflection->getAttributes(HttpRequestMatchers::class);
        $testMatchersAttributes = $reflectionMethod->getAttributes(HttpRequestMatchers::class);
        if (count($testCaseMatchersAttributes) === 0 && count($testMatchersAttributes) === 0) {
            $matchers = [
                Matcher::Body,
                Matcher::Headers,
                Matcher::Method,
                Matcher::ProtocolVersions,
                Matcher::Uri
            ];
        } else {
            /** @var HttpRequestMatchers $httpRequestMatchers */
            if (count($testMatchersAttributes) > 0) {
                $httpRequestMatchers = $testMatchersAttributes[0]->newInstance();
            } else {
                $httpRequestMatchers = $testCaseMatchersAttributes[0]->newInstance();
            }

            $matchers = $httpRequestMatchers->matchers;
        }

        if ($this->testInterceptor === null) {
            $this->testInterceptor = new FixtureAwareInterceptor(
                new XmlFileBackedFixtureRepository($httpFixture->path, new InMemoryFixtureCache()),
                CompositeMatcherStrategy::fromMatchers(...$matchers)
            );
        }

        return $this->testInterceptor;
    }

}
