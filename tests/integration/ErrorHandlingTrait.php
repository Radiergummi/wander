<?php
/** @noinspection UnknownInspectionInspection, PhpUnused, PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace Radiergummi\Wander\Tests\Integration;

use Nyholm\Psr7\Uri;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\UriInterface;
use Radiergummi\Wander\Exceptions\ResponseErrors\BadRequestException;
use Radiergummi\Wander\Exceptions\ResponseErrors\ConflictException;
use Radiergummi\Wander\Exceptions\ResponseErrors\ExpectationFailedException;
use Radiergummi\Wander\Exceptions\ResponseErrors\FailedDependencyException;
use Radiergummi\Wander\Exceptions\ResponseErrors\ForbiddenException;
use Radiergummi\Wander\Exceptions\ResponseErrors\GatewayTimeoutException;
use Radiergummi\Wander\Exceptions\ResponseErrors\GoneException;
use Radiergummi\Wander\Exceptions\ResponseErrors\HttpVersionNotSupportedException;
use Radiergummi\Wander\Exceptions\ResponseErrors\ImATeapotException;
use Radiergummi\Wander\Exceptions\ResponseErrors\InsufficientStorageException;
use Radiergummi\Wander\Exceptions\ResponseErrors\InternalServerErrorException;
use Radiergummi\Wander\Exceptions\ResponseErrors\LengthRequiredException;
use Radiergummi\Wander\Exceptions\ResponseErrors\LockedException;
use Radiergummi\Wander\Exceptions\ResponseErrors\LoopDetectedException;
use Radiergummi\Wander\Exceptions\ResponseErrors\MethodNotAllowedException;
use Radiergummi\Wander\Exceptions\ResponseErrors\MisdirectedRequestException;
use Radiergummi\Wander\Exceptions\ResponseErrors\NetworkAuthenticationRequiredException;
use Radiergummi\Wander\Exceptions\ResponseErrors\NotAcceptableException;
use Radiergummi\Wander\Exceptions\ResponseErrors\NotExtendedException;
use Radiergummi\Wander\Exceptions\ResponseErrors\NotFoundException;
use Radiergummi\Wander\Exceptions\ResponseErrors\NotImplementedException;
use Radiergummi\Wander\Exceptions\ResponseErrors\PayloadTooLargeException;
use Radiergummi\Wander\Exceptions\ResponseErrors\PaymentRequiredException;
use Radiergummi\Wander\Exceptions\ResponseErrors\PreconditionFailedException;
use Radiergummi\Wander\Exceptions\ResponseErrors\PreconditionRequiredException;
use Radiergummi\Wander\Exceptions\ResponseErrors\ProxyAuthenticationRequiredException;
use Radiergummi\Wander\Exceptions\ResponseErrors\RangeNotSatisfiableException;
use Radiergummi\Wander\Exceptions\ResponseErrors\RequestHeaderFieldsTooLargeException;
use Radiergummi\Wander\Exceptions\ResponseErrors\RequestTimeoutException;
use Radiergummi\Wander\Exceptions\ResponseErrors\ServiceUnavailableException;
use Radiergummi\Wander\Exceptions\ResponseErrors\TooEarlyException;
use Radiergummi\Wander\Exceptions\ResponseErrors\TooManyRequestsException;
use Radiergummi\Wander\Exceptions\ResponseErrors\UnauthorizedException;
use Radiergummi\Wander\Exceptions\ResponseErrors\UnavailableForLegalReasonsException;
use Radiergummi\Wander\Exceptions\ResponseErrors\UnprocessableEntityException;
use Radiergummi\Wander\Exceptions\ResponseErrors\UnsupportedMediaTypeException;
use Radiergummi\Wander\Exceptions\ResponseErrors\UpgradeRequiredException;
use Radiergummi\Wander\Exceptions\ResponseErrors\UriTooLongException;
use Radiergummi\Wander\Exceptions\ResponseErrors\VariantAlsoNegotiatesException;
use Radiergummi\Wander\Exceptions\UnresolvableHostException;
use Radiergummi\Wander\Http\Method;
use Radiergummi\Wander\Http\Status as S;
use Radiergummi\Wander\Interfaces\HttpClientInterface;

/**
 * Error Handling Trait
 * ====================
 * This trait adds error handling tests to the integration test class. Providing
 * tests for all known HTTP errors, it makes sure all drivers handle HTTP errors
 * gracefully.
 * There need to be tests for all exceptions, as phpunit can't (AFAIK) check for
 * more than one exception in a single test method.
 *
 * @package Radiergummi\Wander\Tests\Integration
 */
trait ErrorHandlingTrait
{
    public function testThrowsUnresolvableHostException(): void
    {
        if (isset($this->skippedTests[__FUNCTION__])) {
            $this->markTestSkipped($this->skippedTests[__FUNCTION__]);
        }

        // the ".invalid" TLD is specifically reserved for tests exactly like the
        // one below: See https://en.wikipedia.org/wiki/.invalid
        $request = $this->createRequest(
            Method::GET,
            new Uri('https://test.invalid')
        );

        $this->expectException(UnresolvableHostException::class);
        $this->getClient()->request($request);
    }

    public function testThrowsBadRequestException(): void
    {
        if (isset($this->skippedTests[__FUNCTION__])) {
            $this->markTestSkipped($this->skippedTests[__FUNCTION__]);
        }

        $request = $this->createRequest(
            Method::GET,
            $this->getTestServerUri()
                 ->withPath('/status')
                 ->withQuery('code=' . S::BAD_REQUEST)
        );

        $this->expectException(BadRequestException::class);
        $this->getClient()->request($request);
    }

    public function testThrowsUnauthorizedException(): void
    {
        if (isset($this->skippedTests[__FUNCTION__])) {
            $this->markTestSkipped($this->skippedTests[__FUNCTION__]);
        }

        $request = $this->createRequest(
            Method::GET,
            $this->getTestServerUri()
                 ->withPath('/status')
                 ->withQuery('code=' . S::UNAUTHORIZED)
        );

        $this->expectException(UnauthorizedException::class);
        $this->getClient()->request($request);
    }

    public function testThrowsPaymentRequiredException(): void
    {
        if (isset($this->skippedTests[__FUNCTION__])) {
            $this->markTestSkipped($this->skippedTests[__FUNCTION__]);
        }

        $request = $this->createRequest(
            Method::GET,
            $this->getTestServerUri()
                 ->withPath('/status')
                 ->withQuery('code=' . S::PAYMENT_REQUIRED)
        );

        $this->expectException(PaymentRequiredException::class);
        $this->getClient()->request($request);
    }

    public function testThrowsForbiddenException(): void
    {
        if (isset($this->skippedTests[__FUNCTION__])) {
            $this->markTestSkipped($this->skippedTests[__FUNCTION__]);
        }

        $request = $this->createRequest(
            Method::GET,
            $this->getTestServerUri()
                 ->withPath('/status')
                 ->withQuery('code=' . S::FORBIDDEN)
        );

        $this->expectException(ForbiddenException::class);
        $this->getClient()->request($request);
    }

    public function testThrowsNotFoundException(): void
    {
        if (isset($this->skippedTests[__FUNCTION__])) {
            $this->markTestSkipped($this->skippedTests[__FUNCTION__]);
        }

        $request = $this->createRequest(
            Method::GET,
            $this->getTestServerUri()
                 ->withPath('/status')
                 ->withQuery('code=' . S::NOT_FOUND)
        );

        $this->expectException(NotFoundException::class);
        $this->getClient()->request($request);
    }

    public function testThrowsMethodNotAllowedException(): void
    {
        if (isset($this->skippedTests[__FUNCTION__])) {
            $this->markTestSkipped($this->skippedTests[__FUNCTION__]);
        }

        $request = $this->createRequest(
            Method::GET,
            $this->getTestServerUri()
                 ->withPath('/status')
                 ->withQuery('code=' . S::METHOD_NOT_ALLOWED)
        );

        $this->expectException(MethodNotAllowedException::class);
        $this->getClient()->request($request);
    }

    public function testThrowsNotAcceptableException(): void
    {
        if (isset($this->skippedTests[__FUNCTION__])) {
            $this->markTestSkipped($this->skippedTests[__FUNCTION__]);
        }

        $request = $this->createRequest(
            Method::GET,
            $this->getTestServerUri()
                 ->withPath('/status')
                 ->withQuery('code=' . S::NOT_ACCEPTABLE)
        );

        $this->expectException(NotAcceptableException::class);
        $this->getClient()->request($request);
    }

    public function testThrowsProxyAuthenticationRequiredException(): void
    {
        if (isset($this->skippedTests[__FUNCTION__])) {
            $this->markTestSkipped($this->skippedTests[__FUNCTION__]);
        }

        $request = $this->createRequest(
            Method::GET,
            $this->getTestServerUri()
                 ->withPath('/status')
                 ->withQuery('code=' . S::PROXY_AUTHENTICATION_REQUIRED)
        );

        $this->expectException(ProxyAuthenticationRequiredException::class);
        $this->getClient()->request($request);
    }

    public function testThrowsRequestTimeoutException(): void
    {
        if (isset($this->skippedTests[__FUNCTION__])) {
            $this->markTestSkipped($this->skippedTests[__FUNCTION__]);
        }

        $request = $this->createRequest(
            Method::GET,
            $this->getTestServerUri()
                 ->withPath('/status')
                 ->withQuery('code=' . S::REQUEST_TIMEOUT)
        );

        $this->expectException(RequestTimeoutException::class);
        $this->getClient()->request($request);
    }

    public function testThrowsConflictException(): void
    {
        if (isset($this->skippedTests[__FUNCTION__])) {
            $this->markTestSkipped($this->skippedTests[__FUNCTION__]);
        }

        $request = $this->createRequest(
            Method::GET,
            $this->getTestServerUri()
                 ->withPath('/status')
                 ->withQuery('code=' . S::CONFLICT)
        );

        $this->expectException(ConflictException::class);
        $this->getClient()->request($request);
    }

    public function testThrowsGoneException(): void
    {
        if (isset($this->skippedTests[__FUNCTION__])) {
            $this->markTestSkipped($this->skippedTests[__FUNCTION__]);
        }

        $request = $this->createRequest(
            Method::GET,
            $this->getTestServerUri()
                 ->withPath('/status')
                 ->withQuery('code=' . S::GONE)
        );

        $this->expectException(GoneException::class);
        $this->getClient()->request($request);
    }

    public function testThrowsLengthRequiredException(): void
    {
        if (isset($this->skippedTests[__FUNCTION__])) {
            $this->markTestSkipped($this->skippedTests[__FUNCTION__]);
        }

        $request = $this->createRequest(
            Method::GET,
            $this->getTestServerUri()
                 ->withPath('/status')
                 ->withQuery('code=' . S::LENGTH_REQUIRED)
        );

        $this->expectException(LengthRequiredException::class);
        $this->getClient()->request($request);
    }

    public function testThrowsPreconditionFailedException(): void
    {
        if (isset($this->skippedTests[__FUNCTION__])) {
            $this->markTestSkipped($this->skippedTests[__FUNCTION__]);
        }

        $request = $this->createRequest(
            Method::GET,
            $this->getTestServerUri()
                 ->withPath('/status')
                 ->withQuery('code=' . S::PRECONDITION_FAILED)
        );

        $this->expectException(PreconditionFailedException::class);
        $this->getClient()->request($request);
    }

    public function testThrowsPayloadTooLargeException(): void
    {
        if (isset($this->skippedTests[__FUNCTION__])) {
            $this->markTestSkipped($this->skippedTests[__FUNCTION__]);
        }

        $request = $this->createRequest(
            Method::GET,
            $this->getTestServerUri()
                 ->withPath('/status')
                 ->withQuery('code=' . S::PAYLOAD_TOO_LARGE)
        );

        $this->expectException(PayloadTooLargeException::class);
        $this->getClient()->request($request);
    }

    public function testThrowsUriTooLongException(): void
    {
        if (isset($this->skippedTests[__FUNCTION__])) {
            $this->markTestSkipped($this->skippedTests[__FUNCTION__]);
        }

        $request = $this->createRequest(
            Method::GET,
            $this->getTestServerUri()
                 ->withPath('/status')
                 ->withQuery('code=' . S::URI_TOO_LONG)
        );

        $this->expectException(UriTooLongException::class);
        $this->getClient()->request($request);
    }

    public function testThrowsUnsupportedMediaTypeException(): void
    {
        if (isset($this->skippedTests[__FUNCTION__])) {
            $this->markTestSkipped($this->skippedTests[__FUNCTION__]);
        }

        $request = $this->createRequest(
            Method::GET,
            $this->getTestServerUri()
                 ->withPath('/status')
                 ->withQuery('code=' . S::UNSUPPORTED_MEDIA_TYPE)
        );

        $this->expectException(UnsupportedMediaTypeException::class);
        $this->getClient()->request($request);
    }

    public function testThrowsRangeNotSatisfiableException(): void
    {
        if (isset($this->skippedTests[__FUNCTION__])) {
            $this->markTestSkipped($this->skippedTests[__FUNCTION__]);
        }

        $request = $this->createRequest(
            Method::GET,
            $this->getTestServerUri()
                 ->withPath('/status')
                 ->withQuery('code=' . S::RANGE_NOT_SATISFIABLE)
        );

        $this->expectException(RangeNotSatisfiableException::class);
        $this->getClient()->request($request);
    }

    public function testThrowsExpectationFailedException(): void
    {
        if (isset($this->skippedTests[__FUNCTION__])) {
            $this->markTestSkipped($this->skippedTests[__FUNCTION__]);
        }

        $request = $this->createRequest(
            Method::GET,
            $this->getTestServerUri()
                 ->withPath('/status')
                 ->withQuery('code=' . S::EXPECTATION_FAILED)
        );

        $this->expectException(ExpectationFailedException::class);
        $this->getClient()->request($request);
    }

    public function testThrowsImATeapotException(): void
    {
        if (isset($this->skippedTests[__FUNCTION__])) {
            $this->markTestSkipped($this->skippedTests[__FUNCTION__]);
        }

        $request = $this->createRequest(
            Method::GET,
            $this->getTestServerUri()
                 ->withPath('/status')
                 ->withQuery('code=' . S::IM_A_TEAPOT)
        );

        $this->expectException(ImATeapotException::class);
        $this->getClient()->request($request);
    }

    public function testThrowsMisdirectedRequestException(): void
    {
        if (isset($this->skippedTests[__FUNCTION__])) {
            $this->markTestSkipped($this->skippedTests[__FUNCTION__]);
        }

        $request = $this->createRequest(
            Method::GET,
            $this->getTestServerUri()
                 ->withPath('/status')
                 ->withQuery('code=' . S::MISDIRECTED_REQUEST)
        );

        $this->expectException(MisdirectedRequestException::class);
        $this->getClient()->request($request);
    }

    public function testThrowsUnprocessableEntityException(): void
    {
        if (isset($this->skippedTests[__FUNCTION__])) {
            $this->markTestSkipped($this->skippedTests[__FUNCTION__]);
        }

        $request = $this->createRequest(
            Method::GET,
            $this->getTestServerUri()
                 ->withPath('/status')
                 ->withQuery('code=' . S::UNPROCESSABLE_ENTITY)
        );

        $this->expectException(UnprocessableEntityException::class);
        $this->getClient()->request($request);
    }

    public function testThrowsLockedException(): void
    {
        if (isset($this->skippedTests[__FUNCTION__])) {
            $this->markTestSkipped($this->skippedTests[__FUNCTION__]);
        }

        $request = $this->createRequest(
            Method::GET,
            $this->getTestServerUri()
                 ->withPath('/status')
                 ->withQuery('code=' . S::LOCKED)
        );

        $this->expectException(LockedException::class);
        $this->getClient()->request($request);
    }

    public function testThrowsFailedDependencyException(): void
    {
        if (isset($this->skippedTests[__FUNCTION__])) {
            $this->markTestSkipped($this->skippedTests[__FUNCTION__]);
        }

        $request = $this->createRequest(
            Method::GET,
            $this->getTestServerUri()
                 ->withPath('/status')
                 ->withQuery('code=' . S::FAILED_DEPENDENCY)
        );

        $this->expectException(FailedDependencyException::class);
        $this->getClient()->request($request);
    }

    public function testThrowsTooEarlyException(): void
    {
        if (isset($this->skippedTests[__FUNCTION__])) {
            $this->markTestSkipped($this->skippedTests[__FUNCTION__]);
        }

        $request = $this->createRequest(
            Method::GET,
            $this->getTestServerUri()
                 ->withPath('/status')
                 ->withQuery('code=' . S::TOO_EARLY)
        );

        $this->expectException(TooEarlyException::class);
        $this->getClient()->request($request);
    }

    public function testThrowsUpgradeRequiredException(): void
    {
        if (isset($this->skippedTests[__FUNCTION__])) {
            $this->markTestSkipped($this->skippedTests[__FUNCTION__]);
        }

        $request = $this->createRequest(
            Method::GET,
            $this->getTestServerUri()
                 ->withPath('/status')
                 ->withQuery('code=' . S::UPGRADE_REQUIRED)
        );

        $this->expectException(UpgradeRequiredException::class);
        $this->getClient()->request($request);
    }

    public function testThrowsPreconditionRequiredException(): void
    {
        if (isset($this->skippedTests[__FUNCTION__])) {
            $this->markTestSkipped($this->skippedTests[__FUNCTION__]);
        }

        $request = $this->createRequest(
            Method::GET,
            $this->getTestServerUri()
                 ->withPath('/status')
                 ->withQuery('code=' . S::PRECONDITION_REQUIRED)
        );

        $this->expectException(PreconditionRequiredException::class);
        $this->getClient()->request($request);
    }

    public function testThrowsTooManyRequestsException(): void
    {
        if (isset($this->skippedTests[__FUNCTION__])) {
            $this->markTestSkipped($this->skippedTests[__FUNCTION__]);
        }

        $request = $this->createRequest(
            Method::GET,
            $this->getTestServerUri()
                 ->withPath('/status')
                 ->withQuery('code=' . S::TOO_MANY_REQUESTS)
        );

        $this->expectException(TooManyRequestsException::class);
        $this->getClient()->request($request);
    }

    public function testThrowsRequestHeaderFieldsTooLargeException(): void
    {
        if (isset($this->skippedTests[__FUNCTION__])) {
            $this->markTestSkipped($this->skippedTests[__FUNCTION__]);
        }

        $request = $this->createRequest(
            Method::GET,
            $this->getTestServerUri()
                 ->withPath('/status')
                 ->withQuery('code=' . S::REQUEST_HEADER_FIELDS_TOO_LARGE)
        );

        $this->expectException(RequestHeaderFieldsTooLargeException::class);
        $this->getClient()->request($request);
    }

    public function testThrowsUnavailableForLegalReasonsException(): void
    {
        if (isset($this->skippedTests[__FUNCTION__])) {
            $this->markTestSkipped($this->skippedTests[__FUNCTION__]);
        }

        $request = $this->createRequest(
            Method::GET,
            $this->getTestServerUri()
                 ->withPath('/status')
                 ->withQuery('code=' . S::UNAVAILABLE_FOR_LEGAL_REASONS)
        );

        $this->expectException(UnavailableForLegalReasonsException::class);
        $this->getClient()->request($request);
    }

    public function testThrowsInternalServerErrorException(): void
    {
        if (isset($this->skippedTests[__FUNCTION__])) {
            $this->markTestSkipped($this->skippedTests[__FUNCTION__]);
        }

        $request = $this->createRequest(
            Method::GET,
            $this->getTestServerUri()
                 ->withPath('/status')
                 ->withQuery('code=' . S::INTERNAL_SERVER_ERROR)
        );

        $this->expectException(InternalServerErrorException::class);
        $this->getClient()->request($request);
    }

    public function testThrowsNotImplementedException(): void
    {
        if (isset($this->skippedTests[__FUNCTION__])) {
            $this->markTestSkipped($this->skippedTests[__FUNCTION__]);
        }

        $request = $this->createRequest(
            Method::GET,
            $this->getTestServerUri()
                 ->withPath('/status')
                 ->withQuery('code=' . S::NOT_IMPLEMENTED)
        );

        $this->expectException(NotImplementedException::class);
        $this->getClient()->request($request);
    }

    public function testThrowsServiceUnavailableException(): void
    {
        if (isset($this->skippedTests[__FUNCTION__])) {
            $this->markTestSkipped($this->skippedTests[__FUNCTION__]);
        }

        $request = $this->createRequest(
            Method::GET,
            $this->getTestServerUri()
                 ->withPath('/status')
                 ->withQuery('code=' . S::SERVICE_UNAVAILABLE)
        );

        $this->expectException(ServiceUnavailableException::class);
        $this->getClient()->request($request);
    }

    public function testThrowsGatewayTimeoutException(): void
    {
        if (isset($this->skippedTests[__FUNCTION__])) {
            $this->markTestSkipped($this->skippedTests[__FUNCTION__]);
        }

        $request = $this->createRequest(
            Method::GET,
            $this->getTestServerUri()
                 ->withPath('/status')
                 ->withQuery('code=' . S::GATEWAY_TIMEOUT)
        );

        $this->expectException(GatewayTimeoutException::class);
        $this->getClient()->request($request);
    }

    public function testThrowsHttpVersionNotSupportedException(): void
    {
        if (isset($this->skippedTests[__FUNCTION__])) {
            $this->markTestSkipped($this->skippedTests[__FUNCTION__]);
        }

        $request = $this->createRequest(
            Method::GET,
            $this->getTestServerUri()
                 ->withPath('/status')
                 ->withQuery('code=' . S::HTTP_VERSION_NOT_SUPPORTED)
        );

        $this->expectException(HttpVersionNotSupportedException::class);
        $this->getClient()->request($request);
    }

    public function testThrowsVariantAlsoNegotiatesException(): void
    {
        if (isset($this->skippedTests[__FUNCTION__])) {
            $this->markTestSkipped($this->skippedTests[__FUNCTION__]);
        }

        $request = $this->createRequest(
            Method::GET,
            $this->getTestServerUri()
                 ->withPath('/status')
                 ->withQuery('code=' . S::VARIANT_ALSO_NEGOTIATES)
        );

        $this->expectException(VariantAlsoNegotiatesException::class);
        $this->getClient()->request($request);
    }

    public function testThrowsInsufficientStorageException(): void
    {
        if (isset($this->skippedTests[__FUNCTION__])) {
            $this->markTestSkipped($this->skippedTests[__FUNCTION__]);
        }

        $request = $this->createRequest(
            Method::GET,
            $this->getTestServerUri()
                 ->withPath('/status')
                 ->withQuery('code=' . S::INSUFFICIENT_STORAGE)
        );

        $this->expectException(InsufficientStorageException::class);
        $this->getClient()->request($request);
    }

    public function testThrowsLoopDetectedException(): void
    {
        if (isset($this->skippedTests[__FUNCTION__])) {
            $this->markTestSkipped($this->skippedTests[__FUNCTION__]);
        }

        $request = $this->createRequest(
            Method::GET,
            $this->getTestServerUri()
                 ->withPath('/status')
                 ->withQuery('code=' . S::LOOP_DETECTED)
        );

        $this->expectException(LoopDetectedException::class);
        $this->getClient()->request($request);
    }

    public function testThrowsNotExtendedException(): void
    {
        if (isset($this->skippedTests[__FUNCTION__])) {
            $this->markTestSkipped($this->skippedTests[__FUNCTION__]);
        }

        $request = $this->createRequest(
            Method::GET,
            $this->getTestServerUri()
                 ->withPath('/status')
                 ->withQuery('code=' . S::NOT_EXTENDED)
        );

        $this->expectException(NotExtendedException::class);
        $this->getClient()->request($request);
    }

    public function testThrowsNetworkAuthenticationRequiredException(): void
    {
        if (isset($this->skippedTests[__FUNCTION__])) {
            $this->markTestSkipped($this->skippedTests[__FUNCTION__]);
        }

        $request = $this->createRequest(
            Method::GET,
            $this->getTestServerUri()
                 ->withPath('/status')
                 ->withQuery('code=' . S::NETWORK_AUTHENTICATION_REQUIRED)
        );

        $this->expectException(NetworkAuthenticationRequiredException::class);
        $this->getClient()->request($request);
    }

    abstract protected function getClient(): HttpClientInterface;

    abstract protected function createRequest(
        string $method,
        UriInterface $uri
    ): RequestInterface;
}
