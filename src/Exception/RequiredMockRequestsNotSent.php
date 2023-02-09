<?php

namespace Cspray\HttpClientTestInterceptor\Exception;

use Cspray\HttpClientTestInterceptor\HttpMock\HttpMockerRequiredInvocations;

final class RequiredMockRequestsNotSent extends Exception {

    public static function fromMissingRequiredInvocations(int $totalMocks, int $matchedMocks, HttpMockerRequiredInvocations $requiredInvocations) : self {
        $invocationMessage = match($requiredInvocations) {
            HttpMockerRequiredInvocations::All => 'All mocked HTTP interactions must be requested.'  ,
            HttpMockerRequiredInvocations::Any => 'At least 1 mocked HTTP interaction must be requested.'
        };
        return new self(
            sprintf(
                'There are %d mocked HTTP interactions but %d had a matching Request. %s.',
                $totalMocks,
                $matchedMocks,
                $invocationMessage
            )
        );
    }

}