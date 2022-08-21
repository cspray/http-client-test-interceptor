<?php declare(strict_types=1);

namespace Cspray\HttpClientTestInterceptor\Unit\Fixture;

use Amp\ByteStream\ReadableBuffer;
use Amp\Http\Client\Request;
use Amp\Http\Client\Response;
use Cspray\HttpClientTestInterceptor\Exception\InvalidFixtureRepository;
use Cspray\HttpClientTestInterceptor\Fixture\Fixture;
use Cspray\HttpClientTestInterceptor\Fixture\InMemoryFixtureCache;
use Cspray\HttpClientTestInterceptor\Fixture\XmlFileBackedFixture;
use Cspray\HttpClientTestInterceptor\Fixture\XmlFileBackedFixtureRepository;
use Cspray\HttpClientTestInterceptor\Helper\StubFixture;
use DateTimeImmutable;
use org\bovigo\vfs\vfsStream as VirtualFilesystem;
use org\bovigo\vfs\vfsStreamDirectory as VirtualDirectory;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

/**
 * @covers \Cspray\HttpClientTestInterceptor\Fixture\XmlFileBackedFixtureRepository
 * @covers \Cspray\HttpClientTestInterceptor\Fixture\XmlFileBackedFixture
 * @covers \Cspray\HttpClientTestInterceptor\Fixture\Schemas
 * @covers \Cspray\HttpClientTestInterceptor\Fixture\InMemoryFixtureCache
 * @covers \Cspray\HttpClientTestInterceptor\Exception\Exception
 * @covers \Cspray\HttpClientTestInterceptor\Exception\InvalidFixtureRepository
 */
final class XmlFileBackedFixtureRepositoryTest extends TestCase {

    private VirtualDirectory $vfs;
    private string $fixtureDir;

    protected function setUp() : void {
        $this->vfs = VirtualFilesystem::setup();
        $this->fixtureDir = dirname(__DIR__, 2) . '/fixture';
    }

    public function testPathNotDirectoryThrowsException() : void {
        self::expectException(InvalidFixtureRepository::class);
        self::expectExceptionMessage('The path "vfs://root/not-found" is not a directory.');

        new XmlFileBackedFixtureRepository(
            'vfs://root/not-found',
            new InMemoryFixtureCache()
        );
    }

    public function testPathNotWritableThrowsException() : void {
        VirtualFilesystem::newDirectory('not-writable', 0444)->at($this->vfs);

        self::expectException(InvalidFixtureRepository::class);
        self::expectExceptionMessage('The path "vfs://root/not-writable" MUST be writable.');

        new XmlFileBackedFixtureRepository(
            'vfs://root/not-writable',
            new InMemoryFixtureCache()
        );
    }

    public function testSaveSimpleRequestResponseFixtureAddsToDirectory() : void {
        $id = Uuid::uuid4();
        $createdAt = new DateTimeImmutable('2022-01-01 13:00:00');
        $request = new Request('http://example.com');
        $response = new Response(
            '1.1',
            200,
            'OK',
            [],
            new ReadableBuffer(),
            $request
        );

        $fixture = StubFixture::fromAllParams($id, $createdAt, $request, $response);
        $subject = new XmlFileBackedFixtureRepository(
            'vfs://root',
            new InMemoryFixtureCache()
        );

        $subject->saveFixture($fixture);

        self::assertFileExists('vfs://root/' . $id->toString());

        $expected = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<fixture xmlns="https://http-client-test-interceptor.cspray.io/schema/mock-fixture.xsd">
  <id>{$id->toString()}</id>
  <createdAt>2022-01-01T13:00:00+00:00</createdAt>
  <request>
    <protocolVersions>
      <protocolVersion>1.1</protocolVersion>
      <protocolVersion>2</protocolVersion>
    </protocolVersions>
    <method>GET</method>
    <uri>
      <scheme>http</scheme>
      <host>example.com</host>
      <port/>
      <path/>
      <queryParameters/>
      <fragment/>
    </uri>
    <headers/>
    <body/>
  </request>
  <response>
    <protocolVersion>1.1</protocolVersion>
    <status>200</status>
    <statusReason>OK</statusReason>
    <headers/>
    <body/>
  </response>
</fixture>

XML;

        self::assertStringEqualsFile('vfs://root/' . $id->toString(), $expected);
    }

    public function testSaveSimpleRequestResponseFixtureAddedToCache() : void {
        $fixture = StubFixture::fromRequest(new Request('https://example.com'));
        $subject = new XmlFileBackedFixtureRepository(
            'vfs://root',
            $cache = new InMemoryFixtureCache()
        );

        $subject->saveFixture($fixture);

        self::assertTrue($cache->has($fixture->getId()));
    }

    public function testGetFixturesSinglePresentHasCorrectFixtures() : void {
        $id = Uuid::fromString('6fda31c9-4eea-4693-8236-9ff0d8d62c19');
        VirtualFilesystem::newFile($id->toString())
            ->withContent(file_get_contents(sprintf('%s/%s', $this->fixtureDir, 'minimal-fixture-post.xml')))
            ->at($this->vfs);

        $fixtures = iterator_to_array(
            (new XmlFileBackedFixtureRepository('vfs://root', new InMemoryFixtureCache()))->getFixtures()
        );

        self::assertCount(1, $fixtures);
        self::assertInstanceOf(XmlFileBackedFixture::class, $fixtures[0]);
        self::assertSame($id->toString(), $fixtures[0]->getId()->toString());
    }

    public function testGetFixturesDoesNotRecurse() : void {
        $nestedDir = VirtualFilesystem::newDirectory('nested')->at($this->vfs);
        $id = Uuid::fromString('6fda31c9-4eea-4693-8236-9ff0d8d62c19');
        VirtualFilesystem::newFile($id->toString())
            ->withContent(file_get_contents(sprintf('%s/%s', $this->fixtureDir, 'minimal-fixture-post.xml')))
            ->at($nestedDir);

        $fixtures = iterator_to_array(
            (new XmlFileBackedFixtureRepository('vfs://root', new InMemoryFixtureCache()))->getFixtures()
        );

        self::assertCount(0, $fixtures);
    }

    public function testGetFixturesAddsToCache() : void {
        $id = Uuid::fromString('6fda31c9-4eea-4693-8236-9ff0d8d62c19');
        VirtualFilesystem::newFile($id->toString())
            ->withContent(file_get_contents(sprintf('%s/%s', $this->fixtureDir, 'minimal-fixture-post.xml')))
            ->at($this->vfs);

        $fixtures = iterator_to_array(
            (new XmlFileBackedFixtureRepository('vfs://root', $cache = new InMemoryFixtureCache()))->getFixtures()
        );

        self::assertTrue($cache->has($id));
        self::assertSame($fixtures[0], $cache->get($id));
    }

    public function testGetFixturesDoesNotReadFromFilesystemIfInCache() : void {
        $id = Uuid::fromString('6fda31c9-4eea-4693-8236-9ff0d8d62c19');
        VirtualFilesystem::newFile($id->toString())
            ->withContent('intentionally invalid xml')
            ->at($this->vfs);

        $cache = new InMemoryFixtureCache();
        $cache->set($fixture = new XmlFileBackedFixture(sprintf('%s/%s', $this->fixtureDir, 'minimal-fixture-post.xml')));

        $fixtures = iterator_to_array(
            (new XmlFileBackedFixtureRepository('vfs://root', $cache))->getFixtures()
        );

        self::assertCount(1, $fixtures);
        self::assertSame($fixture, $fixtures[0]);
    }

    public function testRemoveFixtureRemovesFromCache() : void {
        $cache = new InMemoryFixtureCache();
        $cache->set($fixture = new XmlFileBackedFixture(sprintf('%s/%s', $this->fixtureDir, 'minimal-fixture-post.xml')));

        self::assertTrue($cache->has($fixture->getId()));

        $subject = new XmlFileBackedFixtureRepository('vfs://root', $cache);
        $subject->removeFixture($fixture);

        self::assertFalse($cache->has($fixture->getId()));
    }

    public function testRemoveFixtureRemovesFile() : void {
        $id = Uuid::fromString('6fda31c9-4eea-4693-8236-9ff0d8d62c19');
        VirtualFilesystem::newFile($id->toString())
            ->withContent(file_get_contents(sprintf('%s/%s', $this->fixtureDir, 'minimal-fixture-post.xml')))
            ->at($this->vfs);

        self::assertFileExists('vfs://root/' . $id->toString());

        $subject = new XmlFileBackedFixtureRepository('vfs://root', new InMemoryFixtureCache());
        $subject->removeFixture(new XmlFileBackedFixture('vfs://root/' . $id->toString()));

        self::assertFileDoesNotExist('vfs://root/' . $id->toString());
    }

    public function testSaveFixtureWithUriPathSpecified() : void {
        $id = Uuid::uuid4();
        $createdAt = new DateTimeImmutable('2022-01-01 13:00:00');
        $request = new Request('http://example.com/my/path/to/resource/');
        $response = new Response(
            '1.1',
            200,
            'OK',
            [],
            new ReadableBuffer(),
            $request
        );

        $fixture = StubFixture::fromAllParams($id, $createdAt, $request, $response);
        $subject = new XmlFileBackedFixtureRepository(
            'vfs://root',
            new InMemoryFixtureCache()
        );

        $subject->saveFixture($fixture);

        self::assertFileExists('vfs://root/' . $id->toString());

        $expected = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<fixture xmlns="https://http-client-test-interceptor.cspray.io/schema/mock-fixture.xsd">
  <id>{$id->toString()}</id>
  <createdAt>2022-01-01T13:00:00+00:00</createdAt>
  <request>
    <protocolVersions>
      <protocolVersion>1.1</protocolVersion>
      <protocolVersion>2</protocolVersion>
    </protocolVersions>
    <method>GET</method>
    <uri>
      <scheme>http</scheme>
      <host>example.com</host>
      <port/>
      <path>/my/path/to/resource/</path>
      <queryParameters/>
      <fragment/>
    </uri>
    <headers/>
    <body/>
  </request>
  <response>
    <protocolVersion>1.1</protocolVersion>
    <status>200</status>
    <statusReason>OK</statusReason>
    <headers/>
    <body/>
  </response>
</fixture>

XML;

        self::assertStringEqualsFile('vfs://root/' . $id->toString(), $expected);
    }

    public function testSaveFixtureWithUriPortSpecified() : void {
        $id = Uuid::uuid4();
        $createdAt = new DateTimeImmutable('2022-01-01 13:00:00');
        $request = new Request('http://example.com:4200');
        $response = new Response(
            '1.1',
            200,
            'OK',
            [],
            new ReadableBuffer(),
            $request
        );

        $fixture = StubFixture::fromAllParams($id, $createdAt, $request, $response);
        $subject = new XmlFileBackedFixtureRepository(
            'vfs://root',
            new InMemoryFixtureCache()
        );

        $subject->saveFixture($fixture);

        self::assertFileExists('vfs://root/' . $id->toString());

        $expected = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<fixture xmlns="https://http-client-test-interceptor.cspray.io/schema/mock-fixture.xsd">
  <id>{$id->toString()}</id>
  <createdAt>2022-01-01T13:00:00+00:00</createdAt>
  <request>
    <protocolVersions>
      <protocolVersion>1.1</protocolVersion>
      <protocolVersion>2</protocolVersion>
    </protocolVersions>
    <method>GET</method>
    <uri>
      <scheme>http</scheme>
      <host>example.com</host>
      <port>4200</port>
      <path/>
      <queryParameters/>
      <fragment/>
    </uri>
    <headers/>
    <body/>
  </request>
  <response>
    <protocolVersion>1.1</protocolVersion>
    <status>200</status>
    <statusReason>OK</statusReason>
    <headers/>
    <body/>
  </response>
</fixture>

XML;

        self::assertStringEqualsFile('vfs://root/' . $id->toString(), $expected);
    }

    public function testSaveFixtureWithUriQueryParametersSpecified() : void {
        $id = Uuid::uuid4();
        $createdAt = new DateTimeImmutable('2022-01-01 13:00:00');
        $request = new Request('http://example.com?foo[]=bar&foo[]=baz&foo[]=qux');
        $response = new Response(
            '1.1',
            200,
            'OK',
            [],
            new ReadableBuffer(),
            $request
        );

        $fixture = StubFixture::fromAllParams($id, $createdAt, $request, $response);
        $subject = new XmlFileBackedFixtureRepository(
            'vfs://root',
            new InMemoryFixtureCache()
        );

        $subject->saveFixture($fixture);

        self::assertFileExists('vfs://root/' . $id->toString());

        $expected = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<fixture xmlns="https://http-client-test-interceptor.cspray.io/schema/mock-fixture.xsd">
  <id>{$id->toString()}</id>
  <createdAt>2022-01-01T13:00:00+00:00</createdAt>
  <request>
    <protocolVersions>
      <protocolVersion>1.1</protocolVersion>
      <protocolVersion>2</protocolVersion>
    </protocolVersions>
    <method>GET</method>
    <uri>
      <scheme>http</scheme>
      <host>example.com</host>
      <port/>
      <path/>
      <queryParameters>
        <queryParameter>
          <name>foo[]</name>
          <value>bar</value>
        </queryParameter>
        <queryParameter>
          <name>foo[]</name>
          <value>baz</value>
        </queryParameter>
        <queryParameter>
          <name>foo[]</name>
          <value>qux</value>
        </queryParameter>
      </queryParameters>
      <fragment/>
    </uri>
    <headers/>
    <body/>
  </request>
  <response>
    <protocolVersion>1.1</protocolVersion>
    <status>200</status>
    <statusReason>OK</statusReason>
    <headers/>
    <body/>
  </response>
</fixture>

XML;

        self::assertStringEqualsFile('vfs://root/' . $id->toString(), $expected);
    }

    public function testSaveFixtureWithUserInfoSpecified() : void {
        $id = Uuid::uuid4();
        $createdAt = new DateTimeImmutable('2022-01-01 13:00:00');
        $request = new Request('http://my-user:my-pass@example.com');
        $response = new Response(
            '1.1',
            200,
            'OK',
            [],
            new ReadableBuffer(),
            $request
        );

        $fixture = StubFixture::fromAllParams($id, $createdAt, $request, $response);
        $subject = new XmlFileBackedFixtureRepository(
            'vfs://root',
            new InMemoryFixtureCache()
        );

        $subject->saveFixture($fixture);

        self::assertFileExists('vfs://root/' . $id->toString());

        $expected = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<fixture xmlns="https://http-client-test-interceptor.cspray.io/schema/mock-fixture.xsd">
  <id>{$id->toString()}</id>
  <createdAt>2022-01-01T13:00:00+00:00</createdAt>
  <request>
    <protocolVersions>
      <protocolVersion>1.1</protocolVersion>
      <protocolVersion>2</protocolVersion>
    </protocolVersions>
    <method>GET</method>
    <uri>
      <scheme>http</scheme>
      <host>example.com</host>
      <port/>
      <path/>
      <queryParameters/>
      <fragment/>
    </uri>
    <headers/>
    <body/>
  </request>
  <response>
    <protocolVersion>1.1</protocolVersion>
    <status>200</status>
    <statusReason>OK</statusReason>
    <headers/>
    <body/>
  </response>
</fixture>

XML;

        self::assertStringEqualsFile('vfs://root/' . $id->toString(), $expected);
    }

    public function testSaveFixtureWithUserInfoOnlyUserSpecified() : void {
        $id = Uuid::uuid4();
        $createdAt = new DateTimeImmutable('2022-01-01 13:00:00');
        $request = new Request('http://my-user:@example.com');
        $response = new Response(
            '1.1',
            200,
            'OK',
            [],
            new ReadableBuffer(),
            $request
        );

        $fixture = StubFixture::fromAllParams($id, $createdAt, $request, $response);
        $subject = new XmlFileBackedFixtureRepository(
            'vfs://root',
            new InMemoryFixtureCache()
        );

        $subject->saveFixture($fixture);

        self::assertFileExists('vfs://root/' . $id->toString());

        $expected = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<fixture xmlns="https://http-client-test-interceptor.cspray.io/schema/mock-fixture.xsd">
  <id>{$id->toString()}</id>
  <createdAt>2022-01-01T13:00:00+00:00</createdAt>
  <request>
    <protocolVersions>
      <protocolVersion>1.1</protocolVersion>
      <protocolVersion>2</protocolVersion>
    </protocolVersions>
    <method>GET</method>
    <uri>
      <scheme>http</scheme>
      <host>example.com</host>
      <port/>
      <path/>
      <queryParameters/>
      <fragment/>
    </uri>
    <headers/>
    <body/>
  </request>
  <response>
    <protocolVersion>1.1</protocolVersion>
    <status>200</status>
    <statusReason>OK</statusReason>
    <headers/>
    <body/>
  </response>
</fixture>

XML;

        self::assertStringEqualsFile('vfs://root/' . $id->toString(), $expected);
    }

    public function testSaveFixtureWithFragmentSpecified() : void {
        $id = Uuid::uuid4();
        $createdAt = new DateTimeImmutable('2022-01-01 13:00:00');
        $request = new Request('http://example.com#my-frag');
        $response = new Response(
            '1.1',
            200,
            'OK',
            [],
            new ReadableBuffer(),
            $request
        );

        $fixture = StubFixture::fromAllParams($id, $createdAt, $request, $response);
        $subject = new XmlFileBackedFixtureRepository(
            'vfs://root',
            new InMemoryFixtureCache()
        );

        $subject->saveFixture($fixture);

        self::assertFileExists('vfs://root/' . $id->toString());

        $expected = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<fixture xmlns="https://http-client-test-interceptor.cspray.io/schema/mock-fixture.xsd">
  <id>{$id->toString()}</id>
  <createdAt>2022-01-01T13:00:00+00:00</createdAt>
  <request>
    <protocolVersions>
      <protocolVersion>1.1</protocolVersion>
      <protocolVersion>2</protocolVersion>
    </protocolVersions>
    <method>GET</method>
    <uri>
      <scheme>http</scheme>
      <host>example.com</host>
      <port/>
      <path/>
      <queryParameters/>
      <fragment>my-frag</fragment>
    </uri>
    <headers/>
    <body/>
  </request>
  <response>
    <protocolVersion>1.1</protocolVersion>
    <status>200</status>
    <statusReason>OK</statusReason>
    <headers/>
    <body/>
  </response>
</fixture>

XML;

        self::assertStringEqualsFile('vfs://root/' . $id->toString(), $expected);
    }

    public function testSaveFixtureWithRequestSingleHeaderSingleValueSpecified() : void {
        $id = Uuid::uuid4();
        $createdAt = new DateTimeImmutable('2022-01-01 13:00:00');
        $request = new Request('http://example.com');
        $request->setHeader('Accept', 'text/plain');
        $response = new Response(
            '1.1',
            200,
            'OK',
            [],
            new ReadableBuffer(),
            $request
        );

        $fixture = StubFixture::fromAllParams($id, $createdAt, $request, $response);
        $subject = new XmlFileBackedFixtureRepository(
            'vfs://root',
            new InMemoryFixtureCache()
        );

        $subject->saveFixture($fixture);

        self::assertFileExists('vfs://root/' . $id->toString());

        $expected = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<fixture xmlns="https://http-client-test-interceptor.cspray.io/schema/mock-fixture.xsd">
  <id>{$id->toString()}</id>
  <createdAt>2022-01-01T13:00:00+00:00</createdAt>
  <request>
    <protocolVersions>
      <protocolVersion>1.1</protocolVersion>
      <protocolVersion>2</protocolVersion>
    </protocolVersions>
    <method>GET</method>
    <uri>
      <scheme>http</scheme>
      <host>example.com</host>
      <port/>
      <path/>
      <queryParameters/>
      <fragment/>
    </uri>
    <headers>
      <header>
        <name>accept</name>
        <values>
          <value>text/plain</value>
        </values>
      </header>
    </headers>
    <body/>
  </request>
  <response>
    <protocolVersion>1.1</protocolVersion>
    <status>200</status>
    <statusReason>OK</statusReason>
    <headers/>
    <body/>
  </response>
</fixture>

XML;

        self::assertStringEqualsFile('vfs://root/' . $id->toString(), $expected);
    }

    public function testSaveFixtureWithRequestSingleHeaderMultipleValueSpecified() : void {
        $id = Uuid::uuid4();
        $createdAt = new DateTimeImmutable('2022-01-01 13:00:00');
        $request = new Request('http://example.com');
        $request->setHeader('My-Custom-Header', ['one', 'two', 'three']);
        $response = new Response(
            '1.1',
            200,
            'OK',
            [],
            new ReadableBuffer(),
            $request
        );

        $fixture = StubFixture::fromAllParams($id, $createdAt, $request, $response);
        $subject = new XmlFileBackedFixtureRepository(
            'vfs://root',
            new InMemoryFixtureCache()
        );

        $subject->saveFixture($fixture);

        self::assertFileExists('vfs://root/' . $id->toString());

        $expected = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<fixture xmlns="https://http-client-test-interceptor.cspray.io/schema/mock-fixture.xsd">
  <id>{$id->toString()}</id>
  <createdAt>2022-01-01T13:00:00+00:00</createdAt>
  <request>
    <protocolVersions>
      <protocolVersion>1.1</protocolVersion>
      <protocolVersion>2</protocolVersion>
    </protocolVersions>
    <method>GET</method>
    <uri>
      <scheme>http</scheme>
      <host>example.com</host>
      <port/>
      <path/>
      <queryParameters/>
      <fragment/>
    </uri>
    <headers>
      <header>
        <name>my-custom-header</name>
        <values>
          <value>one</value>
          <value>two</value>
          <value>three</value>
        </values>
      </header>
    </headers>
    <body/>
  </request>
  <response>
    <protocolVersion>1.1</protocolVersion>
    <status>200</status>
    <statusReason>OK</statusReason>
    <headers/>
    <body/>
  </response>
</fixture>

XML;

        self::assertStringEqualsFile('vfs://root/' . $id->toString(), $expected);
    }

    public function testSaveFixtureWithRequestBodySpecified() : void {
        $id = Uuid::uuid4();
        $createdAt = new DateTimeImmutable('2022-01-01 13:00:00');
        $request = new Request('http://example.com', body: 'My interpretation of the situation');
        $response = new Response(
            '1.1',
            200,
            'OK',
            [],
            new ReadableBuffer(),
            $request
        );

        $fixture = StubFixture::fromAllParams($id, $createdAt, $request, $response);
        $subject = new XmlFileBackedFixtureRepository(
            'vfs://root',
            new InMemoryFixtureCache()
        );

        $subject->saveFixture($fixture);

        self::assertFileExists('vfs://root/' . $id->toString());

        $expected = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<fixture xmlns="https://http-client-test-interceptor.cspray.io/schema/mock-fixture.xsd">
  <id>{$id->toString()}</id>
  <createdAt>2022-01-01T13:00:00+00:00</createdAt>
  <request>
    <protocolVersions>
      <protocolVersion>1.1</protocolVersion>
      <protocolVersion>2</protocolVersion>
    </protocolVersions>
    <method>GET</method>
    <uri>
      <scheme>http</scheme>
      <host>example.com</host>
      <port/>
      <path/>
      <queryParameters/>
      <fragment/>
    </uri>
    <headers/>
    <body><![CDATA[My interpretation of the situation]]></body>
  </request>
  <response>
    <protocolVersion>1.1</protocolVersion>
    <status>200</status>
    <statusReason>OK</statusReason>
    <headers/>
    <body/>
  </response>
</fixture>

XML;

        self::assertStringEqualsFile('vfs://root/' . $id->toString(), $expected);
    }

    public function testSaveFixtureWithResponseProtocolVersion() : void {
        $id = Uuid::uuid4();
        $createdAt = new DateTimeImmutable('2022-01-01 13:00:00');
        $request = new Request('http://example.com');
        $response = new Response(
            '2',
            200,
            'OK',
            [],
            new ReadableBuffer(),
            $request
        );

        $fixture = StubFixture::fromAllParams($id, $createdAt, $request, $response);
        $subject = new XmlFileBackedFixtureRepository(
            'vfs://root',
            new InMemoryFixtureCache()
        );

        $subject->saveFixture($fixture);

        self::assertFileExists('vfs://root/' . $id->toString());

        $expected = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<fixture xmlns="https://http-client-test-interceptor.cspray.io/schema/mock-fixture.xsd">
  <id>{$id->toString()}</id>
  <createdAt>2022-01-01T13:00:00+00:00</createdAt>
  <request>
    <protocolVersions>
      <protocolVersion>1.1</protocolVersion>
      <protocolVersion>2</protocolVersion>
    </protocolVersions>
    <method>GET</method>
    <uri>
      <scheme>http</scheme>
      <host>example.com</host>
      <port/>
      <path/>
      <queryParameters/>
      <fragment/>
    </uri>
    <headers/>
    <body/>
  </request>
  <response>
    <protocolVersion>2</protocolVersion>
    <status>200</status>
    <statusReason>OK</statusReason>
    <headers/>
    <body/>
  </response>
</fixture>

XML;

        self::assertStringEqualsFile('vfs://root/' . $id->toString(), $expected);
    }

    public function testSaveFixtureWithResponseStatus() : void {
        $id = Uuid::uuid4();
        $createdAt = new DateTimeImmutable('2022-01-01 13:00:00');
        $request = new Request('http://example.com');
        $response = new Response(
            '1.0',
            404,
            'Not Found',
            [],
            new ReadableBuffer(),
            $request
        );

        $fixture = StubFixture::fromAllParams($id, $createdAt, $request, $response);
        $subject = new XmlFileBackedFixtureRepository(
            'vfs://root',
            new InMemoryFixtureCache()
        );

        $subject->saveFixture($fixture);

        self::assertFileExists('vfs://root/' . $id->toString());

        $expected = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<fixture xmlns="https://http-client-test-interceptor.cspray.io/schema/mock-fixture.xsd">
  <id>{$id->toString()}</id>
  <createdAt>2022-01-01T13:00:00+00:00</createdAt>
  <request>
    <protocolVersions>
      <protocolVersion>1.1</protocolVersion>
      <protocolVersion>2</protocolVersion>
    </protocolVersions>
    <method>GET</method>
    <uri>
      <scheme>http</scheme>
      <host>example.com</host>
      <port/>
      <path/>
      <queryParameters/>
      <fragment/>
    </uri>
    <headers/>
    <body/>
  </request>
  <response>
    <protocolVersion>1.0</protocolVersion>
    <status>404</status>
    <statusReason>Not Found</statusReason>
    <headers/>
    <body/>
  </response>
</fixture>

XML;

        self::assertStringEqualsFile('vfs://root/' . $id->toString(), $expected);
    }

    public function testSaveFixtureWithResponseHeaders() : void {
        $id = Uuid::uuid4();
        $createdAt = new DateTimeImmutable('2022-01-01 13:00:00');
        $request = new Request('http://example.com');
        $response = new Response(
            '1.0',
            404,
            'Not Found',
            [
                'Content-Type' => 'application/json',
                'Custom-Header' => ['foo', 'bar', 'baz']
            ],
            new ReadableBuffer(),
            $request
        );

        $fixture = StubFixture::fromAllParams($id, $createdAt, $request, $response);
        $subject = new XmlFileBackedFixtureRepository(
            'vfs://root',
            new InMemoryFixtureCache()
        );

        $subject->saveFixture($fixture);

        self::assertFileExists('vfs://root/' . $id->toString());

        $expected = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<fixture xmlns="https://http-client-test-interceptor.cspray.io/schema/mock-fixture.xsd">
  <id>{$id->toString()}</id>
  <createdAt>2022-01-01T13:00:00+00:00</createdAt>
  <request>
    <protocolVersions>
      <protocolVersion>1.1</protocolVersion>
      <protocolVersion>2</protocolVersion>
    </protocolVersions>
    <method>GET</method>
    <uri>
      <scheme>http</scheme>
      <host>example.com</host>
      <port/>
      <path/>
      <queryParameters/>
      <fragment/>
    </uri>
    <headers/>
    <body/>
  </request>
  <response>
    <protocolVersion>1.0</protocolVersion>
    <status>404</status>
    <statusReason>Not Found</statusReason>
    <headers>
      <header>
        <name>content-type</name>
        <values>
          <value>application/json</value>
        </values>
      </header>
      <header>
        <name>custom-header</name>
        <values>
          <value>foo</value>
          <value>bar</value>
          <value>baz</value>
        </values>
      </header>
    </headers>
    <body/>
  </response>
</fixture>

XML;

        self::assertStringEqualsFile('vfs://root/' . $id->toString(), $expected);
    }

    public function testSaveFixtureWithResponseBody() : void {
        $id = Uuid::uuid4();
        $createdAt = new DateTimeImmutable('2022-01-01 13:00:00');
        $request = new Request('http://example.com');
        $response = new Response(
            '1.0',
            404,
            'Not Found',
            [],
            new ReadableBuffer('My Body Content'),
            $request
        );

        $fixture = StubFixture::fromAllParams($id, $createdAt, $request, $response);
        $subject = new XmlFileBackedFixtureRepository(
            'vfs://root',
            new InMemoryFixtureCache()
        );

        $subject->saveFixture($fixture);

        self::assertFileExists('vfs://root/' . $id->toString());
        $content = base64_encode('My Body Content');
        $expected = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<fixture xmlns="https://http-client-test-interceptor.cspray.io/schema/mock-fixture.xsd">
  <id>{$id->toString()}</id>
  <createdAt>2022-01-01T13:00:00+00:00</createdAt>
  <request>
    <protocolVersions>
      <protocolVersion>1.1</protocolVersion>
      <protocolVersion>2</protocolVersion>
    </protocolVersions>
    <method>GET</method>
    <uri>
      <scheme>http</scheme>
      <host>example.com</host>
      <port/>
      <path/>
      <queryParameters/>
      <fragment/>
    </uri>
    <headers/>
    <body/>
  </request>
  <response>
    <protocolVersion>1.0</protocolVersion>
    <status>404</status>
    <statusReason>Not Found</statusReason>
    <headers/>
    <body><![CDATA[$content]]></body>
  </response>
</fixture>

XML;

        self::assertStringEqualsFile('vfs://root/' . $id->toString(), $expected);
    }
}
