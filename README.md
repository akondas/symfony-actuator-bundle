# SymfonyActuatorBundle 

[![Minimum PHP Version](https://img.shields.io/badge/php-%3E%3D%207.4-8892BF.svg)](https://php.net/)

Production-ready features for your Symfony application. Actuator endpoints let you monitor and interact with your application

## Features

- REST API endpoints (`/api/actuator`)

## Endpoints

- health (components in progress)
- info

## Install

```shell
composer require akondas/symfony-actuator-bundle
```

## Security

Use the built-in [symfony/security](https://symfony.com/doc/current/security.html) component to secure api.

TODO: add example `security.yml` configuration

## Configuration reference

TODO: add reference

## Roadmap

- flex recipe
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
