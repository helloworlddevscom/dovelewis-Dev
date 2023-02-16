#!/usr/bin/env bash

# This file is configured in .lando.yml. Run: lando env-deploy
# It will trigger a deployment to the chosen environment and
# then runs database updates, reverts features and clears caches.
# You cannot choose the dev environment because it is not necessary. The dev
# environment is deployed to whenever the master branch is pushed to, and
# database updates, config import and cache clear is handled automatically
# by private/scripts/quicksilver/drush_update_import/drush_update_import.php.

NORMAL="\033[0m"
RED="\033[31m"
YELLOW="\033[32m"
ORANGE="\033[33m"
PINK="\033[35m"
BLUE="\033[34m"

echo
echo -e "${ORANGE}Are you sure you want to deploy to a remote Pantheon environment? (y/n):${BLUE}"
echo
read REPLY
if [[ "$REPLY" =~ ^[NnNono]$ ]]
then
  exit 1
fi

echo
echo -e "${ORANGE}Enter your Pantheon account email. You will need a machine token configured in your Pantheon account for this to work:${BLUE}"
echo
read REPLY_EMAIL
echo

terminus auth:login --email "$REPLY_EMAIL"

echo
echo -e "${ORANGE}Enter the environment to deploy to (test/live):${BLUE}"
echo
read REPLY_ENV
if [ "$REPLY_ENV" = "live" ] || [ "$REPLY_ENV" = "test" ]
then
  if [ "$REPLY_ENV" = "live" ]
  then
    echo
    echo -e "${ORANGE}Are you sure you want to deploy to and run database updates, revert features and clear caches on prod? This will wipe out any config changes that are currently on prod. (y/n):${BLUE}"
    echo
    read REPLY_CONFIRM
    if [[ "$REPLY_CONFIRM" =~ ^[Nn]$ ]]
    then
      exit 1
    fi
  fi

  echo
  echo -e "${ORANGE}Optionally enter a deploy message:${BLUE}"
  echo
  read REPLY_MESSAGE
  if [ -n "$REPLY_MESSAGE" ]
  then
    DEPLOY_MESSAGE="$REPLY_MESSAGE"
  else
    DEPLOY_MESSAGE=""
  fi

  echo
  echo -e "${YELLOW}Deploying to $REPLY_ENV env...${NORMAL}"
  echo

  terminus env:deploy solar4schools."$REPLY_ENV" --note "$DEPLOY_MESSAGE"

  echo
  echo -e "${YELLOW}Running database updates, reverting features and clearing caches on $REPLY_ENV env...${NORMAL}"
  echo

  terminus remote:drush solar4schools."$REPLY_ENV" -- updb -y

  echo
  echo -e "${YELLOW}Reverting features...${NORMAL}"
  echo

  terminus remote:drush solar4schools."$REPLY_ENV" -- fra -y

  echo
  echo -e "${YELLOW}Clearing caches...${NORMAL}"
  echo

  terminus remote:drush solar4schools."$REPLY_ENV" cc all

  echo
  echo -e "${YELLOW}Running drush fl and status so you know what's up...${NORMAL}"
  echo

  terminus remote:drush solar4schools."$REPLY_ENV" fl
  terminus remote:drush solar4schools."$REPLY_ENV" status
else
  echo
  echo -e "${RED}Please provide a valid env to deploy to. Choose between 'test' or 'live'. You can not deploy to dev env or a multidev env.${NORMAL}"
  echo
  exit 1
fi
