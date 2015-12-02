docker run \
    -e "OPINE_ENV=docker" \
    --rm \
    --link opine-memcached:memcached \
    -v "$(pwd)/../":/app opine:phpunit-config \
    --bootstrap /app/tests/bootstrap.php