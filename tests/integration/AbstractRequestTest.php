<?php

declare(strict_types=1);

namespace Radiergummi\Wander\Tests\Integration;

use InvalidArgumentException;
use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriFactoryInterface;
use Psr\Http\Message\UriInterface;
use Radiergummi\Wander\Http\Status;
use Radiergummi\Wander\Interfaces\DriverInterface;
use Radiergummi\Wander\Interfaces\HttpClientInterface;
use Radiergummi\Wander\Wander;
use Symfony\Component\Process\Exception\LogicException;
use Symfony\Component\Process\Exception\RuntimeException;
use Symfony\Component\Process\Process;

use function array_map;
use function explode;
use function fwrite;
use function implode;
use function in_array;
use function realpath;
use function sprintf;
use function trim;
use function usleep;

use const PHP_EOL;
use const STDERR;

/**
 * Abstract Request Test
 * =====================
 * This is the base class for all request integration tests. It provides several
 * helper methods, request/response specific assertions and takes care of running
 * the test server. This server is simply a separately running instance of PHP's
 * built-in web server, configured to invoke a single responder script, providing
 * well-defined endpoints we can request to ensure the client does what it should
 * and is standards compliant.
 * The test server output is captured and will be printed to `stderr` if phpunit
 * is running in debug mode (`--debug`). This can be helpful in debugging tests.
 * Take a look at the existing driver tests to learn how to use this.
 *
 * The test server hooks into the standard phpunit life cycle:
 *
 *     ┌─ [::setUpBeforeClass]      Start the server
 *     ├─── [->setUp]               Restart if stopped
 *     ├─── [->tearDown]            Flush output
 *     └─ [::tearDownAfterClass]    Stop the server, flush output
 *
 * @package Radiergummi\Wander\Tests\Integration
 */
abstract class AbstractRequestTest extends TestCase
{
    protected static string $serverListenAddress = '127.0.0.1:9999';

    private static ?Process $serverProcess;

    protected RequestFactoryInterface $requestFactory;

    protected ResponseFactoryInterface $responseFactory;

    protected UriFactoryInterface $uriFactory;

    /**
     * Holds a map of tests to skip, with a reason for why they are skipped
     *
     * @var array<string, string>
     */
    protected array $skippedTests = [];

    /**
     * Spawn the test server before testing starts.
     *
     * @throws LogicException
     * @throws RuntimeException
     */
    public static function setUpBeforeClass(): void
    {
        self::spawnTestServer();
    }

    /**
     * Ensure the test server "output buffer" is flushed, then kill the process.
     *
     * @throws LogicException
     */
    public static function tearDownAfterClass(): void
    {
        if (self::$serverProcess && self::$serverProcess->isRunning()) {
            self::writeIncrementalTestServerOutput();
            self::$serverProcess->stop();
        }
    }

    /**
     * Creates a fresh factory before each test starts and ensures the test
     * server is still running.
     *
     * @throws LogicException
     * @throws RuntimeException
     */
    protected function setUp(): void
    {
        // Respawn the test server if it crashed
        if ( ! self::$serverProcess->isRunning()) {
            self::spawnTestServer();
        }

        $factory = new Psr17Factory();

        $this->uriFactory = $factory;
        $this->requestFactory = $factory;
        $this->responseFactory = $factory;
    }

    /**
     * Write all generated server output to the process output after each test.
     *
     * @throws LogicException
     */
    protected function tearDown(): void
    {
        // If the server crashed during a test, abort
        if ( ! self::$serverProcess->isRunning()) {
            return;
        }

        self::writeIncrementalTestServerOutput();
    }

    /**
     * Spawn a new PHP built-in web server instance that serves the responder
     * script, which provides for a small set of controlled test endpoints we
     * can call.
     *
     * @throws LogicException
     * @throws RuntimeException
     */
    private static function spawnTestServer(): void
    {
        self::$serverProcess = new Process([
            'php',
            '-S',
            self::$serverListenAddress,
            '-t',
            realpath(__DIR__ . '/../_fixtures/'),
            realpath(__DIR__ . '/../_fixtures/responder.php'),
        ]);

        // Start the process "asynchronously"
        self::$serverProcess->start();

        // Sleep for a moment to let the server bootstrap
        usleep(1000000);

        self::writeIncrementalTestServerOutput();
    }

    /**
     * Writes all test server output generated since the last invocation to the
     * stderr stream of the phpunit process, if phpunit has been started with
     * the `--debug` flag.
     *
     * @throws LogicException
     */
    private static function writeIncrementalTestServerOutput(): void
    {
        global $argv;

        // We only want to print server output in debug mode
        if ( ! in_array('--debug', $argv, true)) {
            return;
        }

        // Join stdout and stderr, the distinction has no relevance for us
        $output = self::$serverProcess->getIncrementalOutput() . PHP_EOL;
        $output .= self::$serverProcess->getIncrementalErrorOutput();
        $output = trim($output);

        // No need to print nothing
        if ( ! $output) {
            return;
        }

        // Prefix all output lines with a ">" sign to clarify the source
        $output = implode(
            PHP_EOL,
            array_map(
                fn(string $line): string => "> {$line}",
                explode(PHP_EOL, $output)
            )
        );

        // Write the server output to stderr
        fwrite(STDERR, "[Server Output] \n{$output}\n");
    }

    /**
     * Retrieves the URI the test server is listening on.
     *
     * @return UriInterface
     * @throws InvalidArgumentException
     */
    protected function getTestServerUri(): UriInterface
    {
        return $this->uriFactory
            ->createUri(self::$serverListenAddress)
            ->withScheme('http');
    }

    /**
     * Creates a client instance with the configured driver.
     *
     * @return HttpClientInterface
     */
    protected function getClient(): HttpClientInterface
    {
        return new Wander(
            $this->getDriver(),
            $this->requestFactory,
            $this->responseFactory
        );
    }

    /**
     * Creates a new request using the request factory.
     *
     * @param string       $method
     * @param UriInterface $uri
     *
     * @return RequestInterface
     */
    protected function createRequest(
        string $method,
        UriInterface $uri
    ): RequestInterface {
        return $this->requestFactory->createRequest($method, $uri);
    }

    /**
     * @param ResponseInterface $response
     * @param string|null       $message
     *
     * @throws ExpectationFailedException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    protected function assertStatusCodeOk(
        ResponseInterface $response,
        ?string $message = null
    ): void {
        $this->assertStatusCode(
            Status::OK,
            $response,
            $message
        );
    }

    /**
     * Asserts a response has a given status code.
     *
     * @param int               $expectedStatusCode
     * @param ResponseInterface $response
     * @param string|null       $message
     *
     * @throws ExpectationFailedException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    protected function assertStatusCode(
        int $expectedStatusCode,
        ResponseInterface $response,
        ?string $message = null
    ): void {
        $statusCode = $response->getStatusCode();
        $message = $message ?? sprintf(
                'Expected status code %d, actual is %d',
                $expectedStatusCode,
                $statusCode
            );

        self::assertSame(
            $expectedStatusCode,
            $statusCode,
            $message
        );
    }

    /**
     * Asserts the body of a response is empty.
     *
     * @param ResponseInterface $response
     * @param string|null       $message
     *
     * @throws ExpectationFailedException
     * @throws \RuntimeException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    protected function assertResponseBodyEmpty(
        ResponseInterface $response,
        ?string $message = null
    ): void {
        $this->assertResponseBodyEquals(
            '',
            $response,
            $message
        );
    }

    /**
     * Asserts the body of a response is not empty.
     *
     * @param ResponseInterface $response
     * @param string|null       $message
     *
     * @throws ExpectationFailedException
     * @throws \RuntimeException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    protected function assertResponseBodyNotEmpty(
        ResponseInterface $response,
        ?string $message = null
    ): void {
        $this->assertResponseBodyNotEquals(
            '',
            $response,
            $message
        );
    }

    /**
     * Asserts the string content of a response body equals the given value.
     *
     * @param string            $expectedBody
     * @param ResponseInterface $response
     * @param string|null       $message
     *
     * @throws ExpectationFailedException
     * @throws \RuntimeException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    protected function assertResponseBodyEquals(
        string $expectedBody,
        ResponseInterface $response,
        ?string $message = null
    ): void {
        $stream = $response->getBody();
        $stream->rewind();

        self::assertSame(
            $expectedBody,
            $stream->getContents(),
            $message ?? ''
        );
    }

    /**
     * Asserts the string content of a response body not equals the given value.
     *
     * @param string            $expectedBody
     * @param ResponseInterface $response
     * @param string|null       $message
     *
     * @throws ExpectationFailedException
     * @throws \RuntimeException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    protected function assertResponseBodyNotEquals(
        string $expectedBody,
        ResponseInterface $response,
        ?string $message = null
    ): void {
        self::assertNotSame(
            $expectedBody,
            $response->getBody()->getContents(),
            $message ?? ''
        );
    }

    /**
     * Retrieves the driver instance to use for new client instances.
     *
     * @return DriverInterface
     */
    abstract protected function getDriver(): DriverInterface;
}
