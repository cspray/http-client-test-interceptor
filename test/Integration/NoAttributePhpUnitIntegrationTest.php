<?php

namespace Cspray\HttpClientTestInterceptor\Integration;

use Cspray\HttpClientTestInterceptor\Exception\MissingFixtureAttribute;
use Cspray\HttpClientTestInterceptor\HttpFixtureTrait;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Cspray\HttpClientTestInterceptor\TestInterceptor
 * @covers \Cspray\HttpClientTestInterceptor\Exception\Exception
 * @covers \Cspray\HttpClientTestInterceptor\Exception\MissingFixtureAttribute
 * @covers \Cspray\HttpClientTestInterceptor\HttpFixtureTrait::getTestInterceptor
 */
final class NoAttributePhpUnitIntegrationTest extends TestCase {

    use HttpFixtureTrait;

    public function testExceptionThrownIfTestCaseHasNoFixtureAttribute() : void {
        self::expectException(MissingFixtureAttribute::class);
        self::expectExceptionMessage(sprintf(
            'The test %s does not have an #[HttpFixture] Attribute on either the TestCase or test method.',
            __METHOD__
        ));

        $this->getTestInterceptor();
    }

}