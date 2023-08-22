# API

The API will be here.

Refer to the [Getting Started Guide](https://api-platform.com/docs/distribution) for more information.

Pour demarer l'application: symfony serve

les configuration de la base de données dans le fichier .env

remplacer par le votre configuration pour comminuquer avec la base de données local

# certificat

https://github.com/lexik/LexikJWTAuthenticationBundle/blob/2.x/Resources/doc/index.rst#id15

https://www.univ-orleans.fr/iut-orleans/informatique/intra/tuto/php/symfony-securitybundle-auth.html

install : $ php composer.phar require "lexik/jwt-authentication-bundle"

$ php bin/console lexik:jwt:generate-keypair

# eliminer le certificat

symfony server:ca:uninstall
