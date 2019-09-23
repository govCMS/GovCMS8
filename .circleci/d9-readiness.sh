#!/usr/bin/env bash
IFS=$'\n\t'
set -euo pipefail

LOG=/app/d9-readiness.log

composer -n require mglaman/drupal-check
rm -f web/profiles/contrib/govcms/composer.json
rm -f "${LOG}" && touch "${LOG}"

echo echo -e "\nRUNNING DEPRECATION TEST FOR: GovCMS Profile" | tee -a "${LOG}"
set +e
vendor/bin/drupal-check --no-progress web/profiles/contrib/govcms > "${LOG}" | tee -a "${LOG}"
set -e

for module in web/modules/contrib/*; do
    if [ -d "${module}" ]; then
        echo -e "\nRUNNING DEPRECATION TEST FOR: ${module}" | tee -a "${LOG}"
        set +e
        vendor/bin/drupal-check --no-progress "${module}" >> "${LOG}"
        set -e
    fi
done

for theme in web/themes/contrib/*; do
    if [ -d "${theme}" ]; then
        echo -e "\nRUNNING DEPRECATION TEST FOR: ${theme}" | tee -a "${LOG}"
        set +e
        vendor/bin/drupal-check --no-progress "${theme}" >> "${LOG}"
        set -e
    fi
done
