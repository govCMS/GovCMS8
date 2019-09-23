#!/usr/bin/env bash
IFS=$'\n\t'
set -euo pipefail

LOG=/app/d9-readiness.log

composer require mglaman/drupal-check
rm -f web/profiles/contrib/govcms/composer.json
rm -f "${LOG}" && touch "${LOG}"

set +e
echo echo -e "\nRUNNING DEPRECATION TEST FOR: GovCMS Profile"
vendor/bin/drupal-check --no-progress web/profiles/contrib/govcms > "${LOG}"
set -e

for module in web/modules/contrib/*; do
    if [ -d "${module}" ]; then
        echo -e "\nRUNNING DEPRECATION TEST FOR: ${module}"
        set +e
        vendor/bin/drupal-check --no-progress "${module}" >> "${LOG}"
        set -e
    fi
done
