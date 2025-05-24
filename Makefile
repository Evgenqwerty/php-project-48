instal:
	composer install

lint:
	composer exec --verbose phpcs -- --standard=PSR12 src bin

tests:
	composer exec phpunit tests
