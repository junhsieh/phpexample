\# curl -s https://getcomposer.org/installer | php

\# ./composer.phar require --dev phpunit/phpunit ^6.0  
\# ./composer.phar require --dev phpunit/php-invoker  
\# ./composer.phar require --dev phpunit/dbunit  

\# cat composer.json

```
{
    "require-dev": {
        "phpunit/phpunit": "^6.0",
        "phpunit/php-invoker": "^1.1",
        "phpunit/dbunit": "^3.0"
    }
}
```

\# ls -l vendor/composer/autoload*  
\# cat ./vendor/composer/autoload_psr4.php  
