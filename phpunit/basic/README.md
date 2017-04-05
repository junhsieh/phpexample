\# curl -s https://getcomposer.org/installer | php

\# ./composer.phar require --dev phpunit/phpunit ^6.0  
\# ./composer.phar require --dev phpunit/php-invoker  
\# ./composer.phar require --dev phpunit/dbunit  

\# vim composer.json

```
{
	"require-dev": {
		"phpunit/phpunit": "^6.0",
		"phpunit/php-invoker": "^1.1",
		"phpunit/dbunit": "^3.0"
	},
	"autoload-dev": {
		"psr-4": {"My\\": "src/My"}
	}
}
```

\# ./composer.phar dump-autoload

\# ls -l vendor/composer/autoload*  
\# cat ./vendor/composer/autoload_psr4.php  

\# ./vendor/bin/phpunit --verbose --debug  
