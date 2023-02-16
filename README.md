<!-- START doctoc generated TOC please keep comment here to allow auto update -->
<!-- DON'T EDIT THIS SECTION, INSTEAD RE-RUN doctoc TO UPDATE -->
**Table of Contents**  *generated with [DocToc](https://github.com/thlorenz/doctoc)*

- [Purpose](#purpose)
- [Project branch strategy](#project-branch-strategy)
- [Recommended versions](#recommended-versions)
- [Codebase](#codebase)
- [Acquia](#acquia)
- [External Resources](#external-resources)
- [Local dev setup using Lando](#local-dev-setup-using-lando)
  - [(Step 1) Request access to Acquia](#step-1-request-access-to-acquia)
  - [(Step 2) Install Lando and Docker Desktop](#step-2-install-lando-and-docker-desktop)
  - [(Step 3) Git clone the repo](#step-3-git-clone-the-repo)
  - [(Step 4) Configure Git](#step-4-configure-git)
  - [(Step 5) Configure /etc/hosts](#step-5-configure-etchosts)
  - [(Step 6) Lando start](#step-6-lando-start)
  - [(Step 7) Add SSH key to Acquia Cloud Dashboard](#step-7-add-ssh-key-to-acquia-cloud-dashboard)
  - [(Step 8) Pull production database to local](#step-8-pull-production-database-to-local)
  - [(Step 9) Run database updates, import config and clear caches](#step-9-run-database-updates-import-config-and-clear-caches)
  - [(Step 10) Test your site](#step-10-test-your-site)
  - [(Step 11) Resolve broken images](#step-11-resolve-broken-images)
    - [Proxying using Resource Override](#proxying-using-resource-override)
  - [(Step 12) Login to site](#step-12-login-to-site)
- [Lando basics](#lando-basics)
- [Drush](#drush)
  - [Useful Drush commands](#useful-drush-commands)
    - [Clear caches](#clear-caches)
    - [Run database updates](#run-database-updates)
    - [Import config](#import-config)
    - [Export config](#export-config)
    - [List installed modules](#list-installed-modules)
  - [Drush aliases](#drush-aliases)
- [Development process](#development-process)
  - [Drupal Configuration Synchronization](#drupal-configuration-synchronization)
    - [Importing config](#importing-config)
    - [Exporting config](#exporting-config)
    - [sync vs. vcs and ignoring webform config](#sync-vs-vcs-and-ignoring-webform-config)
  - [Pulling database to local](#pulling-database-to-local)
    - [Process](#process)
  - [Creating a new feature branch](#creating-a-new-feature-branch)
  - [Exporting config and committing code](#exporting-config-and-committing-code)
  - [Pushing a new branch](#pushing-a-new-branch)
  - [Creating a Pull Request](#creating-a-pull-request)
  - [Merging a Pull Request](#merging-a-pull-request)
    - [Conflicts in build directory that prevent merging into Dev](#conflicts-in-build-directory-that-prevent-merging-into-dev)
- [Deployment](#deployment)
  - [Dev deployment process](#dev-deployment-process)
  - [Stage/Test deployment process](#stagetest-deployment-process)
  - [Prod deployment process](#prod-deployment-process)
- [Updating Drupal core](#updating-drupal-core)
- [Updating a contrib module](#updating-a-contrib-module)
- [Installing a contrib module](#installing-a-contrib-module)
- [Patching Drupal core or a contrib module](#patching-drupal-core-or-a-contrib-module)
- [Theme](#theme)
  - [Node modules](#node-modules)
  - [Compiling Sass/SCSS to CSS](#compiling-sassscss-to-css)
  - [Minifying JS](#minifying-js)
  - [Gulp errors](#gulp-errors)
  - [Sass-lint and sass-lint-auto-fix](#sass-lint-and-sass-lint-auto-fix)
    - [Automatically fix Sass issues](#automatically-fix-sass-issues)
  - [Foundation](#foundation)
- [Site architecture](#site-architecture)
- [Composer](#composer)
- [Lando, PhpStorm & Xdebug](#lando-phpstorm--xdebug)
- [Troubleshooting](#troubleshooting)
  - [Image styles are not generating](#image-styles-are-not-generating)
- [Acquia Cloud notes](#acquia-cloud-notes)
  - [SSH](#ssh)

<!-- END doctoc generated TOC please keep comment here to allow auto update -->

# Purpose

Drupal 9 website for DoveLewis (https://www.dovelewis.org).

# Project branch strategy
    Dev                               :: Push automatically deploys to Dev env.
    Stage                             :: Push automatically deploys to Stage env.
    Prod                              :: Push automatically deploys to Prod env.
    DLM-ticket-number-description     :: Feature branch corresponding to a Jira ticket. Branch off of Dev and create a PR to merge back in.

# Recommended versions
    PHP         :: 7.4 (installed automatically by Lando)
    Node        :: 11.0.0 (installed automatically by Lando)
    Drush       :: >= 10.x (installed automatically by Lando)

# Codebase

The repo exists on Acquia and is forked on Github. Do not clone the Acquia repo. See ["Local dev setup using Lando"](#local-dev-setup-using-lando).

* Github: https://github.com/HelloWorldDevs/dovelewis
* Acquia Cloud: https://cloud.acquia.com/app/develop/applications/13fe84ab-6255-4cb1-8fd6-cb5a38621672

# Acquia

This site is hosted on Acquia. Acquia provides a dashboard from which you can create database backups etc.
See [External Resources](#external-resources) for a link to Acquia Cloud.

# External Resources

* Environments:
  * Local: https://dovelewis.lando
  * Dev: http://dovelewishze2ge4zwu.devcloud.acquia-sites.com
  * Staging/Test: http://dovelewisckfalsvsfs.devcloud.acquia-sites.com
  * Prod: https://www.dovelewis.org
* Acquia Cloud:
  * https://cloud.acquia.com/app/develop/applications/13fe84ab-6255-4cb1-8fd6-cb5a38621672
* Jira:
  * https://helloworlddevs.atlassian.net/secure/RapidBoard.jspa?rapidView=39&projectKey=DLM
* Confluence:
  * https://helloworlddevs.atlassian.net/wiki/spaces/DOV/pages/1206255765/DoveLewis+dovelewis.org

# Local dev setup using Lando

## (Step 1) Request access to Acquia

Ask Heather to request that DoveLewis IT adds you to their Acquia team. It sometimes takes a while for them to reply, but you'll receive an email invitation eventually.

If you haven't received an invite yet, you can proceed up to step 7 before you'll be blocked.

## (Step 2) Install Lando and Docker Desktop

Follow the recommended instructions for installing Lando if you haven't already. Docker Desktop will be installed as well.

https://docs.lando.dev/basics/installation.html#macos

## (Step 3) Git clone the repo

`git clone` the Github repo into whichever directory you prefer.

`cd` into the repo/project root.

## (Step 4) Configure Git

Modify your `.git/config` to match the following, replacing any existing remotes or branches. This is an attempt to keep the Acquia and Github repos in sync.

```
[checkout]
    defaultRemote = github
[push]
    default = current
[remote "github"]
    url = git@github.com:HelloWorldDevs/dovelewis.git
    fetch = +refs/heads/*:refs/remotes/github/*
    pushurl = git@github.com:HelloWorldDevs/dovelewis.git
    pushurl = dovelewis@svn-4707.devcloud.hosting.acquia.com:dovelewis.git
[remote "acquia"]
    url = dovelewis@svn-4707.devcloud.hosting.acquia.com:dovelewis.git
    fetch = +refs/heads/*:refs/remotes/acquia/*
    pushurl = git@github.com:HelloWorldDevs/dovelewis.git
    pushurl = dovelewis@svn-4707.devcloud.hosting.acquia.com:dovelewis.git
[branch "Dev"]
    remote = github
    merge = refs/heads/Dev
    rebase = true
[branch "Stage"]
    remote = github
    merge = refs/heads/Stage
    rebase = true
[branch "Prod"]
    remote = github
    merge = refs/heads/Prod
    rebase = true
```

## (Step 5) Configure /etc/hosts

Edit `/etc/hosts` on your local machine. Add these lines:

```
127.0.0.1				dovelewis.lando
```

Normally Lando will create site URLs in the format *.lndo.site. Because of the proxy settings we have in `.lando.yml`, those won’t be created. Instead we’ll have this nicer URL, but the trade off is we have to add it to our `/etc/hosts` file.

## (Step 6) Lando start

Run:

```
lando start
```

The first time you run this it will take a while. Eventually you’ll be given some URLs to access the site, however they will not work yet because we haven’t pulled the database yet. Lando attempts to create URLs based on the available ports on your machine. The URLs that will be most reliable/consistent will be https://dovelewis.lando or http://dovelewis.lando, however you may find that they are instead https://dovelewis.lando:444/ or http://dovelewis.lando:8000/. Make note of whatever URLs you’re given so we can try them later.

## (Step 7) Add SSH key to Acquia Cloud Dashboard

Follow the Acquia documentation to add your SSH public key to your Acquia profile:

https://docs.acquia.com/cloud-platform/manage/ssh/enable/add-key/

## (Step 8) Pull production database to local

Follow the steps under ["Pulling database to local"](#pulling-database-to-local) to pull the production database.

## (Step 9) Run database updates, import config and clear caches

To run database updates, import config and clear caches, run:

```
cd docroot
```

```
lando drupal-config-import
```

This is a wrapper around `drush updb -y; drush cim sync -y; drush cr`. You could call these directly instead.

_Always run one of these commands after pulling the database from a remote environment._

## (Step 10) Test your site

Go to one of the URLs Lando gave you in Step 6. If using Chrome you may have to use the “Advanced” option to bypass the SSL warning. For some reason Chrome does not like the way Lando signs SSL certificates. You will only have to do this the first time you visit the site after running lando start.

The site should load, however most images will be broken. Lets fix that.

## (Step 11) Resolve broken images

### Proxying using Resource Override

Because this is a large site and some of the images the client uploaded are insanely large,
it is not recommended to pull the `sites/default/files` directory from production.

Instead, we highly recommend you save some space on your machine by proxying images from production using the [Resource Override Chrome plugin](https://helloworlddevs.atlassian.net/wiki/spaces/HWD/pages/1208877057/Developer+Tools#Resource-Override).

## (Step 12) Login to site

* Login to site by going to /user.
* To get credentials, search LastPass for "dovelewis.org: Admin role user".

# Lando basics

https://helloworlddevs.atlassian.net/wiki/spaces/HWD/pages/1635418113/Lando+basics

# Drush

_NOTE: In order to run a Drush command, you must be in `docroot`._

Drush is a CLI tool for Drupal. Lando installs it automatically and you can use it by running:

```
lando drush COMMAND
```

## Useful Drush commands

### Clear caches

```
lando drush cr
```

### Run database updates

```
lando drush updb -y
```

### Import config

```
lando drush cim sync
```

### Export config

```
lando drush cex sync
```

### List installed modules

```
lando drush pm-list
```

## Drush aliases

You can use Drush aliases to run Drush commands on remote Acquia environments from your local environment.
This may be helpful for [Deployment](#deployment).

If you followed the local dev setup steps exactly, you should be able to use them.

The possible aliases are:

* `@dovelewis.dev` (Dev)
* `@dovelewis.test` (Stage)
* `@dovelewis.prod` (Prod)

For example, to clear caches on Dev, run:

```
lando drush @dovelewis.dev cr
```


# Development process

## Drupal Configuration Synchronization

See documentation in HW Confluence:

https://helloworlddevs.atlassian.net/l/c/tYCo1PA9

### Importing config

To import config, run:

```
lando drush cim sync
```

After pulling the database and before starting a new ticket, **it is extremely important to import config**. This will ensure that if you later export config, you are not accidentally deleting another developer's work.

You can also import config by running:

```
lando drupal-config-import
```

This also runs database updates and clears caches.

### Exporting config

To export config, run:

```
lando drush cex sync
```

### sync vs. vcs and ignoring webform config

Acquia advises us to use `vcs` instead of `sync`, however this causes the Config Ignore module to
not work. Because the client can create and edit Webforms on production, which result in config changes,
we need to be able to ignore Webform related config in order to avoid overwriting those changes when we deploy.
It seems that using `sync` instead does not cause any issues and allows Config Ignore to work.
Possibly this is because of the setting of `$settings['config_sync_directory']` and `$settings['config_vcs_directory']`
to the same directory in `settings.php`.

_NOTE:_ If importing config on Dev/Stage/Prod environment using a Drush alias always choose `sync`. Choosing `vcs` instead
may overwrite the client's Webform changes because the Config Ignore module will not work.

## Pulling database to local

@TODO: We don't have any scripts yet to integrate Acquia Cloud with Lando in order to easily pull the database and/or files. If you want to help with this, see these for inspiration:

* https://docs.acquia.com/cloud-platform/develop/dev-environment/
* Keep an eye on the Lando Acquia recipe being developed here: https://github.com/lando/lando/issues/2844

### Process

* Login to Acquia Cloud.
* Click "Prod".
* In the left sidebar, click "Databases".
* Click "Back Up" (looks like an accordion title but it's actually a button).
* Wait for database backup to complete.
* When it finishes, click "Download" next to the new backup. Download it to the project/repo into the `db/` directory. This is necessary for Lando to be able to access the file.
* Run `gunzip ../../../db/filename.sql.gz`.
* Run `lando drush sql-drop` to drop current local database.
* Run `lando db-import ../../../db/filename.sql` to import the database.
* Delete the .sql.gz and .sql files from `db/`when the import completes.

## Creating a new feature branch

* `git checkout Dev; git pull;`
* Only if necessary [pull a fresh copy of the Prod DB](#pulling-database-to-local).
* Run `lando drupal-config-import`. This will run database updates, import config and clear caches. It will import any config changes that have been committed by another developer on the `Dev` branch.
* Run `git checkout -b DLM-TICKET_NUMBER` to create a new feature branch.

## Exporting config and committing code

* Commit your code changes. Please start your commit message with "DLM-TICKET_NUMBER:".
* Run `lando drush cex sync` to export config. Drupal will compare your database to the config yml
files and modify or create new yml files accordingly.
* Inspect the config changes _carefully_ to ensure that you are not committing any changes
that you did not intend to make.
* You will occasionally see Webform related config changes that you did not make. You can commit these. We are using
the Config Ignore module to ignore Webform config, however it is only ignored on _import_. What this means is that
changes made to Webforms on Prod are never overwritten. However, they will still be exported the next time we pull the
Prod database and run config export. Note that you will never be able to deploy Webform changes. You must make
them manually on Prod.
* Commit your config changes.

## Pushing a new branch

Because of the unusual Git config for this project, when you run `git push` to push
a new branch for the first time, you are not given a helpful message.
Here is the pattern to follow:
`git push -u github DLM-15-retina-images`

## Creating a Pull Request

You can create PRs using GitHub: https://github.com/HelloWorldDevs/dovelewis/pulls

Ensure that you've branched off of `Dev` to create your feature branch,
and that your PR merges your feature branch back into `Dev`.

## Merging a Pull Request

Because we have two repos that we're trying to keep in sync (Github and Acquia), there are a few more steps
after you've clicked the "Merge pull request" button in Github.

If your `Dev` branch is configured in `.git/config` like this (recommended):

```
[branch "Dev"]
    remote = github
    merge = refs/heads/Dev
```

Run this after merging a PR:

```
git checkout Dev; git pull; git push;
```

If your `Dev` branch is configured in `.git/config` like this (not recommended):

```
[branch "Dev"]
    remote = acquia
    merge = refs/heads/Dev
```

The process is slightly longer:

```
git checkout Dev; git pull; git fetch --all; git merge github/Dev; git push;
```

Regardless, assuming these pushed without error, the merged commits from Github have now been pushed to Acquia
and the two repos should be in sync. *If you do not do this, the commits won't actually be deployed to Dev env.*

### Conflicts in build directory that prevent merging into Dev

Very often there will be conflicts between a feature branch and `Dev` due to the fact that we are committing our compiled
CSS and not compiling SCSS to CSS on the server. If you see any conflicts within `docroot/themes/dovelewis/build`, the reason is
that changes have been made to the SCSS or JS in both `Dev` and the feature branch and Git cannot rectify the two different
compiled files.

*If the build directory is where the merge conflict is*, follow these steps to resolve it:

* Checkout and pull `Dev`.
* Checkout and pull the feature branch. If this is your first time checking it out, you'll need to set it to track to Github.
(follow the prompt but change `acquia` to `github`).
* `git merge Dev`
* You'll be told there are merge conflicts within `docroot/themes/dovelewis/build`.
* Open a new shell tab and run `cd docroot/themes/dovelewis; lando gulp guild;`.
* Wait for Gulp to recompile the CSS or JS.
* When it's done, run `git add docroot/themes/dovelewis/build`.
* Run `git commit` to finish the merge. Run `git push` to push the new compiled CSS or JS to your feature branch.
* Go back to the PR on Github. You should see there is no longer a conflict.
* Read the "Merging a Pull Request" section above.

# Deployment

The sites/environments are hosted on Acquia Cloud:

https://cloud.acquia.com/app/develop/applications/13fe84ab-6255-4cb1-8fd6-cb5a38621672

[Acquia Cloud Hooks](https://github.com/acquia/cloud-hooks) are used to run database updates and config import
whenever code is updated on an environment (whenever we either push to `Dev`/`Stage`/`Prod` branches). These are configured
in `hooks/common/post-code-update`. There are many sample scripts in `hooks/samples` in case you want to add another.
Please read the Acquia Cloud Hooks documentation.

You do not need to set up Drush aliases in order to deploy, but they may be useful for double checking
that database updates and config import have been run. (See [Drush aliases](#drush-aliases))

## Dev deployment process

* `Dev` branch deploys to the Dev environment when pushed to.
* Either push to the `Dev` branch or merge a PR into `Dev`.
* Optionally, verify database updates and config import were run:
    ```
    lando env-config-import
    ```
    and choose "dev".
    Alternatively you can run:
    ```
    lando drush @dovelewis.dev updb -y
    lando drush @dovelewis.dev cim sync
    lando drush @dovelewis.dev cr
    lando drush @dovelewis.dev status
    ```

## Stage/Test deployment process

* `Stage` branch deploys to the Stage environment when pushed to.
* Either:
  1. Merge `Dev` into  `Stage` with a PR.
  2. Or merge `Dev` into `Stage` manually:
    ```
    git checkout Dev; git pull; git checkout Stage; git pull; git merge Dev
    git push
    ```
* Optionally, verify database updates and config import were run:
    ```
    lando env-config-import
    ```
    and choose "test".
    Alternatively you can run:
    ```
    lando drush @dovelewis.test updb -y
    lando drush @dovelewis.test cim sync
    lando drush @dovelewis.test cr
    lando drush @dovelewis.test status
    ```

## Prod deployment process

* `Prod` branch deploys to the Prod environment when pushed to.
* Merge `Stage` into `Prod` manually:
    ```
    git checkout Stage; git pull; git checkout Prod; git pull; git merge Stage
    git push
    ```
* Optionally, verify database updates and config import were run:
    ```
    lando env-config-import
    ```
    and choose "prod".
    Alternatively you can run:
    ```
    lando drush @dovelewis.dev updb -y
    lando drush @dovelewis.dev cim sync
    lando drush @dovelewis.dev cr
    lando drush @dovelewis.dev status
    ```
* Occasionally you may need to login to Acquia Cloud and clear the Varnish cache.

# Updating Drupal core

Drupal core and contrib modules should be updated using Composer.

For this project, core can be updated by running:

`lando composer update drupal/core-recommended --with-dependencies`

See documentation in HW Confluence (but always run Composer commands using `lando composer`):

https://helloworlddevs.atlassian.net/l/c/tamEHpst

After updating, test the following functionality to ensure the patch was applied successfully or is no longer necessary.

* There are 1 patch currently applied to Drupal core:
    1. "Allow image styles in CKEditor"
        * For information about this patch: https://www.drupal.org/project/drupal/issues/2061377

# Updating a contrib module

See documentation in HW Confluence (but always run Composer commands using `lando composer`):

https://helloworlddevs.atlassian.net/l/c/GRzuiByr

# Installing a contrib module

See documentation in HW Confluence (but always run Composer commands using `lando composer`):

https://helloworlddevs.atlassian.net/l/c/0fknkL3T

# Patching Drupal core or a contrib module

See documentation in HW Confluence (but always run Composer commands using `lando composer`):

https://helloworlddevs.atlassian.net/l/c/PUHQ1DhR

# Theme

The frontend Drupal theme is located at `docroot/themes/dovelewis`.

## Node modules

Lando installs Node and Gulp and runs `npm install` within the `node` container/service the first time you run `lando start`,
or whenever you run `lando rebuild`. You do not need to run `lando npm install` yourself, but you can if you want.
So long as you preface Gulp commands with `lando` you don't need to worry about switching your local environment to use the correct Node version.

## Compiling Sass/SCSS to CSS

To compile, run from `docroot/themes/dovelewis`:

```
lando gulp build
```

To compile and watch, run:

```
lando gulp watch
```

## Minifying JS

To minify, run from `docroot/themes/dovelewis`:

```
lando gulp build
```

To minify and watch, run from `docroot/themes/dovelewis`:

```
lando gulp watch
```

## Gulp errors

If Gulp commands fail, ensure that you are prefacing the command with `lando`.

Make sure you are in `docroot/themes/dovelewis`.

## Sass-lint and sass-lint-auto-fix

Sass-lint and sass-lint-auto-fix are used to lint and automatically fix Sass files.
These are configured by their respective files in the `dovelewis` theme:
* `.sass-lint.yml`
* `.sass-lint-auto-fix.yml`

To run sass-lint and get a report of issues found, run from `docroot/themes/dovelewis`:

```
lando gulp lint-scss
```

or

```
lando npm run lint -s
```

### Automatically fix Sass issues

To automatically fix Sass issues that were found, run from `docroot/themes/dovelewis`:

```
lando npm run lint:fix
```

## Foundation

Foundation 6 is used for the FE of the site.

* To override Foundation SCSS variables, modify ```docroot/themes/dovelewis/scss/config/_foundation-settings.scss```.
* We are using the Foundation XY Grid, which is built using Flexbox. Please avoid overriding the intended use of the Foundation grid system.
* Note that XY Grid system is tied directly to the Container and Column Paragraph Types in Drupal. See [Drupal Architecture](#site-architecture) for more information.
* Refer to the documentation whenever you are having trouble accomplishing something:
https://foundation.zurb.com/sites/docs/xy-grid.html
* We are loading only the SCSS that we need in ```docroot/themes/dovelewis/scss/style.scss```.
* We are loading only the JS we need in ```docroot/themes/dovelewis/dovelewis.libraries.yml```.

# Site architecture

See documentation in HW Confluence:

https://helloworlddevs.atlassian.net/l/c/CqokeB5X

# Composer

When running Composer commands, use `lando composer` to ensure that the correct version of Composer is used.

Composer is used in Drupal 9 to update or install modules or update Drupal core.
Because we're tracking Composer directories in Git, you do _not_ need to run `lando composer install`
as part of your local setup. The only time you will need to run Composer commands is
if you are updating or installing a module or updating Drupal core.

# Lando, PhpStorm & Xdebug

See documentation in HW Confluence:

https://helloworlddevs.atlassian.net/wiki/spaces/HWD/pages/899416079/Step+debugging+with+Lando+PhpStorm+Xdebug+and+Drupal

# Troubleshooting

## Image styles are not generating

* Check /admin/reports/status and make sure there are no errors regarding the file system.
* Usually this is a result of incorrect permissions on the files directory.
To set permissions and clear caches run:
  * `cd docroot/sites/default; sudo chmod -R 775 files; lando drush cr;`
* If this doesn't work, try clearing caches using the Devel module:
  * `lando drush en devel -y;`
  * Refresh the page. Click the "Refresh Cache" button in the admin toolbar.
  * `lando drush pm-uninstall devel -y;`
* Ensure that do NOT have Resource Override enabled if you're attempting to upload a new image locally. This is because when Resource Override is enabled, images will be proxied from production. However, when uploading a new image locally, the image will not exist on production and cannot be proxied.
<hr>
If you encounter the following error:

`You have requested a non-existent service "cache.backend.null"`

Run `lando rebuild -y` - this will cause the rebuilding of the lando env and will add `development.services.yml` & `settings.local.php` to
your codebase, which will eliminate this problem. After doing this once, you _shouldn't_ run into this problem again.

# Acquia Cloud notes

## SSH

You can get the SSH URL for any environment by clicking on the name of the environment. The SSH URL is on the Overview tab.
