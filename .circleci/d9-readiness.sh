#!/usr/bin/env bash
IFS=$'\n\t'
set -euo pipefail

##
# Test for deprecated code in GovCMS profile, themes, and its contrib.
# @see https://github.com/mglaman/drupal-check <-- we're using this here.
# @see https://github.com/drupal8-rector/drupal8-rector
# @see https://www.drupal.org/project/upgrade_status

LOG=/app/d9-readiness.log

rm -f web/profiles/contrib/govcms/composer.json
rm -f "${LOG}" && touch "${LOG}"

echo -e "\n [💥] GovCMS Distribution Drupal 9 Deprecation Testing log [💥]" | tee -a "${LOG}"
echo -e "\n [PROFILE] web/profiles/contrib/govcms" | tee -a "${LOG}"
set +e
vendor/bin/drupal-check --no-progress web/profiles/contrib/govcms >> "${LOG}"
set -e

for theme in web/themes/contrib/*; do
    if [ -d "${theme}" ]; then
        echo -e "\n [THEME] ${theme}" | tee -a "${LOG}"
        set +e
        vendor/bin/drupal-check --no-progress "${theme}" >> "${LOG}"
        set -e
    fi
done

for module in web/modules/contrib/*; do
    if [ -d "${module}" ]; then
        echo -e "\n [MODULE] ${module}" | tee -a "${LOG}"
        set +e
        vendor/bin/drupal-check --no-progress "${module}" >> "${LOG}"
        set -e
    fi
done

WITH_SUMMARY=`cat "${LOG}" | grep "[ ]*\[" ; cat "${LOG}"`
echo "$WITH_SUMMARY" > "${LOG}"
