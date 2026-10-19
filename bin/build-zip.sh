#!/usr/bin/env bash
# Build case-study-manager.zip from tracked files only.
# Exclusions are the gitignore-style patterns in .distignore.
# The archive's top-level folder is case-study-manager. Used by .github/workflows/release.yml.
# Run locally with `bash bin/build-zip.sh` to inspect the package.
set -euo pipefail

cd "$(dirname "$0")/.."

if [[ ! -f .distignore ]]; then
	echo "error: .distignore is missing" >&2
	exit 1
fi

rm -rf build case-study-manager.zip
mkdir -p build/case-study-manager

# Tracked files only (never local settings, vendor/, or untracked scratch files).
# --no-index lets an already-tracked path match .distignore.
while IFS= read -r -d '' file; do
	if git -c core.excludesFile=.distignore check-ignore -q --no-index -- "$file"; then
		continue
	fi
	mkdir -p "build/case-study-manager/$(dirname -- "$file")"
	cp -- "$file" "build/case-study-manager/$file"
done < <(git ls-files -z)

required=(
	case-study-manager.php
	uninstall.php
	readme.txt
	LICENSE
	includes/class-case-study-manager-plugin.php
	languages/case-study-manager.pot
)
for file in "${required[@]}"; do
	if [[ ! -f "build/case-study-manager/$file" ]]; then
		echo "error: package is missing $file" >&2
		exit 1
	fi
done

( cd build && zip -qr9 ../case-study-manager.zip case-study-manager )
unzip -l case-study-manager.zip
