<?php declare(strict_types=1);

namespace Cspray\HttpClientTestInterceptor\Fixture;

use Amp\Http\Client\Request;
use Amp\Http\Client\Response;
use DateTimeImmutable;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

final class InFlightFixture implements Fixture {

    private readonly UuidInterface $id;

    public function __construct(
        private readonly Request $request,
        private readonly Response $response,
        private readonly DateTimeImmutable $createdAt
    ) {
        $this->id = Uuid::uuid4();
    }

    public function getId() : UuidInterface {
        return $this->id;
    }

    public function getCreatedAt() : DateTimeImmutable {
        return $this->createdAt;
    }

    public function getRequest() : Request {
        return $this->request;
    }

    public function getResponse() : Response {
        return $this->response;
    }
}