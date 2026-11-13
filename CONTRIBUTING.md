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
| `refactor` | A code change that neither fixes a bug nor adds a feature, such as reorganizing or simplifying code. |
| `style` | Changes that do not affect the meaning of the code, such as formatting, white space or missing semicolons. |
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

Other types (`docs`, `refactor`, `style`, `test`, `chore`, `ci`) do not create a new version on their own.

The process is automatic:

1. Every time `main` is updated, Release Please opens or updates a release _pull request_ named `chore(main): release x.y.z`, with the next version and the changes that will be included.
2. When that _pull request_ is merged, Release Please updates `CHANGELOG.md` and `version.txt`, creates the tag and publishes the release on GitHub.

Versions and the changelog must not be edited manually.

## Issues

Every piece of work starts with an issue. Issues and _pull requests_ are written in Spanish.

- Before opening an issue, search the existing ones to avoid duplicates.
- Open the issue with the template that matches the work:

  | Template | Use |
  | --- | --- |
  | **Bug report** | Something does not work as expected. |
  | **Feature request** | A new feature or behavior for the project. |
  | **Task** | A specific piece of work or maintenance activity, such as documentation or configuration. |

- The title briefly describes the problem or the work, so it can be identified without opening the issue.
- The description states what has to be done. A bug report also includes the steps to reproduce it, the current behavior, the expected behavior and the error message, if there is one.
- Each issue covers a single problem or task. If another problem is found while working on an issue, it is reported in a new issue.
- The issue gets a label, the milestone it belongs to and the person who will work on it, and it is added to the project with the **Todo** status.
- An issue can be solved by more than one _pull request_, so it is not closed automatically. It is closed once all its _pull requests_ are merged and the result is verified.

## Pull requests

- Every _pull request_ must use the repository template (`.github/pull_request_template.md`).
- The issue it addresses is referenced in the **Issues** section as `#number`, without keywords like `Closes` or `Fixes`, which would close the issue automatically.
- The **Description** summarizes what was done. It does not explain the problem, which is already described in the issue.
- The **List of changes** describes how the behavior of the project changed (before and after) and why, so the reviewer knows what to test. It is not a list of files or commits: names of files, components or variables can be seen in the commits.
- The **Additional context** is used for warnings, decisions or details that the reviewer or future contributors should know.
- The text is written in the past tense ("Se agregó...", "Se corrigió..."). Words in English are written in _italics_, and abbreviations are written in full the first time they appear, for example "Material UI (MUI)".
- The author assigns themselves to the _pull request_, requests a review and adds a label. The label is usually the same as the one of the issue, but it can be different; for example, a bug may need a new feature to be solved.
- _Pull requests_ are merged with **Rebase and merge** to keep the history linear.

## Project status

Issues and _pull requests_ are tracked in the GitHub project of the repository. The issue and its _pull request_ move together through these statuses:

| Status | Meaning | Who moves it |
| --- | --- | --- |
| **Todo** | The work is registered but has not started. | Whoever opens the issue. |
| **In Progress** | Someone is working on it: the branch was created, or changes requested in a review are being applied. | The author. |
| **Waiting Review** | The _pull request_ is open and waiting for a review. | The author. |
| **Error** | The work failed: the checks do not pass, the change does not solve the issue, or a problem appeared after merging it. The cause is explained in a comment. | The reviewer or the author. |
| **Done** | The _pull request_ was merged and the result was verified. | The reviewer. |

Possible flows:

- **Approved on the first review:**
  `Todo` → `In Progress` → `Waiting Review` → `Done`
- **Changes requested in the review:**
  `Todo` → `In Progress` → `Waiting Review` → `In Progress` → `Waiting Review` → `Done`
- **The change fails:**
  `Todo` → `In Progress` → `Waiting Review` → `Error` → `In Progress` → `Waiting Review` → `Done`
- **Issue solved with several _pull requests_:** each _pull request_ follows its own flow, and the issue stays in `In Progress` until the last one reaches `Done`.
