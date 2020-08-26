Wander
======
> A modern, lightweight, fast and type-safe HTTP client for PHP

> **Note:** I'm still actively working on Wander, and it's barely working right now. Help out if you'd like to, but don't use it yet.

Introduction
------------
Making HTTP requests in PHP is a pain. Either you go full-low-level mode and use streams or curl, or you'll need to use one of the existing choices with array-based
configuration, lots of overhead, or missing PSR compatibility. Wander solves all of those.

### Features
 - **Simple, discoverable API**
   Wander exposes _all_ request options as chainable methods.
 - **Fully standards compliant**
   Wander relies on PSR-17 factories, PSR-18 client drivers and PSR-7 requests/responses. Use our implementations of choice
   ([nyholm/psr7](https://github.com/nyholm/psr7)) or bring your own.
 - **Pluggable serialization**
   Request bodies are serialized depending on the content type, transparently and automatically. Use a format we
   don't know yet? Use your own (and submit a PR!).
 - **Compatible with other solutions**
   As drivers are, essentially, PSR-18 clients, you can swap in any other client library and make it work out of the box. This provides for a smooth migration path.
 - **Extensive exceptions**
   Wander throws several exceptions, all of which follow a clear inheritance structure. This makes it
   exceptionally easy to handle errors as coarsly or fine-grained as necessary.

```php
$responseBody = (new Wander())
    ->patch('https://example.com')
    ->withQueryParameter('foo', 'bar')
    ->withBasicAuthorization('User', 'Pass')
    ->withHeaders([
        Header::ACCEPT => MediaType::APPLICATION_JSON,
    ])
    ->withoutHeader(Header::USER_AGENT)
    ->run()
    ->getBody()
    ->getContents();
```

Installation
------------
Install using composer:
```bash
composer require radiergummi/wander
```

Usage
-----
The following section provides usage several, concrete examples. For a full reference, view the [reference section](#reference).

### Request shorthands
Wander has several layers of shorthands built in, which make working with it as simple as possible. To perform a simple `GET` request, the following is enough:
```php
use Radiergummi\Wander\Wander;

$client = new Wander();
$response = $client
    ->get('https://example.com')
    ->run();
```

Wander has several shorthands for common HTTP methods on the `Wander` object (`GET`, `PUT`, `POST`, `DELETE` and so on).

### Creating request contexts
A slightly longer version of the above example, using the `createContext` method the shorthands also use internally:
```php
use Radiergummi\Wander\Http\Method;
use Radiergummi\Wander\Wander;

$client = new Wander();
$response = $client
    ->createContext(Method::GET, 'https://example.com')
    ->run();
```

This context being created here wraps around [PSR-7 requests](https://www.php-fig.org/psr/psr-7/) and adds a few helper methods, making it possible to chain the
method calls. Doing so requires creating request instances, which of course relies on a [PSR-17 factory](https://www.php-fig.org/psr/psr-17/) you can swap out for
your own. More on that below.

### Sending PSR-7 requests directly
Wander also supports direct handling of request instances:
```php
use Nyholm\Psr7\Request;
use Radiergummi\Wander\Http\Method;
use Radiergummi\Wander\Wander;

$client = new Wander();
$request = new Request(Method::GET, 'https://example.com');
$response = $client->request($request);
```

### Using a custom driver
Drivers are what actually handles dispatching requests and processing responses. They have one, simple responsibility: Transform a request instance into a response
instance. By default, Wander uses a PHP stream driver.

### Exception handling
Wander follows an exception hierarchy that represents different classes of errors.
In contrary to PSR-18 clients, I firmly believe response status codes from the 400 or 500 range _should_ throw
an exception, because you end up checking for them anyway. Exceptions are friends! Especially in thee case of
HTTP, where an error be an expected part of the flow.

The exception tree looks as follows:
```
 WanderException (inherits from \RuntimeException)
  ├─ ClientException (implements PSR-18 ClientExceptionInterface)
  ├─ DriverException (implements PSR-18 RequestExceptionInterface)
  ├─ ConnectionException (implements PSR-18 NetworkExceptionInterface)
  ├─ SslCertificateException (implements PSR-18 NetworkExceptionInterface)
  ├─ UnresolvableHostException (implements PSR-18 NetworkExceptionInterface)
  └─ ResponseErrorException
      ├─ ClientErrorException
      │   ├─ BadRequestException
      │   ├─ UnauthorizedException
      │   ├─ PaymentRequiredException
      │   ├─ ForbiddenException
      │   ├─ NotFoundException
      │   ├─ MethodNotAllowedException
      │   ├─ NotAcceptableException
      │   ├─ ProxyAuthenticationRequiredException
      │   ├─ RequestTimeoutException
      │   ├─ ConflictException
      │   ├─ GoneException
      │   ├─ LengthRequiredException
      │   ├─ PreconditionFailedException
      │   ├─ PayloadTooLargeException
      │   ├─ UriTooLongException
      │   ├─ UnsupportedMediaTypeException
      │   ├─ RequestedRangeNotSatisfyableException
      │   ├─ ExpectationFailedException
      │   ├─ MisdirectedRequestException
      │   ├─ UpgradeRequiredException
      │   ├─ PreconditionRequiredException
      │   ├─ TooManyRequestsException
      │   ├─ RequestHeaderFieldsTooLargeException
      │   └─ UnavailableForLegalReasonsException
      └─ ServerErrorException
          ├─ InternalServerErrorException
          ├─ NotImplementedException
          ├─ BadGatewayException
          ├─ ServiceUnavailableException
          ├─ GatewayTimeoutException
          ├─ HTTPVersionNotSupportedException
          ├─ VariantAlsoNegotiatesException
          └─ NetworkAuthenticationRequiredException
```

All response error exceptions provide getters for the request and response instance, so you can do stuff like
this easily:
```php
try {
  $request->run();
} catch (UnauthorizedException | ForbiddenException $e) {
  $this->refreshAccessToken();

  return $this->retry();
} catch (GoneException $e) {
  throw new RecordDeletedExeption(
    $e->getRequest()->getUri()->getPath()
  );
} catch (BadRequestException $e) {
  $responseBody = $e->getResponse()->getBody()->getContents();
  $error = json_decode($responseBody, JSON_THROW_ON_ERROR);
  $field = $error['field'] ?? null;

  if ($field) {
    throw new ValidatorException("Failed to validate {$field}");
  }

  throw new UnknownException($error);
} catch (WanderException $e) {

  // Simply catch all others
  throw new RuntimeException(
    'Server returned an unknown error: ' .
    $e->getResponse()->getBody()->getContents()
  );
}
```
This was just one of a myriad of ways to handle errors with these kinds of exceptions!

Reference
---------
This reference shows all available methods.

### Wander: HTTP Client
This section describes all methods of the HTTP client itself. When creating a new instance, you can pass several
dependencies:

**Signature:**
```php
new Wander(
    DriverInterface $driver = null,
    ?RequestFactoryInterface $requestFactory = null,
    ?ResponseFactoryInterface $responseFactory = null
)
```

**Parameters:**
| Parameter          | Type                       | Required | Description                                     |
|:-------------------|:---------------------------|:---------|:------------------------------------------------|
| `$driver`          | `DriverInterface`          | No       | Underlying HTTP client driver. Defaults to curl |
| `$requestFactory`  | `RequestFactoryInterface`  | No       | PSR-17 request factory                          |
| `$responseFactory` | `ResponseFactoryInterface` | No       | PSR-17 response factory                         |

#### `get`: Create Context Shorthand
Creates a new request context for a `GET` request.

**Signature:**
```php
get(UriInterface|string $uri): Context
```

**Parameters:**
| Parameter | Type                       | Required | Description                                |
|:----------|:---------------------------|:---------|:-------------------------------------------|
| `$uri`    | `string` or `UriInterface` | Yes      | URI instance or string to create one from. |

#### `post`: Create Context Shorthand
Creates a new request context for a `POST` request.

**Signature:**
```php
post(UriInterface|string $uri, ?mixed $body = null): Context
```

**Parameters:**
| Parameter | Type                       | Required | Description                                |
|:----------|:---------------------------|:---------|:-------------------------------------------|
| `$uri`    | `string` or `UriInterface` | Yes      | URI instance or string to create one from. |
| `$body`   | Any type                   | No       | Data to use as the request body.           |

#### `put`: Create Context Shorthand
Creates a new request context for a `PUT` request.

**Signature:**
```php
put(UriInterface|string $uri, ?mixed $body = null): Context
```

**Parameters:**
| Parameter | Type                       | Required | Description                                |
|:----------|:---------------------------|:---------|:-------------------------------------------|
| `$uri`    | `string` or `UriInterface` | Yes      | URI instance or string to create one from. |
| `$body`   | Any type                   | No       | Data to use as the request body.           |

#### `patch`: Create Context Shorthand
Creates a new request context for a `PATCH` request.

**Signature:**
```php
patch(UriInterface|string $uri, ?mixed $body = null): Context
```

**Parameters:**
| Parameter | Type                       | Required | Description                                |
|:----------|:---------------------------|:---------|:-------------------------------------------|
| `$uri`    | `string` or `UriInterface` | Yes      | URI instance or string to create one from. |
| `$body`   | Any type                   | No       | Data to use as the request body.           |

#### `delete`: Create Context Shorthand
Creates a new request context for a `DELETE` request.

**Signature:**
```php
delete(UriInterface|string $uri, ?mixed $body = null): Context
```

**Parameters:**
| Parameter | Type                       | Required | Description                                |
|:----------|:---------------------------|:---------|:-------------------------------------------|
| `$uri`    | `string` or `UriInterface` | Yes      | URI instance or string to create one from. |

#### `head`: Create Context Shorthand
Creates a new request context for a `HEAD` request.

**Signature:**
```php
head(UriInterface|string $uri, ?mixed $body = null): Context
```

**Parameters:**
| Parameter | Type                       | Required | Description                                |
|:----------|:---------------------------|:---------|:-------------------------------------------|
| `$uri`    | `string` or `UriInterface` | Yes      | URI instance or string to create one from. |

#### `options`: Create Context Shorthand
Creates a new request context for a `OPTIONS` request.

**Signature:**
```php
options(UriInterface|string $uri, ?mixed $body = null): Context
```

**Parameters:**
| Parameter | Type                       | Required | Description                                |
|:----------|:---------------------------|:---------|:-------------------------------------------|
| `$uri`    | `string` or `UriInterface` | Yes      | URI instance or string to create one from. |

#### `createContext`
Allows creation of a new request context for an arbitrary request method.

**Signature:**
```php
createContext(string $method, UriInterface|string $uri): Context
```

**Parameters:**
| Parameter | Type                       | Required | Description                                |
|:----------|:---------------------------|:---------|:-------------------------------------------|
| `$method` | `string`                   | Yes      | Any request method, case sensitive.        |
| `$uri`    | `string` or `UriInterface` | Yes      | URI instance or string to create one from. |

#### `createContextFromRequest`
Allows creation of a new request context from an existing request instance.

**Signature:**
```php
createContextFromRequest(RequestInterface $request): Context
```

**Parameters:**
| Parameter | Type                       | Required | Description                                           |
|:----------|:---------------------------|:---------|:------------------------------------------------------|
| `$request` | `RequestInterface`        | Yes      | Existing request instance to create the context from. |

#### `request`
Dispatches a request instance on the client instances driver and returns the response.

**Signature:**
```php
request(RequestInterface $request): ResponseInterface
```

**Parameters:**
| Parameter  | Type                      | Required | Description          |
|:-----------|:--------------------------|:---------|:---------------------|
| `$request` | `RequestInterface`        | Yes      | Request to dispatch. |





### Context: Request context
The context object performs transformations on an underlying request instance. In spirit with PSR-7, the request
is of course immutable. The context will only keep reference to the current instance.
This allows us to chain all method calls and dispatch requests, all without leaving "the chain" even once. We
can also add helper methods and keep references to other objects--like the client itself, for example--making it
very easy to use and extend.
Note that you should rely on the client creating contexts for you; using the constructor manually is
discouraged.

**Signature:**
```php
new Context(
    HttpClientInterface $client,
    RequestInterface $request
)
```

**Parameters:**
| Parameter | Type                  | Required | Description                                        |
|:----------|:----------------------|:---------|:---------------------------------------------------|
| `$client` | `HttpClientInterface` | Yes      | HTTP client instance to dispatch the request with. |
| `$request` | `RequestInterface`   | Yes      | Request as created by our request factory.         |

#### `setRequest`
Replaces the request instance.

#### `getRequest`
Retrieves the request instance.

#### `withMethod`
Replaces the HTTP request method.

#### `getMethod`
Retrieves the HTTP request method.

#### `withUri`
Replaces the URI instance.

#### `getUri`
Retrieves the URI instance.

#### `withQueryString`
Adds a query string to the URI.

#### `getQueryString`
Retrieves the query string from the URI.

#### `withQueryParameters`
Adds multiple query parameters to the URI.

#### `getQueryParameters`
Retrieves all query parameters from the URI as a dictionary.

#### `withQueryParameter`
Adds a query parameter to the URI.

#### `withoutQueryParameter`
Removes a single query parameter from the URI.

#### `getQueryParameter`
Retrieves a single query parameter from the URI by name.

#### `withHeaders`
Adds multiple headers to the request.

#### `getHeaders`
Retrieves all request headers as a dictionary. Proxy to the PSR-7 request method.

#### `withHeader`
Adds a given header to the request. Proxy to the PSR-7 request method.

#### `withoutHeader`
Removes a given header if it is set on the request. Proxy to the PSR-7 request method.

#### `getHeader`
Retrieves an array of all header values. Proxy to the PSR-7 request method.

#### `getHeaderLine`
Retrieves all header values, delimited by a comma, as a single string. Proxy to the PSR-7 request method.

#### `withAuthorization`
Sets the `Authorization` header to the given authentication type and credentials.

#### `withBasicAuthorization`
Sets the `Authorization` header to the type `Basic` and encodes the comma-delimited credentials as Base64.

#### `withBearerAuthorization`
Sets the `Authorization` header to the type `Bearer` and uses the token for the credentials.

#### `withContentType`
Sets the `Content-Type` header.

#### `getContentType`
Retrieves the value of the `Content-Type` header if set, returns `null` otherwise.

#### `asJson`
Sets the `Content-Type` header to JSON (`application/json`).

#### `asXml`
Sets the `Content-Type` header to XML (`text/xml`).

#### `asPlainText`
Sets the `Content-Type` header to plain text (`text/plain`).

#### `withBody`
Sets the (unserialized) body data on the context. This will be serialized according to the `Content-Type` header
before dispatching the request, taking care of serialization automatically, so you don't have to.
By passing a Stream instance, this process will be skipped in the body will be set on the request as-is.

#### `getBody`
Retrieves the current body data.

#### `hasBody`
Checks whether the context has any body data.

#### `run`
Dispatches the request to the client instance.

Contributing
------------
All contributions are welcome, but please be aware of a few requirements:
 - We use [psalm](https://psalm.dev/) for static analysis and would like to keep the level at _at least_ 2 (but would like to reach 1 in the long run). Any PR with
   degraded analysis results will not be accepted. To run psalm, use `composer run static-analysis`.
 - Unit and integration tests must be supplied with every PR. To run all test suites, use `composer run test`.
