<?php declare(strict_types=1);

namespace Cspray\HttpClientTestInterceptor\Fixture;

use Amp\ByteStream\ReadableBuffer;
use Amp\Http\Client\Request;
use Amp\Http\Client\Response;
use Cspray\HttpClientTestInterceptor\Exception\InvalidFixture;
use Cspray\HttpClientTestInterceptor\Exception\MissingFixture;
use DateTimeImmutable;
use DOMDocument;
use DOMElement;
use DOMNodeList;
use DOMText;
use DOMXPath;
use League\Uri\Components\Query;
use League\Uri\UriString;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use function libxml_clear_errors;
use function libxml_use_internal_errors;

final class XmlFileBackedFixture implements Fixture {

    private readonly UuidInterface $id;
    private readonly DateTimeImmutable $createdAt;
    private readonly Request $request;
    private readonly Response $response;

    public function __construct(
        string $filePath
    ) {
        if (!file_exists($filePath)) {
            throw MissingFixture::missingFileBackedFixture($filePath);
        }

        $this->parseXmlFile($filePath);
    }

    private function parseXmlFile(string $filePath) : void {
        try {
            $dom = new DOMDocument();
            $dom->load($filePath);
            libxml_use_internal_errors(true);
            if (!$dom->schemaValidate(Schemas::Xml->getLocalSchemaPath())) {
                throw InvalidFixture::fromInvalidXmlSchema($filePath);
            }

            $xpath = new DOMXPath($dom);
            $xpath->registerNamespace('mf', Schemas::Xml->getNamespace());

            $id = $xpath->query('/mf:fixture/mf:id/text()[1]')[0]->nodeValue;
            $createdAt = $xpath->query('/mf:fixture/mf:createdAt/text()[1]')[0]->nodeValue;

            $this->id = Uuid::fromString($id);
            $this->createdAt = new DateTimeImmutable($createdAt);
            $this->parseRequest($xpath);
            $this->parseResponse($xpath);
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors(false);
        }
    }

    private function parseRequest(DOMXPath $xpath) : void {
        $protocolVersions = array_map(
            fn(DOMText $text) => $text->nodeValue,
            iterator_to_array($xpath->query('/mf:fixture/mf:request/mf:protocolVersions/mf:protocolVersion/text()'))
        );

        $method = $xpath->query('/mf:fixture/mf:request/mf:method/text()[1]')[0]->nodeValue;
        $body = $xpath->query('/mf:fixture/mf:request/mf:body/text()[1]')[0]?->nodeValue ?? '';

        $this->request = new Request(
            $this->parseRequestUri($xpath),
            $method,
            $body
        );
        $this->request->setProtocolVersions($protocolVersions);
        $this->request->setHeaders($this->parseHeaders($xpath, 'request'));
    }

    private function parseRequestUri(DOMXPath $xpath) : string {
        $uriScheme = $xpath->query('/mf:fixture/mf:request/mf:uri/mf:scheme/text()[1]')[0]->nodeValue;
        $uriHost = $xpath->query('/mf:fixture/mf:request/mf:uri/mf:host/text()[1]')[0]->nodeValue;
        $uriPath = $xpath->query('/mf:fixture/mf:request/mf:uri/mf:path/text()[1]')[0]->nodeValue;
        $uriPort = $xpath->query('/mf:fixture/mf:request/mf:uri/mf:port/text()[1]')[0]?->nodeValue;
        $uriFragment = $xpath->query('/mf:fixture/mf:request/mf:uri/mf:fragment/text()[1]')[0]?->nodeValue;
        $uriUser = $xpath->query('/mf:fixture/mf:request/mf:uri/mf:userInfo/mf:user/text()[1]')[0]?->nodeValue;
        $uriPass = $xpath->query('/mf:fixture/mf:request/mf:uri/mf:userInfo/mf:password/text()[1]')[0]?->nodeValue;

        $query = Query::createFromPairs();
        $uriQueryParameters = $xpath->query('/mf:fixture/mf:request/mf:uri/mf:queryParameters/mf:queryParameter');
        foreach ($uriQueryParameters as $queryParameter) {
            assert($queryParameter instanceof DOMElement);
            $query = $query->withPair(
                $queryParameter->getElementsByTagNameNS(Schemas::Xml->getNamespace(), 'name')[0]->nodeValue,
                $queryParameter->getElementsByTagNameNS(Schemas::Xml->getNamespace(), 'value')[0]->nodeValue
            );
        }

        return UriString::build([
            'scheme' => $uriScheme,
            'user' => $uriUser,
            'pass' => $uriPass,
            'host' => $uriHost,
            'path' => $uriPath,
            'port' => $uriPort,
            'query' => $query->count() === 0 ? null : $query->toString(),
            'fragment' => $uriFragment
        ]);
    }

    /**
     * @param DOMXPath $xpath
     * @return array<string, string>
     */
    private function parseHeaders(DOMXPath $xpath, string $requestOrResponse) : array {
        $headers = $xpath->query(sprintf('/mf:fixture/mf:%s/mf:headers/mf:header', $requestOrResponse));
        assert($headers instanceof DOMNodeList);

        $parsedHeaders = [];
        foreach ($headers as $header) {
            assert($header instanceof DOMElement);
            $name = $header->getElementsByTagNameNS(Schemas::Xml->getNamespace(), 'name')[0]->nodeValue;
            $values = $header->getElementsByTagNameNS(Schemas::Xml->getNamespace(), 'values')[0];
            assert($values instanceof DOMElement);

            $parsedValues = [];
            foreach ($values->getElementsByTagNameNS(Schemas::Xml->getNamespace(), 'value') as $value) {
                assert($value instanceof DOMElement);
                $parsedValues[] = $value->nodeValue;
            }

            $parsedHeaders[$name] = $parsedValues;
        }
        return $parsedHeaders;
    }

    private function parseResponse(DOMXPath $xpath) : void {
        $protocolVersion = $xpath->query('/mf:fixture/mf:response/mf:protocolVersion/text()[1]')[0]->nodeValue;
        $statusCode = (int) $xpath->query('/mf:fixture/mf:response/mf:status/text()[1]')[0]->nodeValue;
        $statusReason = $xpath->query('/mf:fixture/mf:response/mf:statusReason/text()[1]')[0]->nodeValue;
        $contents = $xpath->query('/mf:fixture/mf:response/mf:body/text()[1]')[0]?->nodeValue ?? '';
        $this->response = new Response(
            $protocolVersion,
            $statusCode,
            $statusReason,
            $this->parseHeaders($xpath, 'response'),
            new ReadableBuffer(base64_decode($contents)),
            $this->request
        );
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