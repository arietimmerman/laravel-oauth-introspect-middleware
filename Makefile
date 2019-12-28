
.PHONY: phpunit
phpunit:
	./vendor/bin/phpunit --debug

.PHONY: format
format:
	./vendor/bin/phpcbf  --ignore=./vendor/ .
