#!/bin/sh
#
# Runs database updates, config import and clears caches in the target environment.

site="$1"
target_env="$2"
source_branch="$3"
deployed_tag="$4"
repo_url="$5"
repo_type="$6"

# Fallback to the dev target environment if target_env isn't set to an expected environment
if [[ "$target_env" != 'dev' && "$target_env" != 'test' && "$target_env" != 'prod' ]]; then
  target_env="dev"
fi


echo "Running database updates, config import and clearing caches for $site.$target_env"

/usr/local/bin/drush10 @$site.$target_env updb -y
/usr/local/bin/drush10 @$site.$target_env cim sync -y
/usr/local/bin/drush10 @$site.$target_env cr


echo "Clearing Twig cache for $site.$target_env"

if [ "$target_env" = "dev" ];
then
  /usr/local/bin/drush10 @$site.$target_env --uri=http://dev-site.dovelewis.org/ ev '\Drupal\Core\PhpStorage\PhpStorageFactory::get("twig")->deleteAll();'
elif [ "$target_env" = "test" ];
then
  /usr/local/bin/drush10 @$site.$target_env --uri=http://stage-site.dovelewis.org/ ev '\Drupal\Core\PhpStorage\PhpStorageFactory::get("twig")->deleteAll();'
elif [ "$target_env" = "prod" ];
then
  /usr/local/bin/drush10 @$site.$target_env --uri=https://www.dovelewis.org/ ev '\Drupal\Core\PhpStorage\PhpStorageFactory::get("twig")->deleteAll();'
fi


echo "Clearing Varnish for $site.$target_env"

# Clear Varnish cache.
# Based on:
# https://github.com/acquia/cloud-hooks/issues/57

client_key="b262ffd8-3a45-4a2f-ac9e-a15b860306c4"
client_secret="BEelYMNjAACs/sZJoXa/fCyUQMruW02d4OnvOBThktE="

# Generate an access token
access_token=$(curl https://accounts.acquia.com/api/auth/oauth/token --data-urlencode "client_id=$client_key" --data-urlencode "client_secret=$client_secret" --data-urlencode "grant_type=client_credentials" | grep -Po '"'"access_token"'"\s*:\s*"\K([^"]*)')

# Get the application uuid filtering where the name field is "dovelewis"
application_uuid=$(curl -X GET --header "Authorization: Bearer $access_token" --header "Content-Type: application/json" "https://cloud.acquia.com/api/applications?filter=name%3Ddovelewis" | grep -Po '"'"uuid"'"\s*:\s*"\K([^"]*)' | head -1)

# Get the environment id for the target environment
environment_id=$(curl -X GET --header "Authorization: Bearer $access_token" --header "Content-Type: application/json" "https://cloud.acquia.com/api/applications/$application_uuid/environments?filter=name%3D$target_env" | grep -Po '"'"id"'"\s*:\s*"\K([^"]*)')

# Parses the hostname from domains json response, loops through the number of domains, and wraps them in valid json leaving the comma off the last item
domains_json="{\"domains\": ["
#domains_json+=$(curl -X GET --header "Authorization: Bearer $access_token" --header "Content-Type: application/json" "https://cloud.acquia.com/api/environments/$environment_id/domains" | grep -Po '"'"hostname"'"\s*:\s*"\K([^"]*)' | sed 's/^/\"/;s/$/\"/;$!s/$/\,/')

if [ "$target_env" = "dev" ];
then
  domains_json="$domains_json \"dev-site.dovelewis.org\""
elif [ "$target_env" = "test" ];
then
  domains_json="$domains_json \"stage-site.dovelewis.org\""
elif [ "$target_env" = "prod" ];
then
  domains_json="$domains_json \"www.dovelewis.org\""
fi

domains_json="$domains_json ]}"

echo "domains_json: $domains_json"

# Clear Varnish cache for the specified domains attached to this environment
curl -X POST --header "Authorization: Bearer $access_token" --header "Content-Type: application/json" --data "$domains_json" "https://cloud.acquia.com/api/environments/$environment_id/domains/actions/clear-varnish"


echo "Clearing Drupal caches for $site.$target_env"

/usr/local/bin/drush10 @$site.$target_env cr
