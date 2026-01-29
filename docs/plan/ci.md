CI & Local reproducible environment

This project includes a GitHub Actions workflow and a Dockerfile to run tests with a reproducible environment.

What I added
- `.github/workflows/ci.yml` — GitHub Actions workflow that runs on Ubuntu and a PHP matrix (8.2..8.5). Installs OpenJDK 17 and tries to install `epubcheck` via apt; if missing it downloads epubcheck release JAR as fallback. Runs `phpcs`, `phpstan`, `phpunit`, runs `epubcheck` on artifacts in `tests/build` and deletes them afterwards.
- `Dockerfile` and `docker-compose.yml` — local container to reproduce the environment (PHP 8.4 CLI image, JRE, zip, dom). The container also downloads epubcheck JAR if apt package is not available.

How to use locally with Docker (recommended):

1) Build the image:

```bash
docker-compose build --no-cache
```

2) Run tests inside the container:

```bash
docker-compose run --rm app
```

This will run composer install and then run PHPUnit.

How CI validates epubs
- The workflow will run your test suite, then look for `.epub` files under `tests/build`. For each found epub, the CI will run `epubcheck` (system binary if installed via apt, or `java -jar <downloaded-jar>` if apt package is not present). After validation each generated EPUB is deleted from `tests/build`.

Notes
- You said you installed `epubcheck` with apt locally — great: CI will try the same. If your GitHub runner has apt package available the workflow will use it.
- I added `symfony/process` usage in the adapter so validation is done with proper timeouts and captured exit code.

Next steps I can do on demand
- Add a `bin/epubcheck.php` wrapper to call the validation from PHP for tight integration with tests.
- Extend the workflow to upload artifacts/logs on failure.
- Add a GitHub Actions job that caches composer dependencies between runs for speed.

