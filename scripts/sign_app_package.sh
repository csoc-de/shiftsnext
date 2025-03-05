#!/bin/bash

source .env

docker exec -u 33 master-nextcloud-1 php /var/www/html/occ integrity:sign-app --privateKey=${SHIFTS_APP_PRIVATE_KEY_PATH} --certificate=${SHIFTS_APP_CERT_PATH} --path=/var/www/html/apps-extra/shifts
tar -czf build/shifts-${SHIFTS_APP_VERSION}.tar.gz -C build/sign shifts

SIGNATURE=$(openssl dgst -sha512 -sign ${SHIFTS_APP_PRIVATE_KEY_PATH} build/shifts-${SHIFTS_APP_VERSION}.tar.gz | openssl base64 | sed -z 's/\n//g');

echo "{" > release.json
echo "\t\"download\":\"https://github.com/csoc-de/Shifts/releases/download/v${SHIFTS_APP_VERSION}/shifts-${SHIFTS_APP_VERSION}.tar.gz\"," >> release.json
echo "\t\"nightly\":false," >> release.json
echo "\t\"signature\":\"${SIGNATURE}\"" >> release.json
echo "}" >> release.json

# TODO: Push to github
# TODO: Create tag
# TODO: Create release with artifacts

curl -X POST https://apps.nextcloud.com/api/v1/apps/releases -H "Authorization: Token ${SHIFTS_APP_APP_STORE_TOKEN}" -H "Content-Type: application/json" --data @release.json
