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
 - **Compatible with other solutions**  
   As drivers are, essentially, PSR-18 clients, you can swap in any other client library and make it work out of the box. This provides for a smooth migration path.

```php
$responseBody = (new Wander())
    ->patch('https://example.com')
    ->withQueryParameter('foo', 'bar')
    ->withAuthorization('Bearer', getenv('TOKEN'))
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

Reference
---------
This reference shows all available methods.

> (TBD)

Contributing
------------
All contributions are welcome, but please be aware of a few requirements:
 - We use [psalm](https://psalm.dev/) for static analysis and would like to keep the level at _at least_ 2 (but would like to reach 1 in the long run). Any PR with
   degraded analysis results will not be accepted. To run psalm, use `composer run static-analysis`.
 - Unit and integration tests must be supplied with every PR. To run all test suites, use `composer run test`.
