all: clean coverage docs

clean:
	rm -rf build/artifacts/*

test:
	vendor/bin/phpunit --testsuite=unit $(TEST)

coverage:
	vendor/bin/phpunit --testsuite=unit --coverage-html=build/artifacts/coverage $(TEST)

coverage-show:
	open build/artifacts/coverage/index.html

coverage-publish:
	vendor/bin/phpunit --testsuite=unit --coverage-clover build/logs/clover.xml
	CODECLIMATE_REPO_TOKEN=42ae1a885c6a20a0dc1c7802a3eb3a915b01f8f9c6f2443b8dd99ef77be48150 ./vendor/bin/test-reporter

integ:
	vendor/bin/phpunit --debug --testsuite=integ $(TEST)

perf:
	php tests/perf.php

travis:
	vendor/bin/phpunit
