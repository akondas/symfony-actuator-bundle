# SymfonyActuatorBundle

[![Minimum PHP Version](https://img.shields.io/badge/php-%3E%3D%207.4-8892BF.svg)](https://php.net/)

Production-ready features for your Symfony application. Actuator endpoints let you monitor and interact with your application

## Features

- REST API endpoints (`/api/actuator`)
- UI console (not implemented)

## Endpoints

- **/health** (`components` in progress)
  ```json
  {
    "status": "up"
  }
  ```

- **/info**
  ```json
  {
    "git": {
      "branch": "actuator",
      "commit": "6c01dce07274c6fddfd58610cf5fe14964689edd"
    },
    "php": {
      "version": "7.4.3",
      "architecture": 64,
      "intlLocale": "en",
      "timezone": "Europe/Berlin",
      "xdebugEnabled": false,
      "apcuEnabled": false,
      "opCacheEnabled": true
    },
    "symfony": {
      "version": "5.2.2",
      "lts": false,
      "environment": "dev",
      "endOfMaintenance": "July 2021",
      "endOfLife": "July 2021",
      "bundles": {
        "FrameworkBundle": "Symfony\\Bundle\\FrameworkBundle\\FrameworkBundle",
        "SensioFrameworkExtraBundle": "Sensio\\Bundle\\FrameworkExtraBundle\\SensioFrameworkExtraBundle",
        "TwigBundle": "Symfony\\Bundle\\TwigBundle\\TwigBundle",
        "MonologBundle": "Symfony\\Bundle\\MonologBundle\\MonologBundle",
        "DoctrineBundle": "Doctrine\\Bundle\\DoctrineBundle\\DoctrineBundle",
        "DoctrineMigrationsBundle": "Doctrine\\Bundle\\MigrationsBundle\\DoctrineMigrationsBundle",
        "SecurityBundle": "Symfony\\Bundle\\SecurityBundle\\SecurityBundle",
        "ActuatorBundle": "Akondas\\ActuatorBundle\\ActuatorBundle"
      }
    }
  }
  ```

## Install

```shell
composer require akondas/symfony-actuator-bundle
```

Add `ActuatorBundle` to `config/bundles.php`
```php
Akondas\ActuatorBundle\ActuatorBundle::class => ['all' => true]
```

Add `actuator.yml` to `config/routes` directory (you can change prefix):

```yaml
web_profiler_wdt:
  resource: '@ActuatorBundle/Resources/config/routing.yml'
  prefix: /api/actuator
```

## Security

⚠️Be aware that this bundle provides information and functionalities that may be potentially dangerous for your application.

Use the built-in [symfony/security](https://symfony.com/doc/current/security.html) component to secure api.

Example configuration (`security.yml`) for Basic Authentication:

```yaml
security:
    providers:
        in_memory:
            memory:
                users:
                    admin:
                        password: 'password'
                        roles: 'ROLE_ACTUATOR'
    firewalls:
        actuator:
            http_basic: ~
    access_control:
        - { path: ^/api/actuator, roles: ROLE_ACTUATOR }
    encoders:
        Symfony\Component\Security\Core\User\User: plaintext
```

## Configuration reference

Currently, no configuration required.

TODO: add reference

## Roadmap

- flex recipe
- status for components (database, mailer, notifier, etc.)
- endpoints for components:
    - messenger: show failed message, retry by message class (most wanted feature!)
    - mailer: send email, 
    - cache: clear cache maybe? 
    - notifier: trigger test notification
- UI: same as rest api but presented in the beauty of the admin panel. 
  In particular, I care about the messenger component, because at the moment retrying erroneous messages is very clunky

## License

SymfonyActuatorBundle is released under the MIT Licence. See the bundled LICENSE file for details.

## Author

[Arkadiusz Kondas](https://twitter.com/ArkadiuszKondas)
