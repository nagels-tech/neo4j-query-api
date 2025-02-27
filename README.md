# Neo4jQueryAPI client

## Interact programmatically with Top Graph Technology

- Easy configuration to pick and choose drivers
- An intuitive API for smooth query execution
- Extensibility for custom use cases
- Built and tested under close collaboration with the official Neo4j driver team
- Easier to start with, just need a client to any neo4j instance
- Fully typed with Psalm and CS fixed for code quality
- It does not supports Bolt, Rather compatible with HTTP, and auto-routed drivers



## Installation

You can install the package via Composer:

```sh
composer require this-repo/neo4j-client
```

## Usage

### Connecting to Neo4j


```php
use Neo4j\QueryAPI\Neo4jQueryAPI;
use Neo4j\QueryAPI\Authentication\AuthenticateInterface;

$client = Neo4jQueryAPI::login('http://localhost:7474', new AuthenticateInterface('username', 'password'));
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
| Local DateTime     |                                                     |
| Local Time         |                                                     |
| Zoned DateTime     |                                                     |
| Zoned Time         |                                                     |
| Duration           | ``` * string  ``` (will be upgraded in version 1.1) |
| WGS 84 2D Point    | ``` * string  ``` (will be upgraded in version 1.1) |
| WGS 84 3D Point    | ``` * string  ``` (will be upgraded in version 1.1) |
| Cartesian 2D Point | ``` * string  ``` (will be upgraded in version 1.1) |
| Cartesian 3D Point | ``` * string  ``` (will be upgraded in version 1.1) |
| Map                |                  ``` * string  ```                  |

## Diving deeper:

| Feature   |      Supported?    | 
|----------|:-------------:|
| Authentication |  Yes |
| Transaction |    Yes   |
| HTTP | Yes |
| Cluster |  Yes |
| Aura |    Partly (recent versions) | 
| Bookmarks | Yes |

> **_NOTE:_**  It supports neo4j databases versions > 5.25 (which has QueryAPI enabled.)



## Contributing

Please see CONTRIBUTING for details.

## Security

If you discover any security-related issues, please email *security@nagels.tech* instead of using the issue tracker.

## Credits

- Created with ❤️ by Nagels
- [All Contributors](https://github.com/your-repo/neo4j-client/graphs/contributors)

## License

The MIT License (MIT). Please see License File for more information.