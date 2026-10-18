#!/bin/sh
# PreToolUse. Exit 0 allows the write. Exit 2 blocks it.
# Blocks secret filenames. Does not block CLAUDE.local.md or settings.local.json.

input=$(cat)
path=$(printf '%s' "$input" | sed -n 's/.*"file_path"[[:space:]]*:[[:space:]]*"\([^"]*\)".*/\1/p' | head -n 1)

if [ -z "$path" ]; then
  exit 0
fi

normalized=$(printf '%s' "$path" | tr '\\' '/')
base=$(basename "$normalized")
block=0

case "$base" in
  .env|.env.*|wp-config.php|credentials.json|id_rsa|id_rsa.*|*.pem|*.p12|*.pfx|*.key)
    block=1
    ;;
esac

case "$normalized" in
  */.env|*/.env.*|*/wp-config.php|*/credentials.json|*/id_rsa|*/id_rsa.*|*.pem|*.p12|*.pfx|*.key)
    block=1
    ;;
esac

if [ "$block" -eq 1 ]; then
  echo "Blocked a write to a sensitive path: $normalized" >&2
  exit 2
fi

exit 0
