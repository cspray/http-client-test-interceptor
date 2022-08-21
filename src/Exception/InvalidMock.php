<?php

namespace Cspray\HttpClientTestInterceptor\Exception;

class InvalidMock extends Exception {

    public static function fromNoResponse() : self {
        return new self('An HttpMocker MUST provide a Response to return but none was provided.');
    }

    public static function fromNoRequest() : self {
        return new self('An HttpMocker MUST provide a Request to match against but none was provided.');
    }

    public static function fromNoRequestAndResponse() : self {
        return new self('An HttpMocker MUST provide a Request to match against AND a Response to return but nothing was provided.');
    }

    public static function fromEmptyMatchers() : self {
        return new self('An HttpMocker MUST provide a list of Matchers to compare against sent Requests but none was provided.');
    }

}