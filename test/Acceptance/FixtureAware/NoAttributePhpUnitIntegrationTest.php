<?php

namespace Cspray\HttpClientTestInterceptor\Acceptance\FixtureAware;

use Cspray\HttpClientTestInterceptor\Exception\MissingFixtureAttribute;
use Cspray\HttpClientTestInterceptor\HttpFixtureAwareTestTrait;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Cspray\HttpClientTestInterceptor\FixtureAwareInterceptor
 * @covers \Cspray\HttpClientTestInterceptor\Exception\Exception
 * @covers \Cspray\HttpClientTestInterceptor\Exception\MissingFixtureAttribute
 * @covers \Cspray\HttpClientTestInterceptor\HttpFixtureAwareTestTrait::getFixtureAwareInterceptor
 */
final class NoAttributePhpUnitIntegrationTest extends TestCase {

    use HttpFixtureAwareTestTrait;

    public function testExceptionThrownIfTestCaseHasNoFixtureAttribute() : void {
        self::expectException(MissingFixtureAttribute::class);
        self::expectExceptionMessage(sprintf(
            'The test %s does not have an #[HttpFixture] Attribute on either the TestCase or test method.',
            __METHOD__
        ));

        $this->getFixtureAwareInterceptor();
    }

}