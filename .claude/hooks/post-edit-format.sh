#!/bin/sh
# PostToolUse. Lint PHP files that were just written, then format them when phpcbf is installed.
# Exit 0 allows. Exit 2 asks the session to fix a syntax or standards error.

if ! command -v php >/dev/null 2>&1; then
  echo "php is not on PATH. PHP syntax was not checked." >&2
  exit 0
fi

exec php -r '
$raw = stream_get_contents(STDIN);
$data = json_decode($raw, true);
$path = "";
if (is_array($data) && isset($data["tool_input"]["file_path"])) {
    $path = (string) $data["tool_input"]["file_path"];
}
if ($path === "" || substr($path, -4) !== ".php" || !is_file($path)) {
    exit(0);
}
passthru("php -l " . escapeshellarg($path), $code);
if ($code !== 0) {
    fwrite(STDERR, "PHP syntax check failed for " . $path . "\n");
    exit(2);
}
$root = getenv("CLAUDE_PROJECT_DIR");
if (!$root) {
    $root = getcwd();
}
$phpcbf = $root . DIRECTORY_SEPARATOR . "vendor" . DIRECTORY_SEPARATOR . "bin" . DIRECTORY_SEPARATOR . "phpcbf";
if (is_file($phpcbf)) {
    $formatCommand = escapeshellarg(PHP_BINARY) . " " . escapeshellarg($phpcbf) . " " . escapeshellarg($path);
    passthru($formatCommand, $format);
    if ($format !== 0 && $format !== 1) {
        fwrite(STDERR, "phpcbf failed for " . $path . "\n");
        exit(2);
    }
}
exit(0);
'
