Veloce
===

[![Build Status](https://travis-ci.org/Atriedes/veloce.svg?branch=master)](https://travis-ci.org/Atriedes/veloce)

Veloce is combination between [slim microframework](https://github.com/slimphp/Slim/) and [swoole http server](https://github.com/swoole/swoole-src). Inspired by [espresso](https://github.com/reactphp/espresso)

Install
===

```
composer require jowy/veloce
```

Example
===

```php
<?php
require 'vendor/autoload.php';

$app = new Slim\App();

$app->get('/hello/{name}', function ($request, $response, $args) {
    $response->write("Hello, " . $args['name']);
    return $response;
});

$stack = new \Veloce\Stack($app);

$stack->listen(8000);
```

Test
===

You have to install development dependencies in order to run test.

`php vendor/bin/codecept run`

License
===

MIT, see LICENSE