# Neo4j Query API client


![License](https://img.shields.io/github/license/nagels-tech/neo4j-query-api)
![Version](https://img.shields.io/github/v/release/nagels-tech/neo4j-query-api)
[![Codacy Badge](https://app.codacy.com/project/badge/Grade/2cb8a1e71ed04987b1c763a09e196c84)](https://app.codacy.com/gh/nagels-tech/neo4j-query-api/dashboard?utm_source=gh&utm_medium=referral&utm_content=&utm_campaign=Badge_grade)
[![codecov](https://codecov.io/github/nagels-tech/neo4j-query-api/graph/badge.svg?token=NTHCFY38D7)](https://codecov.io/github/nagels-tech/neo4j-query-api)

## Interact programmatically with Top Graph Technology

- Easy to start with, just build your client in one line and start running queries
- Use an intuitive API for smooth query execution
- Built and tested under close collaboration with the official Neo4j driver team
- Fully typed with Psalm and CS fixed for code quality
- Uses HTTP under the hood instead of bolt
- Small, lightweight, well maintained and fully tested codebase


## Installation

You can install the package via Composer:

```sh
composer require neo4j-php/query-api
```

## Client Installation

This client uses the HTTP protocol, make sure you have psr-7, psr-17, and psr-18 implementations included in your project. 
If you don't have any, you can install one of the many options via Composer:

```sh
composer require guzzlehttp/guzzle
```

> **_NOTE:_**  PSR-17 and PSR-18 are essential for HTTP client communication. Other compatible clients like Guzzle can also be used.
> \* [PSR auto-discovery](https://docs.php-http.org/en/latest/discovery.html) will detect the installed HTTP client automatically.

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

## Testing

To run the tests, execute the following command:

```sh
vendor/bin/phpunit
```

Cypher values and types map to these php types and classes:

| Cypher             |                         PHP                         |
|--------------------|:---------------------------------------------------:|
| List               |                    ```* array```                    |
| Integer            |                    ``` * int ```                    |
| Float              |                   ``` * float ```                   |
| Boolean            |                   ``` * bool ```                    |
| Null               |                   ``` * null ```                    |
| String             |                  ``` * string  ```                  |
| Array              |                    ```* array```                    |
| Local DateTime     | ``` * string  ``` (will be upgraded in version 1.1) |
| Local Time         | ``` * string  ``` (will be upgraded in version 1.1) |
| Zoned DateTime     | ``` * string  ``` (will be upgraded in version 1.1) |
| Zoned Time         | ``` * string  ``` (will be upgraded in version 1.1) |
| Duration           | ``` * string  ``` (will be upgraded in version 1.1) |
| WGS 84 2D Point    |           `Neo4j\QueryAPI\Objects\Point`            |
| WGS 84 3D Point    |           `Neo4j\QueryAPI\Objects\Point`            |
| Cartesian 2D Point |           `Neo4j\QueryAPI\Objects\Point`            |
| Cartesian 3D Point |           `Neo4j\QueryAPI\Objects\Point`            |
| Map                |                  ``` * array  ```                   |
| Node               |         ```Neo4j\QueryAPI\Objects\Node ```          |
| Relationship       |     ```Neo4j\QueryAPI\Objects\Relationship  ```     |
| Path               |      ```Neo4j\QueryAPI\Objects\Relationship```      |

## Diving deeper:

| Feature   | Supported? | 
|----------|:----------:|
| Authentication |    Yes     |
| Transaction |    Yes     |
| HTTP |    Yes     |
| Cluster |  Partly *  |
| Aura |    Yes     | 
| Bookmarks |    Yes     |
| Bolt |     No     |

> \* Client side routing is only supported in the Neo4j driver

 **_NOTE:_**  *_It supports neo4j databases versions > 5.25 or Neo4j Aura (which has QueryAPI enabled.)_*

## Contributing

Please see [CONTRIBUTING.md](./Contributing.md) for details.

## Security

If you discover any security-related issues, please email *security@nagels.tech* instead of using the issue tracker.

## Credits

- Created with ❤️ by Nagels
- [Kiran Chandani](https://www.linkedin.com/in/kiran-chandani-5628a1213/), 
- [Pratiksha Zalte](https://github.com/p123-stack),
- [Ghlen Nagels](https://www.linkedin.com/in/ghlen/)

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.