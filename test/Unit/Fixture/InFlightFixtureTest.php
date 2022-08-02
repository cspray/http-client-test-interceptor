<?php declare(strict_types=1);

namespace Cspray\HttpClientTestInterceptor\Unit\Fixture;

use Amp\ByteStream\ReadableBuffer;
use Amp\Http\Client\Request;
use Amp\Http\Client\Response;
use Cspray\HttpClientTestInterceptor\Fixture\InFlightFixture;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Rfc4122\FieldsInterface;

/**
 * @covers \Cspray\HttpClientTestInterceptor\Fixture\InFlightFixture
 */
final class InFlightFixtureTest extends TestCase {

    public function testGetIdReturnsUuidV4() : void {
        $request = new Request('http://example.com');
        $response = new Response('1.0', 200, 'OK', [], new ReadableBuffer(), $request);
        $createdAt = new \DateTimeImmutable();
        $subject = new InFlightFixture($request, $response, $createdAt);

        $fields = $subject->getId()->getFields();
        self::assertInstanceOf(FieldsInterface::class, $fields);
        self::assertSame(4, $fields->getVersion());
        self::assertSame($createdAt, $subject->getCreatedAt());
        self::assertSame($request, $subject->getRequest());
        self::assertSame($response, $subject->getResponse());
    }

}