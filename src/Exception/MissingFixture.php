<?php declare(strict_types=1);

namespace Cspray\HttpClientTestInterceptor\Exception;

final class MissingFixture extends Exception {

    public static function missingFileBackedFixture(string $file) : self {
        return new self(sprintf('There is no file present at "%s".', $file));
    }

}