# Panel tests

Two normal test suites for the panel: tests of its features (login, accounts,
roles, users, channels, servers, bans, logs, search, settings, ...) and
security tests (script injection, forged requests, permissions, install and
web server). Both are grouped in the same categories, see
[`categories.json`](categories.json).

## Unit tests (PHPUnit)

The panel's own functions, without a web server or IRC server:

```sh
composer install
vendor/bin/phpunit
```

Files: `phpunit.xml`, `tests/bootstrap.php`, `tests/Unit/`.

## Integration and browser tests (node:test)

These use the panel like a person would, against a real UnrealIRCd, in Docker.
They run on a copy of this checkout, so your own `config/` and accounts are
never touched.

```sh
cd tests/integration
npm install
npx playwright install chromium         # for the browser tests
npm test                                # everything, the PHPUnit tests too
node run-tests.mjs --only "Channels"    # only tests or categories whose name matches
node run-tests.mjs --skip-prepare       # reuse the running stack and installed panel
```

You need Docker, git and Node 20+. The first run builds the test images
(UnrealIRCd is compiled from source) and takes several minutes. Everything
listens on 127.0.0.1 only (ports 18080 and 16667, change them with
`PANEL_PORT` / `IRC_PORT`). Scratch files and the JUnit reports go to
`<tmp>/unrealircd-panel-tests/` (change it with `TEST_WORK_DIR`).

* `run-tests.mjs`: copies the panel, starts the stack, installs the panel, connects the test IRC users, then runs PHPUnit and `node --test suites/`
* `plant.mjs`: ordinary IRC users that put data in the IRC server that the tests look at (channels, bans, topics, realnames, quit messages; some of it with HTML in it, which only sets a marker variable if it ever runs)
* `suites/*.test.mjs`: one file per area; `90-browser` uses Playwright, `95-docker` builds the panel's own Docker image, `99-gline` runs last because it Z-lines the test clients
* `demo.mjs`: starts the same stack with a panel full of demo data (IRC users, channels, bans, spamfilters, accounts, roles) to click around in, at http://127.0.0.1:18080/unrealircd-webpanel/ (admin / Adm1n!pass#)
