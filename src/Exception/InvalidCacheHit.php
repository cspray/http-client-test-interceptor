<?php declare(strict_types=1);

namespace Cspray\HttpClientTestInterceptor\Exception;

use Ramsey\Uuid\UuidInterface;

final class InvalidCacheHit extends Exception {

    public static function fromMissingUuid(UuidInterface $uuid) : self {
        return new self(
            sprintf('Attempted to get a cached Fixture using an ID "%s" that is not present.', $uuid->toString())
        );
    }

}