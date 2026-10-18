---
description: Package a Case Study Manager release without uploading it anywhere
argument-hint: "[version]"
---

Prepare a release of Case Study Manager. Requested version: $ARGUMENTS

This is a WordPress plugin package, not a server deploy.

1. Read `OWNERSHIP.md`, `PLAN.md`, and `.claude/agents/release-reviewer.md`.
2. Confirm the plugin header, `CSM_VERSION` fallback, `readme.txt` stable tag, and changelog are one version. If `$ARGUMENTS` names a version, use that. Otherwise stop and ask Odell for the version.
3. Confirm copyright still names Odell Duppins / Duppins Technology.
4. Run `.claude/workflows/departmental-review.md` if this release has not already cleared it.
5. Build a zip of the plugin folder that includes PHP, CSS, `readme.txt`, `LICENSE`, `uninstall.php`, and `languages/`. Leave out `.git`, `.claude`, `CLAUDE.local.md`, `.mcp.json`, and agent markdown.
6. Do not upload the zip, tag, or push unless Odell names the destination and asks for that upload.

Delegate to `release-reviewer`, then `security-reviewer`, before the zip is treated as final.
