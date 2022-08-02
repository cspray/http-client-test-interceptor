<?php declare(strict_types=1);

namespace Cspray\HttpClientTestInterceptor\Helper;

use Amp\ByteStream\ReadableBuffer;
use Amp\Http\Client\Request;
use Amp\Http\Client\Response;
use Cspray\HttpClientTestInterceptor\Fixture\Fixture;
use DateTimeImmutable;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

final class StubFixture implements Fixture {

    private function __construct(
        private readonly UuidInterface $id,
        private readonly DateTimeImmutable $createdAt,
        private readonly Request $request,
        private readonly Response $response
    ) {}

    public static function fromAllParams(
        UuidInterface $id,
        DateTimeImmutable $createdAt,
        Request $request,
        Response $response
    ) : self {
        return new self($id, $createdAt, $request, $response);
    }

    public static function fromRequestAndResponse(Request $request, Response $response) : self {
        return new self(
            Uuid::uuid4(),
            new DateTimeImmutable(),
            $request,
            $response
        );
    }

    public static function fromRequest(Request $request) : self {
        $response = new Response('1.0', 200, 'OK', [], new ReadableBuffer(), $request);
        return self::fromRequestAndResponse($request, $response);
    }

    public static function fromRequestFactory(callable $requestFactory) : self {
        return self::fromRequest($requestFactory());
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