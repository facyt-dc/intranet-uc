# Deployment

This guide explains how to turn a Linux server into a deployment target for the project. Once it is set up, every update of the `main` branch is deployed to the server automatically.

## How it works

The deployment uses a [GitHub Actions self-hosted runner](https://docs.github.com/en/actions/hosting-your-own-runners/managing-self-hosted-runners/about-self-hosted-runners): a program from GitHub that is installed on the server and stays connected to the repository.

1. A _pull request_ is merged into `main`.
2. GitHub starts the **Deploy** workflow (`.github/workflows/deploy.yml`) and sends it to the runner of the server.
3. The runner, on the server itself:
   1. Downloads the latest changes of `main` (`git pull`).
   2. Rebuilds the production Docker images.
   3. Restarts the containers with the new images.
   4. Runs the pending database migrations.
   5. Caches the configuration, routes and views of Laravel.
   6. Removes the Docker images that are no longer used.

The runner opens the connection to GitHub, and not the other way around, so the server does not need to expose any port for the deployment. It only needs outbound access to `github.com` over HTTPS.

## Requirements

- A Linux server (for example, Ubuntu 22.04 or newer) with:
  - [Docker Engine](https://docs.docker.com/engine/install/) and the Docker Compose plugin.
  - `git`.
  - Port `80` open to the users of the application.
- A GitHub account with **admin** access to the repository, to register the runner and create the repository variable.

## 1. Prepare the server

Run these steps once, on the server.

### 1.1. Create a user for the deployment

Create a dedicated user, without administrator privileges, and allow it to use Docker:

```bash
sudo adduser --disabled-password --gecos "" deploy
sudo usermod -aG docker deploy
```

The rest of the guide runs as this user (`sudo -iu deploy`).

### 1.2. Clone the repository

The repository is cloned in the directory where the application will live. This guide uses `/opt/intranet-uc`:

```bash
sudo mkdir -p /opt/intranet-uc
sudo chown deploy:deploy /opt/intranet-uc
git clone https://github.com/facyt-dc/intranet-uc.git /opt/intranet-uc
cd /opt/intranet-uc
```

### 1.3. Create the environment files

The environment files are never stored in the repository; they only exist on the server.

```bash
cp .env.example .env
cp src/.env.example src/.env
chmod 600 .env src/.env
```

Edit both files:

- In `.env`, replace the example passwords of the database with secure values.
- In `src/.env`:
  - Set `APP_ENV=production` and `APP_DEBUG=false`.
  - Set `APP_URL` to the address of the server (for example, `http://intranet.example.com`).
  - Use in `DB_DATABASE`, `DB_USERNAME` and `DB_PASSWORD` the same values defined in `.env`.
  - Set `APP_KEY` with a new key, generated with:

    ```bash
    echo "base64:$(openssl rand -base64 32)"
    ```

> [!IMPORTANT]
> The key must be written in `src/.env` on the server. The production image copies that file when it is built, and it is rebuilt on every deployment, so a key generated inside a running container would be lost on the next deployment.

### 1.4. Run the first deployment manually

```bash
docker compose -f docker-compose.prod.yaml build
docker compose -f docker-compose.prod.yaml up -d
docker compose -f docker-compose.prod.yaml exec app php artisan migrate --force --seed
```

If the migrations fail because the database is not ready yet, wait a few seconds and run the last command again.

> [!WARNING]
> The seeders create example users with known passwords (see `src/database/seeders/DatabaseSeeder.php`). Run `--seed` only on the first deployment, and change those passwords or remove those users right after.

The application is now available at the address of the server.

## 2. Register the runner

### 2.1. Install the runner

1. On GitHub, go to the repository **Settings** → **Actions** → **Runners** → **New self-hosted runner**.
2. Select **Linux** and the architecture of the server (usually **x64**).
3. On the server, as the `deploy` user, run the commands of the **Download** section in a new directory (for example, `~/actions-runner`).
4. Run the command of the **Configure** section. It includes a registration token, which is the key that connects the server with the repository. When the configuration asks for them:
   - **Runner group**: keep the default.
   - **Name**: a name that identifies the server (for example, `intranet-uc-production`).
   - **Additional labels**: `intranet-uc-production`. The workflow only runs on runners with this label.
   - **Work folder**: keep the default.

> [!NOTE]
> The registration token is valid for one hour and is only used to register the runner. After that, the runner keeps its own credentials.

### 2.2. Run the runner as a service

So that the runner keeps working after the session is closed or the server restarts, install it as a service from the runner directory:

```bash
cd ~/actions-runner
sudo ./svc.sh install deploy
sudo ./svc.sh start
```

On GitHub, the runner must appear in **Settings** → **Actions** → **Runners** with the **Idle** status.

## 3. Configure the deployment directory

The workflow needs to know where the application lives on the server.

1. On GitHub, go to **Settings** → **Secrets and variables** → **Actions** → **Variables**.
2. Create the repository variable `DEPLOY_PATH` with the directory of step 1.2 (for example, `/opt/intranet-uc`).

## 4. Automatic deployments

From now on, every update of `main` is deployed automatically. A deployment can also be started manually from **Actions** → **Deploy** → **Run workflow**.

The result and the log of each deployment are in the **Actions** tab of the repository. If a step fails, the following steps are not executed and the containers that were running keep working with the previous version, unless the failure happens after the containers were restarted.

## Security considerations

- **The repository is public.** A self-hosted runner runs the code of the workflows that are sent to it, so it must only be used by the **Deploy** workflow. This workflow only runs on updates of `main` and on manual executions, never on _pull requests_, so code from forks or unreviewed changes never reaches the server. Do not add the `intranet-uc-production` label to other workflows, and do not use the runner in workflows triggered by `pull_request`.
- **Keep fork approvals enabled.** In **Settings** → **Actions** → **General**, keep the option that requires approval before running workflows from forks.
- **Docker access is equivalent to administrator access.** Any user in the `docker` group can control the server. Use the `deploy` user only for the runner and the application.
- **`main` must always be deployable.** Since every update of `main` reaches the server, changes must be reviewed and tested before they are merged.
- **Migrations run automatically.** Back up the database before merging changes that include migrations that modify or delete data.
- **Environment files are secrets.** `.env` and `src/.env` only exist on the server, with permissions restricted to the `deploy` user.

## Known limitations

- **Uploaded files are not persistent.** The production configuration does not store the Laravel `storage` directory in a volume, so the files saved there (such as logs or uploaded documents) are lost when the containers are recreated in a deployment.
- **Brief downtime.** While the containers restart, the application may not respond for a few seconds.
- **One server.** The workflow deploys to the runners with the `intranet-uc-production` label, using a single `DEPLOY_PATH` variable for the repository.

## Troubleshooting

| Problem | Solution |
| --- | --- |
| The workflow stays in **Queued** | The runner is offline. On the server, check it with `sudo ./svc.sh status` from the runner directory. |
| `The DEPLOY_PATH repository variable is not set` | Create the variable of step 3. |
| `... does not exist. Follow docs/DEPLOYMENT.md` | The directory of `DEPLOY_PATH` does not have the cloned repository or the environment files of step 1. |
| The `git pull` step fails | Files tracked by the repository were modified on the server. Do not edit them on the server; discard the changes with `git checkout -- .` in `DEPLOY_PATH`. |
| `permission denied` when running `docker` | The `deploy` user is not in the `docker` group. Add it and restart the runner service. |
| The migrations fail on the first deployment | The database was still starting. Run the workflow again from **Actions** → **Deploy** → **Run workflow**. |

## Remove a server

1. Stop and uninstall the service: `sudo ./svc.sh stop` and `sudo ./svc.sh uninstall`.
2. On GitHub, go to **Settings** → **Actions** → **Runners**, select the runner and use **Remove** to get the removal command, then run it on the server.
3. Stop the application with `docker compose -f docker-compose.prod.yaml down` in `DEPLOY_PATH`.
