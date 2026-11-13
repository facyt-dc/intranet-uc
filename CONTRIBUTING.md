# Contributing

This document describes the rules that every contribution to the project must follow. They keep the history of the project readable and allow versions to be generated automatically.

## Branching strategy

The project follows **Trunk-Based Development**:

- `main` is the only long-lived branch. It must always be in a working state.
- Every change is made in a short-lived branch created from `main` and is integrated back into `main` through a _pull request_.
- Branch names use the format `type/short-description`, where the type matches the kind of change. For example: `fix/employees-optional-address`, `docs/readme-laravel-port`.
- To update a branch with the latest changes of `main`, use _rebase_ instead of _merge_, so the history stays linear.
- Once the _pull request_ is merged, the branch is deleted.

## Commit messages

Commit messages follow the [Conventional Commits](https://www.conventionalcommits.org/) specification:

```
<type>[optional scope]: <description>
```

The most common types are:

| Type | Use |
| --- | --- |
| `feat` | A new feature. |
| `fix` | A bug fix. |
| `docs` | Documentation-only changes. |
| `test` | Adding or updating tests. |
| `chore` | Maintenance tasks that do not change the behavior of the application. |
| `ci` | Changes to the continuous integration or delivery workflows. |

Examples:

```
feat: add seeders for employees module master data
fix: allow creating employees without address
docs: fix application access port in readme
```

A change that breaks compatibility is marked with `!` after the type (for example, `feat!: ...`) or with a `BREAKING CHANGE:` footer in the commit body.

Commit messages are written in English.

## Versioning and releases

The project follows [Semantic Versioning](https://semver.org/) (`MAJOR.MINOR.PATCH`), and versions are generated automatically with [Release Please](https://github.com/googleapis/release-please). For this reason, following Conventional Commits is mandatory: the type of each commit decides the next version.

| Commit | Version change | Example |
| --- | --- | --- |
| `fix` | Patch | `1.0.1` → `1.0.2` |
| `feat` | Minor | `1.0.1` → `1.1.0` |
| `!` or `BREAKING CHANGE` | Major | `1.0.1` → `2.0.0` |

Other types (`docs`, `test`, `chore`, `ci`) do not create a new version on their own.

The process is automatic:

1. Every time `main` is updated, Release Please opens or updates a release _pull request_ named `chore(main): release x.y.z`, with the next version and the changes that will be included.
2. When that _pull request_ is merged, Release Please updates `CHANGELOG.md` and `version.txt`, creates the tag and publishes the release on GitHub.

Versions and the changelog must not be edited manually.

## Pull requests

- Every _pull request_ must use the repository template (`.github/pull_request_template.md`) and reference the issue it addresses.
- The author assigns themselves to the _pull request_ and adds the corresponding label.
