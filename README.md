# HttpClient TestInterceptor

A testing framework for easily mocking Requests using the [amphp/http-client](https://github.com/amphp/http-client) v5. The primary reason for using this library is in your Unit or Integration Tests to provide the following benefits:

- Interactions with HTTP APIs can be tested with known Responses
- Tests in a CI/CD environment do not rely on network interactions

There are plenty of reasons **_not_** to use this library:

- You aren't using [amphp/http-client](https://github.com/amphp/http-client) v5 or greater.
- You already have a viable, working solution for testing your API interactions.

## Installation

```
composer require --dev cspray/http-client-test-interceptor
```

> The amphp/http-client library is still in beta. You may need to adjust your minimum stability until http-client releases a 5.0 version.

## Quick Start

If you're interested in the technical details check out the "How It Works" section below, otherwise the "Quick Start" shows you the minimum setup you need to get started.

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
use Cspray\HttpClientTestInterceptor\HttpFixtureTrait;
use PHPUnit\Framework\TestCase;

#[HttpFixture(__DIR__ . '/http_fixture')]
final class ApiTest extends TestCase {

    use HttpFixtureTrait;

    public function testGetResource() : void {
        $httpClient = (new HttpClientBuilder())
            ->intercept($this->getTestInterceptor())
            ->build();
        
        $response = $httpClient->request(new Request('https://api.example.com'));
        
        self::assertSame(200, $response->getStatus());
    }

}
```

On the first run of this test a real request will be sent to `https://api.example.com`. The Request and Response will be stored as a Fixture in the given directory. The next time this test runs a matching Fixture will be found and instead of an HTTP request going over the network the Response stored in the Fixture will be returned.

### Request Matching

Out-of-the-box the library will attempt to match every aspect of the Request against a stored Fixture. It is possible to have granular control over what parts of the Request are matched against. For example, let's set up our `TestInterceptor` to only match the Request method and URI instead of everything.

```php
<?php declare(strict_types=1);

// Stored in test/ApiTest.php

namespace Acme\HttpFixtureDemo;

use Amp\Http\Client\HttpClientBuilder;
use Amp\Http\Client\Request;
use Cspray\HttpClientTestInterceptor\Attribute\HttpFixture;
use Cspray\HttpClientTestInterceptor\HttpFixtureTrait;
use Cspray\HttpClientTestInterceptor\Attribute\HttpRequestMatchers;
use Cspray\HttpClientTestInterceptor\RequestMatchingStrategy\Matchers;use PHPUnit\Framework\TestCase;

#[HttpFixture(__DIR__ . '/http_fixture')]
#[HttpRequestMatchers(Matchers::Method, Matchers::Uri)]
final class ApiTest extends TestCase {

    use HttpFixtureTrait;

    public function testGetResource() : void {
        $httpClient = (new HttpClientBuilder())
            ->intercept($this->getTestInterceptor())
            ->build();
        
        $response = $httpClient->request(new Request('https://api.example.com'));
        
        self::assertSame(200, $response->getStatus());
    }

}
```

Now the `RequestMatchingStrategy` utilized will only check the method and URI. You can compose your request matching with whatever pieces of the Request are appropriate.