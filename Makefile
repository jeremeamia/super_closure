all: clean coverage docs

clean:
	rm -rf build/artifacts/*

test:
	vendor/bin/phpunit --testsuite=unit $(TEST)

coverage:
	vendor/bin/phpunit --testsuite=unit --coverage-html=build/artifacts/coverage $(TEST)

coverage-show:
	open build/artifacts/coverage/index.html

integ:
	vendor/bin/phpunit --debug --testsuite=integ $(TEST)

perf:
	php tests/perf.php

travis:
	vendor/bin/phpunit
