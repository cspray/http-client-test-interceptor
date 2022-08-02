<?php declare(strict_types=1);

namespace Cspray\HttpClientTestInterceptor\Exception;

final class InvalidFixtureRepository extends Exception {

    public static function fromFileBackedRepoNotDirectory(string $path) : self {
        return new self(
            sprintf('The path "%s" is not a directory.', $path)
        );
    }

    public static function fromFileBackedRepoDirectoryNotWritable(string $path) : self {
        return new self(
            sprintf('The path "%s" MUST be writable.', $path)
        );
    }

}