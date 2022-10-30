install:
	composer install

lint:
	composer exec --verbose phpcs -- --standard=PSR12 src bin

test:
	vendor/bin/phpunit tests

test-coverage:
	vendor/bin/phpunit --coverage-clover clover.xml tests