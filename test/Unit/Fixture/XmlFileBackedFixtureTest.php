<?php declare(strict_types=1);

namespace Cspray\HttpClientTestInterceptor\Unit\Fixture;

use Cspray\HttpClientTestInterceptor\Exception\InvalidFixture;
use Cspray\HttpClientTestInterceptor\Exception\MissingFixture;
use Cspray\HttpClientTestInterceptor\Fixture\XmlFileBackedFixture;
use DateTimeImmutable;
use org\bovigo\vfs\vfsStream as VirtualFilesystem;
use org\bovigo\vfs\vfsStreamDirectory as VirtualDirectory;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Cspray\HttpClientTestInterceptor\Fixture\XmlFileBackedFixture
 * @covers \Cspray\HttpClientTestInterceptor\Fixture\Schemas
 * @covers \Cspray\HttpClientTestInterceptor\Exception\Exception
 * @covers \Cspray\HttpClientTestInterceptor\Exception\InvalidFixture
 * @covers \Cspray\HttpClientTestInterceptor\Exception\MissingFixture
 */
final class XmlFileBackedFixtureTest extends TestCase {

    private VirtualDirectory $vfs;
    private string $fixtureDir;

    protected function setUp() : void {
        $this->vfs = VirtualFilesystem::setup();
        $this->fixtureDir = dirname(__DIR__, 2) . '/fixture';
    }

    private function writeFile(string $fileName, string $fixtureName) : void {
        $fixturePath = sprintf('%s/%s', $this->fixtureDir, $fixtureName);
        VirtualFilesystem::newFile($fileName)
            ->withContent(file_get_contents($fixturePath))
            ->at($this->vfs);
    }

    public function testFileNotPresentThrowsException() : void {
        self::expectException(MissingFixture::class);
        self::expectExceptionMessage('There is no file present at "vfs://root/missing-fixture.xml".');

        new XmlFileBackedFixture('vfs://root/missing-fixture.xml');
    }

    public function testFileDoesNotValidateFixtureSchemaThrowsException() : void {
        $xml = <<<XML
<?xml version="1.0" encoding="UTF-8" ?>
<fixture xmlns="https://http-client-test-interceptor.cspray.io/chema/mock-fixture.xsd"></fixture>
XML;

        VirtualFilesystem::newFile('my-fixture.xml')
            ->withContent($xml)
            ->at($this->vfs);

        self::expectException(InvalidFixture::class);
        self::expectExceptionMessage('The file present at "vfs://root/my-fixture.xml" does not validate the https://http-client-test-interceptor.cspray.io/schema/mock-fixture.xsd schema.');

        new XmlFileBackedFixture('vfs://root/my-fixture.xml');
    }

    public function testFileWithMinimalSchemaReturnsCorrectId() : void {
        $this->writeFile('my-fixture.xml', 'minimal-fixture-post.xml');
        $fixture = new XmlFileBackedFixture('vfs://root/my-fixture.xml');

        self::assertSame('6fda31c9-4eea-4693-8236-9ff0d8d62c19', $fixture->getId()->toString());
    }

    public function testFileWithMinimalSchemaReturnsCorrectCreatedAt() : void {
        $this->writeFile('my-fixture.xml', 'minimal-fixture-post.xml');
        $fixture = new XmlFileBackedFixture('vfs://root/my-fixture.xml');

        self::assertSame((new DateTimeImmutable('2022-01-01 13:00:00'))->getTimestamp(), $fixture->getCreatedAt()->getTimestamp());
    }

    public function testFileWithMinimalSchemaReturnsCorrectRequestProtocolVersions() : void {
        $this->writeFile('fixture.xml', 'minimal-fixture-post.xml');
        $fixture = new XmlFileBackedFixture('vfs://root/fixture.xml');

        self::assertSame(['2'], $fixture->getRequest()->getProtocolVersions());
    }

    public function testFileWithMinimalSchemaReturnsCorrectRequestMethod() : void {
        $this->writeFile('my-fixture.xml', 'minimal-fixture-post.xml');
        $fixture = new XmlFileBackedFixture('vfs://root/my-fixture.xml');

        self::assertSame('POST', $fixture->getRequest()->getMethod());
    }

    public function testFileWithMinimalSchemaReturnsCorrectRequestUri() : void {
        $this->writeFile('my-fixture.xml', 'minimal-fixture-post.xml');
        $fixture = new XmlFileBackedFixture('vfs://root/my-fixture.xml');

        self::assertSame('http://example.com/', (string) $fixture->getRequest()->getUri());
    }

    public function testFileWithMinimalSchemaReturnsCorrectResponseProtocolVersion() : void {
        $this->writeFile('fixture.xml', 'minimal-fixture-post.xml');
        $fixture = new XmlFileBackedFixture('vfs://root/fixture.xml');

        self::assertSame('1.1', $fixture->getResponse()->getProtocolVersion());
    }

    public function testFileWithMinimalSchemaReturnsCorrectResponseStatusCode() : void {
        $this->writeFile('fixture.xml', 'minimal-fixture-post.xml');
        $fixture = new XmlFileBackedFixture('vfs://root/fixture.xml');

        self::assertSame(200, $fixture->getResponse()->getStatus());
    }

    public function testFileWithMinimalSchemaReturnsCorrectResponseStatusReason() : void {
        $this->writeFile('fixture.xml', 'minimal-fixture-post.xml');
        $fixture = new XmlFileBackedFixture('vfs://root/fixture.xml');

        self::assertSame('OK', $fixture->getResponse()->getReason());
    }

    public function testFileWithMinimalSchemaReturnsCorrectResponseRequest() : void {
        $this->writeFile('fixture.xml', 'minimal-fixture-post.xml');
        $fixture = new XmlFileBackedFixture('vfs://root/fixture.xml');

        self::assertSame($fixture->getRequest(), $fixture->getResponse()->getRequest());
    }

    public function testFileWithUriPortReturnsCorrectRequestUri() : void {
        $this->writeFile('another-fixture.xml', 'uri-port.xml');
        $fixture = new XmlFileBackedFixture('vfs://root/another-fixture.xml');

        self::assertSame('http://example.com:8080/', (string) $fixture->getRequest()->getUri());
    }

    public function testFileWithUriSimpleQueryCorrectRequestUri() : void {
        $this->writeFile('fixture.xml', 'uri-simple-query.xml');
        $fixture = new XmlFileBackedFixture('vfs://root/fixture.xml');

        self::assertSame('http://example.com/?foo=bar', (string) $fixture->getRequest()->getUri());
    }

    public function testFileWithUriFragmentReturnsCorrectRequestUri() : void {
        $this->writeFile('fragment.xml', 'uri-fragment.xml');
        $fixture = new XmlFileBackedFixture('vfs://root/fragment.xml');

        self::assertSame('http://example.com/#the-fragment', (string) $fixture->getRequest()->getUri());
    }

    public function testFileWithSingleRequestHeaderSingleValueReturnsCorrectRequestHeaders() : void {
        $this->writeFile('request-headers.xml', 'request-single-header-single-value.xml');
        $fixture = new XmlFileBackedFixture('vfs://root/request-headers.xml');

        self::assertSame([
            'accept' => ['text/plain']
        ], $fixture->getRequest()->getHeaders());
    }

    public function testFileWithSingleRequestHeaderMultipleValuesReturnsCorrectRequestHeaders() : void {
        $this->writeFile('request-headers.xml', 'request-single-header-multiple-values.xml');
        $fixture = new XmlFileBackedFixture('vfs://root/request-headers.xml');

        self::assertSame([
            'custom-header' => ['foo', 'bar', 'baz']
        ], $fixture->getRequest()->getHeaders());
    }

    public function testFileWithMultipleRequestHeaderValuesReturnsCorrectRequestHeaders() : void {
        $this->writeFile('request-headers.xml', 'request-multiple-headers.xml');
        $fixture = new XmlFileBackedFixture('vfs://root/request-headers.xml');

        self::assertSame([
            'accept' => ['text/plain'],
            'content-type' => ['application/json']
        ], $fixture->getRequest()->getHeaders());
    }

    public function testFileWithRequestBodyReturnsCorrectRequestBody() : void {
        $this->writeFile('body.xml', 'request-simple-body.xml');
        $fixture = new XmlFileBackedFixture('vfs://root/body.xml');

        self::assertSame('request content', $fixture->getRequest()->getBody()->createBodyStream()->read());
    }

    public function testFileWithStatusNotFoundReturnsCorrectResponseStatusCode() : void {
        $this->writeFile('response-not-found-status.xml', 'response-custom-reason.xml');
        $fixture = new XmlFileBackedFixture('vfs://root/response-not-found-status.xml');

        self::assertSame(404, $fixture->getResponse()->getStatus());
    }

    public function testFileWithStatusNotFoundReturnsCorrectResponseReason() : void {
        $this->writeFile('response-not-found-reason.xml', 'response-custom-reason.xml');
        $fixture = new XmlFileBackedFixture('vfs://root/response-not-found-reason.xml');

        self::assertSame('Not Found - My Custom Reason', $fixture->getResponse()->getReason());
    }

    public function testFileWithSingleHeaderSingleValueHasCorrectResponseHeaders() : void {
        $this->writeFile('fixture.xml', 'response-single-header-single-value.xml');
        $fixture = new XmlFileBackedFixture('vfs://root/fixture.xml');

        self::assertSame([
            'my-response-header' => ['my-value']
        ], $fixture->getResponse()->getHeaders());
    }

    public function testFileWithSingleHeaderMultipleValuesHasCorrectResponseHeaders() : void {
        $this->writeFile('fixture.xml', 'response-single-header-multiple-values.xml');
        $fixture = new XmlFileBackedFixture('vfs://root/fixture.xml');

        self::assertSame([
            'custom-header' => ['foo', 'bar', 'qux']
        ], $fixture->getResponse()->getHeaders());
    }

    public function testFileWithResponseBodyReturnsCorrectResponse() : void {
        $this->writeFile('fixture.xml', 'response-body.xml');
        $fixture = new XmlFileBackedFixture('vfs://root/fixture.xml');

        self::assertSame('<even><nested>xml</nested></even>', $fixture->getResponse()->getBody()->read());
    }

}