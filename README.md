# request
Route request handler.

```php
$routes = function(\FastRoute\RouteCollector $r) {

    $r->get('/', IndexHandler::class);
    $r->post('/', PostHandler::class);

};

$headers = [
    'Access-Control-Allow-Origin' => '*',
    'Cache-Control' => 'no-store'
];

new \drhino\Request\Handler($routes, $headers);
```
