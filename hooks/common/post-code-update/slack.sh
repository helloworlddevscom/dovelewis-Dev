#!/bin/sh
#
# Sends notification to #notification-dovelewis-deploy Slack channel.
#
# In accordance with hooks/samples/slack/README.md, the Slack webhook used is
# configured outside of the repo on the Acquia Cloud server.
# SSH into the server and you should find it at /home/dovelewis/slack_settings.
#
# Usage: post-code-update site target-env source-branch deployed-tag repo-url
#                         repo-type

site="$1"
target_env="$2"
source_branch="$3"
deployed_tag="$4"
repo_url="$5"
repo_type="$6"

FILE=$HOME/slack_settings

if [ -f $FILE ]; then
  # Load the Slack webhook URL (which is not stored in this repo).
  . $HOME/slack_settings

  # Post deployment notice to Slack

  if [ "$source_branch" != "$deployed_tag" ]; then
    curl -X POST --data-urlencode "payload={\"username\": \"Acquia Cloud\", \"text\": \"An updated deployment has been made to *$site.$target_env* using branch *$source_branch* as *$deployed_tag*.\", \"icon_emoji\": \":acquiacloud:\"}" $SLACK_WEBHOOK_URL
  else
    curl -X POST --data-urlencode "payload={\"username\": \"Acquia Cloud\", \"text\": \"An updated deployment has been made to *$site.$target_env* using tag *$deployed_tag*.\", \"icon_emoji\": \":acquiacloud:\"}" $SLACK_WEBHOOK_URL
  fi
else
  echo "File $FILE does not exist."
fi
