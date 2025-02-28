# Neo4j Query API client

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

If you plan on using the HTTP drivers, make sure you have psr-7, psr-17, and psr-18 implementations included in your project. 
If you don't have any, you can install them via Composer:

```sh
composer require psr/http-message psr/http-factory psr/http-client
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
| Cluster |  Partly*   |
| Aura |    Yes     | 
| Bookmarks |    Yes     |
| Bolt |     No     |

> **_NOTE:_**  It supports neo4j databases versions > 5.25 or Neo4j Aura (which has QueryAPI enabled.)
> \* Client side routing is only supported in the Neo4j driver



## Contributing

Please see CONTRIBUTING for details.

## Security

If you discover any security-related issues, please email *security@nagels.tech* instead of using the issue tracker.

## Credits

- Created with ❤️ by Nagels
- [Ghlen Nagels](https://www.linkedin.com/in/ghlen/), [Kiran Chandani](https://www.linkedin.com/in/kiran-chandani-5628a1213/), [Pratiksha Zalte]()

## License

The MIT License (MIT). Please see License File for more information.