<h2 align="center">
    <strong>The Websocket Client For PHP.</strong>
</h2>

<p align="center">
    <a href="https://github.com/awuxtron/websocket/blob/main/README.md"><img alt="License" src="https://img.shields.io/github/license/awuxtron/websocket?style=flat-square"></a>
    <a href="https://php.net"><img alt="PHP Version Support" src="https://img.shields.io/packagist/php-v/awuxtron/websocket?style=flat-square"></a>
    <a href="https://packagist.org/packages/awuxtron/websocket"><img alt="Packagist Version" src="https://img.shields.io/packagist/v/awuxtron/websocket?style=flat-square"></a>
    <a href="https://packagist.org/packages/awuxtron/websocket"><img alt="Packagist Downloads" src="https://img.shields.io/packagist/dt/awuxtron/websocket?style=flat-square"></a>
    <a href="https://github.com/awuxtron/websocket/actions/workflows/fix-code-style.yml"><img alt="Code style fix status" src="https://img.shields.io/github/workflow/status/awuxtron/websocket/fix-code-style?label=code%20style&style=flat-square"></a>
    <a href="https://github.com/awuxtron/websocket/actions/workflows/analyse.yml"><img alt="Code Static Analysis Status" src="https://img.shields.io/github/workflow/status/awuxtron/websocket/analyse?label=analyse&style=flat-square"></a>
    <a href="https://github.com/awuxtron/websocket/actions/workflows/run-tests.yml"><img alt="GitHub Workflow Status" src="https://img.shields.io/github/workflow/status/awuxtron/websocket/run-tests?label=tests&style=flat-square"></a>
</p>

## Installation

You can install the package via composer:

```bash
composer require awuxtron/websocket
```

## Usage

```php
use Awuxtron\Websocket\Client;
use Awuxtron\Websocket\Enums\CloseStatus;

$client = new Client('ws://localhost:8080');
$socket = $client->connect();

$socket->send('hello');

echo $socket->read();

$socket->close(CloseStatus::NORMAL, 'Success');

```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](../../.github/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Awuxtron](https://github.com/awuxtron)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
