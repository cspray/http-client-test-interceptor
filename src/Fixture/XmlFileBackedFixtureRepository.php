<?php declare(strict_types=1);

namespace Cspray\HttpClientTestInterceptor\Fixture;

use Amp\Http\Client\Request;
use Amp\Http\Client\Response;
use Cspray\HttpClientTestInterceptor\Exception\InvalidFixtureRepository;
use DOMDocument;
use DOMElement;
use DOMNode;
use FilesystemIterator;
use Generator;
use League\Uri\Components\Query;
use League\Uri\Components\UserInfo;
use Ramsey\Uuid\Uuid;
use SplFileInfo;

final class XmlFileBackedFixtureRepository implements FixtureRepository {

    public function __construct(
        private readonly string $fixtureDir,
        private readonly FixtureCache $cache
    ) {
        if (!is_dir($this->fixtureDir)) {
            throw InvalidFixtureRepository::fromFileBackedRepoNotDirectory($this->fixtureDir);
        } else if (!is_writable($this->fixtureDir)) {
            throw InvalidFixtureRepository::fromFileBackedRepoDirectoryNotWritable($this->fixtureDir);
        }
    }

    public function getFixtures() : Generator {
        $iterator = new FilesystemIterator($this->fixtureDir, FilesystemIterator::SKIP_DOTS);
        foreach ($iterator as $file) {
            if ($file->isDir()) {
                continue;
            }
            assert($file instanceof SplFileInfo);
            $id = Uuid::fromString($file->getBasename());
            if (!$this->cache->has($id)) {
                $fixture = new XmlFileBackedFixture($file->getPathname());
                $this->cache->set($fixture);
            }
            yield $this->cache->get($id);
        }
    }

    public function saveFixture(Fixture $fixture) : void {
        $filePath = sprintf('%s/%s', $this->fixtureDir, $fixture->getId()->toString());
        $this->getDomDocumentFromFixture($fixture)->save($filePath);
        $this->cache->set($fixture);
    }

    private function getDomDocumentFromFixture(Fixture $fixture) : DOMDocument {
        $dom = new DOMDocument(encoding: 'UTF-8');
        $dom->formatOutput = true;

        $this->add($dom, $fixtureElement = $this->element($dom, 'fixture'));
        $this->add(
            $fixtureElement,
            $this->element($dom, 'id', $fixture->getId()->toString()),
            $this->element($dom, 'createdAt', $fixture->getCreatedAt()->format(DATE_RFC3339)),
            $requestElement = $this->element($dom, 'request'),
            $responseElement = $this->element($dom, 'response')
        );

        $this->populateRequestDom($dom, $requestElement, $fixture->getRequest());
        $this->populateResponseDom($dom, $responseElement, $fixture->getResponse());

        $dom->schemaValidate(Schemas::Xml->getLocalSchemaPath());

        return $dom;
    }

    private function populateRequestDom(DOMDocument $dom, DOMElement $requestElement, Request $request) : void {
        $protocolVersionsElement = $this->element($dom, 'protocolVersions');

        foreach ($request->getProtocolVersions() as $protocolVersion) {
            $this->add($protocolVersionsElement, $this->element($dom, 'protocolVersion', $protocolVersion));
        }

        $this->add(
            $requestElement,
            $protocolVersionsElement,
            $this->element($dom, 'method', $request->getMethod()),
            $uriElement = $this->element($dom, 'uri'),
            $headersElement = $this->element($dom, 'headers'),
            $bodyElement = $this->element($dom, 'body')
        );

        $uri = $request->getUri();
        $uriElements = [
            $this->element($dom, 'scheme', $uri->getScheme()),
            $this->element($dom, 'host', $uri->getHost()),
            $portElement = $this->element($dom, 'port'),
            $pathElement = $this->element($dom, 'path'),
            $queryParametersElement = $this->element($dom, 'queryParameters'),
            $fragmentElement = $this->element($dom, 'fragment')
        ];

        if ($uri->getPort() !== null) {
            $portElement->nodeValue = (string) $uri->getPort();
        }

        if ($uri->getPath() !== '') {
            $pathElement->nodeValue = $uri->getPath();
        }

        foreach (Query::createFromUri($uri)->pairs() as $name => $value) {
            $this->add(
                $queryParametersElement,
                $queryParameterElement = $this->element($dom, 'queryParameter')
            );

            $this->add(
                $queryParameterElement,
                $this->element($dom, 'name', $name),
                $this->element($dom, 'value', $value)
            );
        }

        if ($uri->getFragment() !== '') {
            $fragmentElement->nodeValue = $uri->getFragment();
        }

        $this->add($uriElement, ...$uriElements);

        $this->populateHeadersDom($dom, $headersElement, $request);

        $rawBody = $request->getBody()->getContent()->read();
        if ($rawBody !== null && $rawBody !== '') {
            $bodyCdata = $dom->createCDATASection($rawBody);
            $this->add($bodyElement, $bodyCdata);
        }

    }

    private function populateResponseDom(DOMDocument $dom, DOMElement $responseElement, Response $response) : void {
        $this->add(
            $responseElement,
            $this->element($dom, 'protocolVersion', $response->getProtocolVersion()),
            $this->element($dom, 'status', (string) $response->getStatus()),
            $this->element($dom, 'statusReason', $response->getReason()),
            $headersElement = $this->element($dom, 'headers'),
            $bodyElement = $this->element($dom, 'body')
        );

        $this->populateHeadersDom($dom, $headersElement, $response);
        $body = $response->getBody()->buffer();
        // We need to set the body to a new stream otherwise if read() or buffer() is called again it will fail
        $response->setBody($body);
        if ($body !== '') {
            $bodyCdata = $dom->createCDATASection(base64_encode($body));
            $bodyElement->appendChild($bodyCdata);
        }
    }

    private function populateHeadersDom(DOMDocument $dom, DOMElement $headersElement, Request|Response $requestOrResponse) : void {
        if (count($headers = $requestOrResponse->getHeaders()) > 0) {
            foreach ($headers as $headerName => $headerValues) {
                $this->add(
                    $headersElement,
                    $headerElement = $this->element($dom, 'header')
                );
                $this->add(
                    $headerElement,
                    $this->element($dom, 'name', $headerName),
                    $valuesElement = $this->element($dom, 'values')
                );
                foreach ($headerValues as $headerValue) {
                    $this->add(
                        $valuesElement,
                        $this->element($dom, 'value', base64_encode($headerValue))
                    );
                }
            }
        }
    }

    private function add(DOMNode $parent, DOMNode... $children) {
        foreach ($children as $child) {
            $parent->appendChild($child);
        }
    }

    private function element(DOMDocument $dom, string $name, string $value = null) : DOMElement {
        $node = $dom->createElementNS(Schemas::Xml->getNamespace(), $name);
        if ($value !== null) {
            $node->nodeValue = $value;
        }

        return $node;
    }

    public function removeFixture(Fixture $fixture) : void {
        $this->cache->remove($fixture->getId());
        $fixturePath = sprintf('%s/%s', $this->fixtureDir, $fixture->getId()->toString());
        if (file_exists($fixturePath)) {
            unlink($fixturePath);
        }
    }
}