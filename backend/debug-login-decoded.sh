#!/bin/sh
set -e
COOKIE=/tmp/debug-cookies.txt
rm -f "$COOKIE"
curl -s -c "$COOKIE" -b "$COOKIE" -H 'Accept: application/json' http://localhost:8000/sanctum/csrf-cookie -o /dev/null
# Extract encoded XSRF token and URL-decode it for the header
XSRF_ENCODED=$(awk '/XSRF-TOKEN/{print $7}' "$COOKIE")
XSRF_DECODED=$(printf '%b' "$(echo "$XSRF_ENCODED" | sed 's/+/ /g; s/%/\\x/g')")
echo "XSRF decoded prefix: $(echo "$XSRF_DECODED" | cut -c1-40)"
curl -s -c "$COOKIE" -b "$COOKIE" -H 'Accept: application/json' -H 'Content-Type: application/json' -H "X-XSRF-TOKEN: $XSRF_DECODED" -d '{"email":"admin@siderae.test","password":"password"}' http://localhost:8000/login -w '\nLOGIN_STATUS=%{http_code}\n'
echo "Cookies after login:"
cat "$COOKIE"
curl -s -b "$COOKIE" -H 'Accept: application/json' http://localhost:8000/api/me -w '\nME_STATUS=%{http_code}\n'
