<?php declare(strict_types=1);

namespace Cspray\HttpClientTestInterceptor\Fixture;

use Amp\Http\Client\Request;
use Amp\Http\Client\Response;
use DateTimeImmutable;
use Ramsey\Uuid\UuidInterface;

interface Fixture {

    public function getId() : UuidInterface;

    public function getCreatedAt() : DateTimeImmutable;

    public function getRequest() : Request;

    public function getResponse() : Response;

}