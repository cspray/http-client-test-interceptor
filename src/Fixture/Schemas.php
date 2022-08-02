<?php declare(strict_types=1);

namespace Cspray\HttpClientTestInterceptor\Fixture;

enum Schemas {
    case Xml;

    public function getNamespace() : string {
        return 'https://http-client-test-interceptor.cspray.io/schema/mock-fixture.xsd';
    }

    public function getLocalSchemaPath() : string {
        return dirname(__DIR__, 2) . '/resources/schema/mock-fixture.xsd';
    }
}