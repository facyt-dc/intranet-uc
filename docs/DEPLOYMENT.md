# Deployment

This guide explains how to turn a machine into a deployment target for the project. Once it is set up, every update of the `main` branch is deployed to that machine automatically.

## How it works

The deployment uses a [GitHub Actions self-hosted runner](https://docs.github.com/en/actions/hosting-your-own-runners/managing-self-hosted-runners/about-self-hosted-runners): a program from GitHub that is installed on the machine and stays connected to the repository.

1. A _pull request_ is merged into `main`.
2. GitHub starts the **Deploy** workflow (`.github/workflows/deploy.yml`) and sends it to the runner.
3. The runner, in its project folder:
   1. Downloads the latest version of `main`.
   2. Creates the environment files from the examples of the repository, if they do not exist yet.
   3. Rebuilds the production Docker images and restarts the containers.
   4. Runs the pending database migrations.
   5. Loads the initial data, only on the first deployment.
   6. Caches the configuration, routes and views of Laravel.

The runner opens the connection to GitHub, and not the other way around, so the machine does not need to expose any port for the deployment.

> [!CAUTION]
> The repository is public. A self-hosted runner runs the code of the workflows that are sent to it, so it must only be used by the **Deploy** workflow, which only runs on updates of `main` and on manual executions. Do not use the self-hosted runner in workflows triggered by `pull_request`, and keep enabled the option that requires approval to run workflows from forks (**Settings** → **Actions** → **General**).

## Requirements

- The requirements of the project described in the [`README.md`](../README.md).
- `bash`, which is used by the steps of the workflow. On Windows, it is included in Git for Windows.
- A GitHub account with **admin** access to the repository, to register the runner.

> [!WARNING]
> Docker runs the containers with administrator (root) privileges, so any user that can use Docker can control the machine. The user that runs the runner needs access to Docker. How users and permissions are configured depends on each machine and is the responsibility of whoever sets it up.

## 1. Register the runner

1. On GitHub, go to the repository **Settings** → **Actions** → **Runners** → **New self-hosted runner**.
2. Select the operating system and the architecture of the machine.
3. On the machine, run the commands of the **Download** section shown by GitHub.
4. Run the command of the **Configure** section. It includes a registration token, which is the key that connects the machine with the repository. When the configuration asks for the **Work folder**, enter the folder where the deployments will live, for example `/srv/deploy`. For the rest of the questions, keep the default values.

   The runner stores each repository in a folder with its name, and the code inside another folder with the same name, so the project is downloaded into `<work folder>/intranet-uc/intranet-uc` (for example, `/srv/deploy/intranet-uc/intranet-uc`). That folder also stores the environment files of the deployment.

   > [!NOTE]
   > The registration token is valid for one hour and is only used to register the runner.

5. Configure the runner as a service, so it keeps working after the session is closed or the machine restarts. On Linux and macOS, run from the runner folder:

   ```bash
   sudo ./svc.sh install
   sudo ./svc.sh start
   ```

   On Windows, the configuration offers to install the runner as a service.

On GitHub, the runner must appear in **Settings** → **Actions** → **Runners** with the **Idle** status.

## 2. First deployment

Start the deployment from **Actions** → **Deploy** → **Run workflow**, or wait for the next update of `main`.

On the first deployment, the workflow creates `.env` and `src/.env` from `.env.example` and `src/.env.example`, sets the application in production mode, generates the application key and loads the initial data. When it finishes, the application is available on port `80` of the machine.

> [!NOTE]
> The files of the project folder that belong to the repository are replaced on every deployment. Only the environment files are kept, so the configuration must be changed in `.env` and `src/.env`, and not in other files.

> [!WARNING]
> The example files contain example database passwords, and the initial data creates users with known passwords (see `src/database/seeders/DatabaseSeeder.php`). Change the passwords of those users after the first deployment.
>
> To use other database passwords, edit `.env` and `src/.env` in the project folder after the first deployment. Then, before the application is used, delete the database with `docker compose -f docker-compose.prod.yaml down -v` in that folder and run the workflow again. The database is created with the passwords of the first deployment, so changing them later in the files does not change them in the database.

## 3. Automatic deployments

From now on, every update of `main` is deployed automatically. A deployment can also be started manually from **Actions** → **Deploy** → **Run workflow**. The result and the log of each deployment are in the **Actions** tab of the repository.

> [!WARNING]
> The migrations run automatically on every deployment. Back up the database before merging changes with migrations that modify or delete data.

> [!WARNING]
> The production configuration does not store the Laravel `storage` folder in a volume, so the files saved there, such as uploaded documents or logs, are lost on every deployment.
