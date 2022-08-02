<?php declare(strict_types=1);

namespace Cspray\HttpClientTestInterceptor\Exception;

use Cspray\HttpClientTestInterceptor\Fixture\Schemas;

final class InvalidFixture extends Exception {

    public static function fromInvalidXmlSchema(string $file) : self {
        return new self(sprintf(
            'The file present at "%s" does not validate the %s schema.',
            $file,
            Schemas::Xml->getNamespace()
        ));
    }

}