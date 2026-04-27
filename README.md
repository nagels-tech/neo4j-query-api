# Neo4j Query API client

![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)
![PHP](https://img.shields.io/packagist/php-v/neo4j-php/query-api)
![Version](https://img.shields.io/github/v/release/nagels-tech/neo4j-query-api)
[![Codacy Badge](https://app.codacy.com/project/badge/Grade/2cb8a1e71ed04987b1c763a09e196c84)](https://app.codacy.com/gh/nagels-tech/neo4j-query-api/dashboard?utm_source=gh&utm_medium=referral&utm_content=&utm_campaign=Badge_grade)
[![codecov](https://codecov.io/github/nagels-tech/neo4j-query-api/graph/badge.svg?token=NTHCFY38D7)](https://codecov.io/github/nagels-tech/neo4j-query-api)
![Packagist Downloads](https://img.shields.io/packagist/dt/neo4j-php/query-api)

PHP client for running Cypher against Neo4j using the [Query API](https://neo4j.com/docs/query-api/current/). Package: [Packagist](https://packagist.org/packages/neo4j-php/query-api) · Source: [GitHub](https://github.com/nagels-tech/neo4j-query-api).

## Requirements

- **PHP** 8.1 or newer (see `composer.json`).
- **Neo4j** 5.25+ with the Query API enabled, or **Neo4j Aura** with Query API enabled.
- **HTTP stack:** PSR-7, PSR-17, and PSR-18 implementations in your application (see below).

## Why use this client

- Easy to get started: build the client in one line and run queries.
- Intuitive API for query execution.
- Built and tested in close collaboration with the official Neo4j driver team.
- Fully typed with Psalm and PHP-CS-Fixer for consistent code style.
- Uses HTTP instead of Bolt.
- Small, lightweight, well maintained, and fully tested.

## Installation

Install the package with Composer:

```sh
composer require neo4j-php/query-api
```

## HTTP client dependencies

This client uses HTTP; your project must provide PSR-7, PSR-17, and PSR-18 implementations. If you do not already have them, install a compatible stack, for example:

```sh
composer require guzzlehttp/guzzle
```

> **Note:** PSR-17 and PSR-18 are required for HTTP communication. Other PSR-18 clients work as well as Guzzle.
>
> - [PHP-HTTP discovery](https://docs.php-http.org/en/latest/discovery.html) detects an installed HTTP client when available.

## Usage

### Connecting to Neo4j

```php
use Neo4j\QueryAPI\Neo4jQueryAPI;
use Neo4j\QueryAPI\Objects\Authentication;

$client = Neo4jQueryAPI::login('http://localhost:7474', Authentication::basic('username', 'password'));
```

### Running a Query

```php
$query = 'MATCH (n) RETURN n';
$result = $client->run($query);

foreach ($result as $record) {
    print_r($record);
}
```

### Transactions

#### Begin a Transaction

```php
$transaction = $client->beginTransaction();
```

#### Run a Query in a Transaction

```php
$query = 'CREATE (n:Person {name: $name}) RETURN n';
$parameters = ['name' => 'John Doe'];
$result = $transaction->run($query, $parameters);
```

#### Commit a Transaction

```php
$transaction->commit();
```

#### Rollback a Transaction

```php
$transaction->rollback();
```

## Cypher type mapping

Cypher values map to these PHP types and classes:

| Cypher             | PHP |
|--------------------|-----|
| List               | `array` |
| Integer            | `int` |
| Float              | `float` |
| Boolean            | `bool` |
| Null               | `null` |
| String             | `string` |
| Array              | `array` |
| Local DateTime     | `string` (richer types planned for 1.1) |
| Local Time         | `string` (richer types planned for 1.1) |
| Zoned DateTime     | `string` (richer types planned for 1.1) |
| Zoned Time         | `string` (richer types planned for 1.1) |
| Duration           | `string` (richer types planned for 1.1) |
| WGS 84 2D Point    | `Neo4j\QueryAPI\Objects\Point` |
| WGS 84 3D Point    | `Neo4j\QueryAPI\Objects\Point` |
| Cartesian 2D Point | `Neo4j\QueryAPI\Objects\Point` |
| Cartesian 3D Point | `Neo4j\QueryAPI\Objects\Point` |
| Map                | `array` |
| Node               | `Neo4j\QueryAPI\Objects\Node` |
| Relationship       | `Neo4j\QueryAPI\Objects\Relationship` |
| Path               | `Neo4j\QueryAPI\Objects\Path` |

## Testing

Run the test suite:

```sh
vendor/bin/phpunit
```

Other suites are available via the `scripts` section in `composer.json` (for example `unit-tests`, `integration-tests`, and `all-tests`).

## Feature support

| Feature          | Supported? |
|------------------|:----------:|
| Authentication | Yes        |
| Transaction      | Yes        |
| HTTP             | Yes        |
| Cluster          | Partly†    |
| Aura             | Yes        |
| Bookmarks        | Yes        |
| Bolt             | No         |

† Client-side routing is only supported in the official Neo4j driver.

## Contributing

Please see [Contributing.md](./Contributing.md) for details.

## Security

If you discover any security-related issues, please email *security@nagels.tech* instead of using the issue tracker.

## Credits

- Created with ❤️ by Nagels
- [Kiran Chandani](https://www.linkedin.com/in/kiran-chandani-5628a1213/)
- [Pratiksha Zalte](https://github.com/p123-stack)
- [Ghlen Nagels](https://www.linkedin.com/in/ghlen/)

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
