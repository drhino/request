# request
Route request handler.

```bash
composer require drhino/request
```

```php
<?php declare(strict_types=1);

# require __DIR__ . '/vendor/autoload.php';

$routes = function(\FastRoute\RouteCollector $r) {

    $r->get('/{name}', drhino\Request\Example\IndexHandler::class);

    $r->post('/', drhino\Request\Example\PostHandler::class);

};

// Default headers, unless otherwise specified in the response.
$headers = [
    'Access-Control-Allow-Origin' => '*',
    'Cache-Control' => 'no-store'
];

new \drhino\Request\Handler($routes, $headers);
```
