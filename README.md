# Fix Common Problems

An Unraid plugin that scans the server for common problems and configuration mistakes and suggests a fix for each one it finds. It is installed from Community Applications and appears under **Tools > Fix Common Problems**.

## Support

- **Questions about a warning or error the plugin shows:** start a new topic in [General Support](https://forums.unraid.net/forum/55-general-support/).
- **The plugin itself** (false positives, requests for new checks, problems with the plugin): the [support thread](https://forums.unraid.net/topic/47266-plugin-ca-fix-common-problems/).
- **What a warning means:** [Fix Common Problems: more information](https://forums.unraid.net/topic/120220-fix-common-problems-more-information/). Most warnings link to their own post there.
- **Bugs in the code:** [GitHub issues](https://github.com/unraid/fix.common.problems/issues).

These are the same links the plugin's own page shows.

## What it does

- **Scan:** runs every check and lists the results as errors, warnings and other comments, each with a suggested fix and, where possible, a button to the setting it refers to.
- **Ignore:** any result can be ignored from the results list. Ignored results stay listed separately and can be restored. The list is kept in `/boot/config/plugins/fix.common.problems/ignoreList.json`.
- **Extended tests:** an optional, slower set of checks, started from the page.
- **Troubleshooting mode:** for random lockups and resets. It keeps copying the syslog to the flash drive and saves diagnostics every 30 minutes until the server restarts.
- **Update Assistant:** checks for known problems before an Unraid OS upgrade.

Settings and the ignore list live in `/boot/config/plugins/fix.common.problems/`.

## Repository layout

| Path | What it is |
|---|---|
| `plugins/fix.common.problems.plg` | The plugin file Unraid installs and checks for updates |
| `source/fix.common.problems/usr/local/emhttp/plugins/fix.common.problems/` | The plugin's files as installed on the server |
| `.../include/tests.php` | The checks |
| `.../scripts/scan.php` | Runs the checks, in the order of its `$tests` list |
| `.../scripts/checkList.php` | The plain-language list of checks |
| `source/fix.common.problems/pkg_build.sh` | Builds the package into `archive/` |
| `archive/` | Released packages |

## Adding a check

1. Write the check as a function in `include/tests.php`, using `addError()`, `addWarning()` or `addOther()` for its results. Keep the description text the same for the same problem: the ignore list matches on it.
2. Add the function name to the `$tests` list in `scripts/scan.php`, and a line describing it to `scripts/checkList.php`.
3. The plugin supports Unraid 6.7.0 and later (`min` in the `.plg`), so code has to run on the PHP versions those releases ship.
4. Check changed PHP files with `php -l` on PHP 7.2 (the version Unraid 6.7.0 ships, for example the `php:7.2-cli` Docker image) and on a current PHP: a newer PHP accepts syntax that 7.2 cannot parse. Check changed Bash scripts with `bash -n`; `php -l` passes any file without PHP tags.

## Licence

GPL-2.0, see [LICENSE](LICENSE).
