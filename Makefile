install:
	composer install

dump:
	composer dump-autoload

lint:
	composer exec --verbose phpcs -- --standard=PSR12 src bin

test:
	composer exec --verbose phpunit tests

test-coverage:
	XDEBUG_MODE=coverage
	composer exec --verbose phpunit tests -- --coverage-html html-coverage
