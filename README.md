# VrijBRPToZGWBundle [![Codacy Badge](https://app.codacy.com/project/badge/Grade/980ea2efc85a427ea909518f29506ff6)](https://app.codacy.com/gh/CommonGateway/VrijBRPToZGWBundle/dashboard?utm_source=gh\&utm_medium=referral\&utm_content=\&utm_campaign=Badge_grade)

This repository is for synchronizing from VrijBRP to ZGW with usage of the CommonGateway and CoreBundle.

### Installation with the Common Gateway admin user-interface

Head to the `Plugins` tab to search, select and install plugins.

#### Installing with PHP commands

To execute the following command, you will need [Composer](https://getcomposer.org/download/) or a dockerized installation that already has PHP and Composer.

The Composer method in the terminal and root folder:

> for the installation of the plugin

`$composer require common-gateway/vrijbrp-to-zgw-bundle:dev-main`

> for the installation of schemas

`$php bin/console commongateway:install common-gateway/vrijbrp-to-zgw-bundle`

The dockerized method in the terminal and root folder:

> for the installation of the plugin

`$docker-compose exec php composer require common-gateway/vrijbrp-to-zgw-bundle:dev-main`

> for the installation of schemas

`$docker-compose exec php bin/console commongateway:install common-gateway/vrijbrp-to-zgw-bundle`