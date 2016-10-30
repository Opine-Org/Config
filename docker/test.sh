docker run \
    -e "OPINE_ENV=docker" \
    --rm \
    -v "$(pwd)/../":/app opine:phpunit-config \
    --bootstrap /app/tests/bootstrap.php
