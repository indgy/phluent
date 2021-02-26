.PHONY: dist
# Make all documentation
docs: apidocs userdocs
# Make the API docs
apidocs:
	php73 /opt/local/bin/doctum update ./doctum.config.php
# Make the user guide docs
userdocs:
	@rm -r docs
	@mkdir docs
	mkdocs build
# Start the docs server
userdocs-serve:
	@rm -r docs
	@mkdir docs
	mkdocs serve

# make a single file containg all classes
# todo remove <?php declare, move all namespaces to top
dist:
	@rm -r dist
	@mkdir dist
	@cat \
		src/functions.php \
		src/Query.php \
		src/DB.php \
		>> dist/Phluent.php
	@echo Created dist/Phluent.php

# Run unit tests
test:
	phpunit