# Query API

Usage example:

```php
use Neo4j\QueryAPI\Neo4jQueryAPI;
use Neo4j\QueryAPI\Objects\Authentication;

$client = Neo4jQueryAPI::login('https://myaddress.com', Authentication::bearer('mytokken'))
```