# HttpClient TestInterceptor

A framework for testing [amphp/http-client](https://github.com/amphp/http-client) v5 using the application interceptor design pattern it introduces. The primary reason for using this library is in your Unit or Integration Tests to test known HTTP interactions. This library provides 2 mechanisms for testing these interactions:

1. Writing **unit tests** where every Request should be mocked and **no network requests** are made.
2. Writing **integration tests** where a Request will be sent over the network if there isn't a _fixture_ stored for that Request. The response received from the network request will be stored and subsequent requests will not go over the network.

Whether you're writing unit or integration tests the same underlying mechanisms are used. You can specify fine-grained pieces of the Request that should be matched against or provide your own strategy for matching Requests against a Fixture or Mock.

There are plenty of reasons **_not_** to use this library:

- You aren't using [amphp/http-client](https://github.com/amphp/http-client) v5 or greater.
- You already have a viable, working solution for testing your API interactions.

## Installation

```
composer require amphp/http-client:v5.0.0-beta.3
composer require --dev cspray/http-client-test-interceptor
```

> The amphp/http-client library is still in beta. You may need to adjust your minimum stability until http-client releases a 5.0 version.

## Unit Testing Quick Start

Unit testing is meant to test your HttpClient against specific, known HTTP interactions. For example, you may need to test how your code under test responds to different headers, specific error codes, or a precise body structure. These tests can often be hard, or even impossible, to test reliably with integration tests.

Unlike writing integration tests that use fixtures there's no setup steps necessary. The first step is to write your TestCase!

```php
<?php declare(strict_types=1);

namespace Acme\HttpMockingDemo;

use Cspray\HttpClientTestInterceptor\HttpMockingTestTrait;
use Amp\Http\Client\Request;
use Amp\Http\Client\HttpClientBuilder;
use Cspray\HttpClientTestInterceptor\MockResponse;
use League\Uri\Http;
use PHPUnit\Framework\TestCase;

final class ApiUnitTest extends TestCase {

    use HttpMockingTestTrait;
    
    public function testGetResource() : void {
        $request = new Request(Http::createFromString('http://example.com'), 'POST');
        $response = MockResponse::fromBody('My expected body');
        $this->httpMock()->whenClientReceivesRequest($request)->willReturnResponse($response);
            
        $client = (new HttpClientBuilder())->intercept($this->getMockingInterceptor())->build();
        
        // We match on the values of the mocked Request ... NOT the identity of the object
        $actual = $client->request(new Request(Http::createFromString('http://example.com')), 'POST');
        
        // If you were to uncomment the below line this test would fail with a RequestNotMocked exception as the methods do not match
        // $client->request(new Request(Http::createFromString('http://example.com')), 'GET');
        
        self::assertSame($response, $actual);
    }

}
```

### Mocking Request Matching

Because mocks require more manual setup the way they get matched uses a looser set of request matching strategies; instead of checking against the entire Request only the method and URI are matched against. If you'd like to make this more strict you can provide a set of Matchers when defining the Request to mock.

```php
<?php declare(strict_types=1);

namespace Acme\HttpMockingDemo;

use Cspray\HttpClientTestInterceptor\HttpMockingTestTrait;
use Amp\Http\Client\Request;
use Amp\Http\Client\HttpClientBuilder;
use Cspray\HttpClientTestInterceptor\MockResponse;
use Cspray\HttpClientTestInterceptor\RequestMatchingStrategy\Matcher;use League\Uri\Http;
use PHPUnit\Framework\TestCase;

final class ApiHeadersMatchingUnitTest extends TestCase {

    use HttpMockingTestTrait;
    
    public function testGetResource() : void {
        $request = new Request(Http::createFromString('http://example.com'), 'POST');
        $request->setHeader('Authorization', 'some-token');
        $response = MockResponse::fromBody('My expected body');
        $this->httpMock()->whenClientReceivesRequest($request, [Matcher::Uri, Matcher::Method, Matcher::Headers])
            ->willReturnResponse($response);
            
        $client = (new HttpClientBuilder())->intercept($this->getMockingInterceptor())->build();
        
        // We match on the values of the mocked Request ... NOT the identity of the object
        $actualRequest = new Request('http://example.com', 'POST');
        $actualRequest->setHeader('Authorization', 'some-token');
        $actual = $client->request($actualRequest);
        
        // If you were to uncomment the below line this test would fail with a RequestNotMocked exception as the _headers_ do not match
        // $client->request(new Request(Http::createFromString('http://example.com')), 'POST');
        
        self::assertSame($response, $actual);
    }

}
```

## Integration Testing Quick Start

Integration testing is meant when you want to test your HttpClient against **real responses from an actual API**. When the request is sent over the network the first time a fixture is stored with the response. If a matching request is sent again the response will be generated from the stored fixture and not sent over the network.

### Create a Fixture Directory

The first thing needs to be done is to create a directory where HTTP Fixtures will be stored. There's no "right" place to store these files, any writable directory will work. Assuming your tests live in a directory named `test`, this guide recommends creating a `test/http_fixture` directory.

```
mkdir -p test/http_fixture
```

### Mark Tests Making HTTP Requests

Now, in your tests Attribute your `TestCase` or test method with the path where Fixtures will be stored and used to match requests.

```php
<?php declare(strict_types=1);

// Stored in test/ApiTest.php

namespace Acme\HttpFixtureDemo;

use Amp\Http\Client\HttpClientBuilder;
use Amp\Http\Client\Request;
use Cspray\HttpClientTestInterceptor\Attribute\HttpFixture;
use Cspray\HttpClientTestInterceptor\HttpFixtureAwareTestTrait;
use PHPUnit\Framework\TestCase;

#[HttpFixture(__DIR__ . '/http_fixture')]
final class ApiTest extends TestCase {

    use HttpFixtureAwareTestTrait;

    public function testGetResource() : void {
        $httpClient = (new HttpClientBuilder())
            ->intercept($this->getTestInterceptor())
            ->build();
        
        $response = $httpClient->request(new Request('https://api.example.com'));
        
        self::assertSame(200, $response->getStatus());
    }

}
```

### Integration Request Matching

Out-of-the-box the library will attempt to match every aspect of the Request against a stored Fixture in integration tests. It is possible to have granular control over what parts of the Request are matched against. For example, let's set up our `TestInterceptor` to only match the Request method and URI instead of everything.

```php
<?php declare(strict_types=1);

// Stored in test/ApiTest.php

namespace Acme\HttpFixtureDemo;

use Amp\Http\Client\HttpClientBuilder;
use Amp\Http\Client\Request;
use Cspray\HttpClientTestInterceptor\Attribute\HttpFixture;
use Cspray\HttpClientTestInterceptor\HttpFixtureAwareTestTrait;
use Cspray\HttpClientTestInterceptor\Attribute\HttpRequestMatchers;
use Cspray\HttpClientTestInterceptor\RequestMatchingStrategy\Matcher;
use PHPUnit\Framework\TestCase;

#[HttpFixture(__DIR__ . '/http_fixture')]
#[HttpRequestMatchers(Matcher::Method, Matcher::Uri)]
final class ApiTest extends TestCase {

    use HttpFixtureAwareTestTrait;

    public function testGetResource() : void {
        $httpClient = (new HttpClientBuilder())
            ->intercept($this->getTestInterceptor())
            ->build();
        
        $response = $httpClient->request(new Request('https://api.example.com'));
        
        self::assertSame(200, $response->getStatus());
    }

}
```

Now the `RequestMatchingStrategy` utilized will only check the method and URI. You can compose your request matching with whatever pieces of the Request are appropriate. It is also possible to define the `#[HttpRequestMatchers]` and `#[HttpFixture]` Attributes on individual test methods. If you provide Attributes on both the Attributes on the test method will override those on the TestCase.