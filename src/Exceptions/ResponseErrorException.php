<?php

namespace Radiergummi\Wander\Exceptions;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
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
use Radiergummi\Wander\Http\Status;

class ResponseErrorException extends WanderException
{
    private const DICTIONARY = [
        Status::BAD_REQUEST                     => BadRequestException::class,
        Status::UNAUTHORIZED                    => UnauthorizedException::class,
        Status::PAYMENT_REQUIRED                => PaymentRequiredException::class,
        Status::FORBIDDEN                       => ForbiddenException::class,
        Status::NOT_FOUND                       => NotFoundException::class,
        Status::METHOD_NOT_ALLOWED              => MethodNotAllowedException::class,
        Status::NOT_ACCEPTABLE                  => NotAcceptableException::class,
        Status::PROXY_AUTHENTICATION_REQUIRED   => ProxyAuthenticationRequiredException::class,
        Status::REQUEST_TIMEOUT                 => RequestTimeoutException::class,
        Status::CONFLICT                        => ConflictException::class,
        Status::GONE                            => GoneException::class,
        Status::LENGTH_REQUIRED                 => LengthRequiredException::class,
        Status::PRECONDITION_FAILED             => PreconditionFailedException::class,
        Status::PAYLOAD_TOO_LARGE               => PayloadTooLargeException::class,
        Status::URI_TOO_LONG                    => UriTooLongException::class,
        Status::UNSUPPORTED_MEDIA_TYPE          => UnsupportedMediaTypeException::class,
        Status::RANGE_NOT_SATISFIABLE           => RangeNotSatisfiableException::class,
        Status::EXPECTATION_FAILED              => ExpectationFailedException::class,
        Status::IM_A_TEAPOT                     => ImATeapotException::class,
        Status::MISDIRECTED_REQUEST             => MisdirectedRequestException::class,
        Status::UNPROCESSABLE_ENTITY            => UnprocessableEntityException::class,
        Status::LOCKED                          => LockedException::class,
        Status::FAILED_DEPENDENCY               => FailedDependencyException::class,
        Status::TOO_EARLY                       => TooEarlyException::class,
        Status::UPGRADE_REQUIRED                => UpgradeRequiredException::class,
        Status::PRECONDITION_REQUIRED           => PreconditionRequiredException::class,
        Status::TOO_MANY_REQUESTS               => TooManyRequestsException::class,
        Status::REQUEST_HEADER_FIELDS_TOO_LARGE => RequestHeaderFieldsTooLargeException::class,
        Status::UNAVAILABLE_FOR_LEGAL_REASONS   => UnavailableForLegalReasonsException::class,
        Status::INTERNAL_SERVER_ERROR           => InternalServerErrorException::class,
        Status::NOT_IMPLEMENTED                 => NotImplementedException::class,
        Status::SERVICE_UNAVAILABLE             => ServiceUnavailableException::class,
        Status::GATEWAY_TIMEOUT                 => GatewayTimeoutException::class,
        Status::HTTP_VERSION_NOT_SUPPORTED      => HttpVersionNotSupportedException::class,
        Status::VARIANT_ALSO_NEGOTIATES         => VariantAlsoNegotiatesException::class,
        Status::INSUFFICIENT_STORAGE            => InsufficientStorageException::class,
        Status::LOOP_DETECTED                   => LoopDetectedException::class,
        Status::NOT_EXTENDED                    => NotExtendedException::class,
        Status::NETWORK_AUTHENTICATION_REQUIRED => NetworkAuthenticationRequiredException::class,
    ];

    protected RequestInterface $request;

    protected ResponseInterface $response;

    final public function __construct(
        RequestInterface $request,
        ResponseInterface $response
    ) {
        $this->request = $request;
        $this->response = $response;

        parent::__construct(
            "Request failed with status {$response->getStatusCode()}",
            $response->getStatusCode()
        );
    }

    /**
     * Resolves the type of exception to throw based on the status of a response
     *
     * @param RequestInterface  $request
     * @param ResponseInterface $response
     *
     * @return ResponseErrorException
     */
    public static function create(
        RequestInterface $request,
        ResponseInterface $response
    ): ResponseErrorException {
        $statusCode = $response->getStatusCode();

        /** @var class-string<ResponseErrorException> $exceptionType */
        $exceptionType = self::DICTIONARY[$statusCode] ?? self::class;

        return new $exceptionType(
            $request,
            $response
        );
    }

    public function getRequest(): RequestInterface
    {
        return $this->request;
    }

    public function getResponse(): ResponseInterface
    {
        return $this->response;
    }
}
