# laravel-seb

This Laravel-Package provides the possibility to generate encrypted [SEB](https://safeexambrowser.org/news_de.html) (Safe Exam Browser) configurations.
its mainly used to generate automatic seb-exams configurations with secure defaults options.

## Requirements
Laravel 8 with php8 and enabled ext-openssl, ext-zlib

## Limititations
Its can only be used for password-encrypted exams (client-configurations currently are not supported)

## Installation
Install via Composer.

```bash
$ composer require ndum/laravel-seb
```

## Examples

**_NOTE:_** For production - Please use secure passwords and a secure seb-config!

### Parameters

```
$sebConfig = seb-config as json-array (show examples/example_seb_config.json)
$startPassword = encryption-password as string
$quitPassword = close-password as string
$adminPassword = admin-password as string
```

##### Traditionally:

```php
use Ndum\Laravel\SebConfigGenerator;
use Storage;

$config = Storage::disk('local')->get('/examples/example_seb_config.json'); // just as example...
$startPassword = 'test';
$quitPassword = 'test';
$adminPassword = 'test';

$sebConfig = json_decode($config, true);

$generator = new SebConfigGenerator();
$encryptedSebConfig = $generator->createSebConfig($sebConfig, $startPassword, $quitPassword, $adminPassword);
dd($encryptedSebConfig);
```

##### Facade:

```php
use Ndum\Laravel\Facades\SebConfigGenerator;
use Storage;

$config = Storage::disk('local')->get('/examples/example_seb_config.json'); // just as example...
$startPassword = 'test';
$quitPassword = 'test';
$adminPassword = 'test';

$sebConfig = json_decode($config, true);

$encryptedSebConfig = SebConfigGenerator::createSebConfig($sebConfig, $startPassword, $quitPassword, $adminPassword);
dd($encryptedSebConfig);
```

## Issues / Contributions
Directly via [GitHub](https://github.com/ndum/laravel-seb/issues)

## License
This project is licensed under the MIT License - see the [LICENSE-File](LICENSE) for details
