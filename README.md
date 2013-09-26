# Installation

## Composer installation
```json

```

## Application installation and configuration
Edit your public/index.php file to replace 
```php
Zend\Mvc\Application::init(require 'config/application.config.php')->run();
```
with
```php
DbuApplication\Application::init(require 'config/application.config.php')->run();
```

and append to 'service_manager.factories' section of your application.config.php the folowing row:
```php
'ApplicationManager' => 'DbuApplication\Service\ApplicationManagerFactory'
```
like this:

```php
    'service_manager' => array(
        // ...
        'factories' => array(
            // ...
            'ApplicationManager' => 'DbuApplication\Service\ApplicationManagerFactory',
        ),
    ),

    'application_environment' => array(
        'error_reporting'       => E_ALL | E_STRICT,
        'ini_set' => array(
            'display_errors'    => 1,
        ),
        'date_default_timezone' => 'Europe/Moscow',
        'base_dir'              => dirname(__DIR__),
        'umask'                 => 0,
    ),
```

You also have possibility to add environment options as shown above
