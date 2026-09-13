# API Test Verification Results

Verified on 2026-09-13 using PHP 8.4.25 and PHPUnit 12.5.35 in the app container, with isolated in-memory SQLite data and fake photo storage.

| Check | Result |
|---|---|
| API endpoint matrix | 488 tests passed, 1,046 assertions |
| Complete suite, including existing feature/unit tests | 501 tests passed, 1,134 assertions |
| Declared API method/path coverage | All 38 `/api/v1` method/path combinations have full-parameter tests and titled documents |
| New PHP test formatting | Passed Laravel Pint |
| Documentation examples | 40 Bash blocks passed syntax validation; local documentation links checked |

Commands used:

```bash
docker compose exec -T app vendor/bin/phpunit --no-progress --filter=ApiEndpointMatrixTest
docker compose exec -T app vendor/bin/phpunit --no-progress
docker compose exec -T app vendor/bin/pint --test tests/Feature/ApiEndpointMatrixTest.php
```

The matrix is defined in `tests/Fixtures/api-cases.json` and executed by `tests/Feature/ApiEndpointMatrixTest.php`. Each data-provider case starts with independent fixtures. HTTP requests pass through Laravel routing, authentication, authorization, and validation; successful mutation cases include selected database/file assertions.

This run did not execute the new matrix against MySQL, call live Firebase, or test real-device behavior. The curl snippets were syntax-checked, while equivalent full-parameter requests were executed through Laravel's test client. Response examples are illustrative excerpts. All supported parameters are documented, with representative valid/invalid and boundary cases; this is not exhaustive coverage of all possible values, combinations, concurrency, or production infrastructure failures.

[Return to the API test index](README.md)
