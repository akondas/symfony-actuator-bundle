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
    },
    "database": {
      "default": {
        "type": "stgreSQL100",
        "database": "app",
        "driver": "Symfony\\Bridge\\Doctrine\\Middleware\\Debug\\Driver"
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

Add `actuator.yaml` to `config/routes` directory (you can change prefix):

```yaml
web_profiler_wdt:
  resource: '@ActuatorBundle/Resources/config/routing.yaml'
  prefix: /api/actuator
```

## Security

⚠️Be aware that this bundle provides information and functionalities that may be potentially dangerous for your application.

Use the built-in [symfony/security](https://symfony.com/doc/current/security.html) component to secure api.

Example configuration (`security.yaml`) for Basic Authentication:

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

## Extending

### Health indicator

You can write your own health indicator and implement your own logic to determine the state of your application. To do so, you have to implement the interface `HealthIndicator` and tag your service with the tag `akondas.health_indicator`.

So for example, add following class under `src/Health/CustomHealthIndicator.php`:

```php
<?php

declare(strict_types=1);

namespace App\Health;

use Akondas\Service\Health\HealthIndicator;
use Akondas\Service\Health\Health;

class CustomHealthIndicator implements HealthIndicator
{
    public function name(): string
    {
        return 'custom';
    }

    public function health(): Health
    {
        return Health::up()->setDetails(['state' => 'OK!']);
    }
}
```

Then add following definition to `config/services.yaml`:

```yaml
services:
  App\Health\CustomHealthIndicator: 
    tags: ['akondas.health_indicator']
```

### Information Collector

Similar to a health indicator, you can write also a service which exposes informations. To do so, you have to implement the interface `Collector` and add the tag `akondas.info_collector`.

```php
<?php

declare(strict_types=1);

namespace App\Info;

use Akondas\Service\Info\Collector\Collector;
use Akondas\Service\Info\Info;

class CustomInfoCollector implements Collector
{
    public function collect(): Info
    {
        return new Info('my-info', [ 'time' => time() ]);
    }
}
```

Then add following definition to `config/services.yaml`:

```yaml
services:
  App\Info\CustomInfoCollector: 
    tags: ['akondas.info_collector']
```

## Configuration reference

The bundle works out of the box with no configuration. If you want to change the default configuration, create a configuration file under `config/packages/actuator.yaml`. The default configuration is as follows:

```yaml
actuator:
  health:
    enabled: true
    builtin:
      disk_space:
        enabled: true
        threshold: 52428800
        path: '%kernel.project_dir%'
      database:
        enabled: true
        connections:
          default:
            service: 'Doctrine\DBAL\Connection'
            check_sql: 'SELECT 1'
  info:
    enabled: true
    builtin:
      php:
        enabled: true
      symfony:
        enabled: true
      git:
        enabled: true
      database:
        enabled: true
        connections:
          connection_name: 'Doctrine\DBAL\Connection' 
```

Following table outlines the configuration:

| key                                                   | default                    | description                                                                                                                                                                                                                                                                    |
|-------------------------------------------------------|----------------------------|--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| actuator.health.enabled                               | true                       | if the health endpoint should be enabled                                                                                                                                                                                                                                       |
| actuator.health.disk_space.enabled                    | true                       | if the builtin disk_space health endpoint should be enabled                                                                                                                                                                                                                    |
| actuator.health.disk_space.threshold                  | 52428800                   | Size in bytes which has to be free in order that this health endpoint is "UP"                                                                                                                                                                                                  |
| actuator.health.disk_space.path                       | '%kernel.project_dir%'     | The directory which should be monitored                                                                                                                                                                                                                                        |
| actuator.health.database.enabled                      | true                       | if the database health endpoint should be enabled                                                                                                                                                                                                                              |
| actuator.health.database.connections                  | Array                      | Contains a list of names, where each represents an connection to e database. The name itself can be chosen at will                                                                                                                                                             |
| actuator.health.database.connections.`name`.enabled   | true                       | If the connection associated with this name should monitored                                                                                                                                                                                                                   |
| actuator.health.database.connections.`name`.service   | 'Doctrine\DBAL\Connection' | The service name inside the dependency injection container. You can lookup your connection name with `bin/console debug:container`                                                                                                                                             |
| actuator.health.database.connections.`name`.check_sql | 'Select 1'                 | The SQL which will be executed to determine if the database is up. The response will be ignored, it only matters if the sql can be executed without error. If you set this to `~` it will only check if a connection to the database can be established                        |
| actuator.info.enabled                                 | true                       | if the info endpoint should be enabled                                                                                                                                                                                                                                         |
| actuator.info.builtin.php.enabled                     | true                       | if the php info endpoint should be enabled                                                                                                                                                                                                                                     |
| actuator.info.builtin.symfony.enabled                 | true                       | if the symfony info endpoint should be enabled                                                                                                                                                                                                                                 |
| actuator.info.builtin.git.enabled                     | true                       | if the git info endpoint should be enabled                                                                                                                                                                                                                                     |
| actuator.info.builtin.database.enabled                | true                       | if the database info endpoint should be enabled                                                                                                                                                                                                                                |
| actuator.info.builtin.database.connections            | Array                      | List of connections which for which the info endpoint should return the database informations. The list contains of a key which can be choosen at your own will. The second argument is the service in the DIC. You can lookup your service with `bin/console debug:container` |

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
