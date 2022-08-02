<?php

namespace Cspray\HttpClientTestInterceptor\Exception;

final class MissingFixtureAttribute extends Exception {

    public static function fromMissingHttpFixtureAttribute(string $testCase, string $test) : self {
        return new self(sprintf(
            'The test %s::%s does not have an #[HttpFixture] Attribute on either the TestCase or test method.',
            $testCase,
            $test
        ));
    }

}