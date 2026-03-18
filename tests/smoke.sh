#!/usr/bin/env bash
set -euo pipefail

BASE="http://localhost:8888"
FAIL=0

# Wait for WordPress to be ready
echo "Waiting for WordPress..."
for i in $(seq 1 30); do
  if curl -s -o /dev/null -w '' "$BASE/" 2>/dev/null; then
    break
  fi
  sleep 1
done

check_page() {
  local path="$1"
  local label="$2"
  local url="${BASE}${path}"

  STATUS=$(curl -s -o /dev/null -w '%{http_code}' "$url")
  if [ "$STATUS" -ge 500 ]; then
    echo "FAIL [$STATUS] $label ($url)"
    FAIL=1
  elif [ "$STATUS" -ge 400 ]; then
    echo "WARN [$STATUS] $label ($url)"
  else
    echo " OK  [$STATUS] $label ($url)"
  fi
}

echo "=== Smoke Testing Key Pages ==="

check_page "/"               "Homepage (front-page.php)"
check_page "/pqrrs/"         "PQRRS (page-pqrrs.php)"
check_page "/transparencia/" "Transparencia (page-transparencia.php)"
check_page "/contactenos/"   "Contáctenos (page-contactenos.php)"
check_page "/blog/"          "Blog listing (index.php)"
check_page "/?s=prueba"      "Search (search.php)"
check_page "/nonexistent-page-12345/" "404 page (404.php)"

# Check that PQRRS form JS is loaded (the bug we fixed)
echo ""
echo "=== Asset Checks ==="
PQRRS_HTML=$(curl -s "$BASE/pqrrs/")
if echo "$PQRRS_HTML" | grep -q 'pqrrs-form.js'; then
  echo " OK  PQRRS form JS is enqueued"
else
  echo "FAIL PQRRS form JS not found in page source"
  FAIL=1
fi

if echo "$PQRRS_HTML" | grep -q 'eeppjPqrrs'; then
  echo " OK  PQRRS AJAX config is present"
else
  echo "FAIL PQRRS AJAX config (eeppjPqrrs) not found"
  FAIL=1
fi

echo ""
if [ "$FAIL" -ne 0 ]; then
  echo "SMOKE TEST FAILED"
  exit 1
fi
echo "All smoke tests passed."
