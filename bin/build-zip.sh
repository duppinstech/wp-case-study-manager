#!/usr/bin/env bash
# Build case-study-manager.zip from tracked files only, excluding development files.
# The archive's top-level folder is case-study-manager. Used by .github/workflows/release.yml.
# Run locally with `bash bin/build-zip.sh` to inspect the package.
set -euo pipefail

cd "$(dirname "$0")/.."
rm -rf build case-study-manager.zip
mkdir -p build/case-study-manager

# Tracked files only (never local settings, vendor/, or untracked scratch files), minus dev-only paths.
git ls-files -z \
  | grep -zvE '^(\.github/|\.claude/|tests/|bin/|docs/|\.gitignore$|\.gitattributes$|\.mcp\.json$|AGENTS\.md$|CLAUDE\.md$|CLAUDE\.local\.md\.example$|OWNERSHIP\.md$|CONTRIBUTING\.md$|INTENT\.md$|PLAN\.md$|SECURITY\.md$|composer\.(json|lock)$|phpcs\.xml\.dist$|phpunit\.xml\.dist$)' \
  | xargs -0 -I{} cp --parents {} build/case-study-manager/

( cd build && zip -qr9 ../case-study-manager.zip case-study-manager )
unzip -l case-study-manager.zip
