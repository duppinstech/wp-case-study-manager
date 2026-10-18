#!/bin/sh
# PreToolUse syntax check for full-file PHP writes.
# Exit 0 allows. Exit 2 blocks. Edits have no full file body; post-edit-format.sh lints those.
# Missing php is reported and allowed so a machine without PHP is not stuck.

if ! command -v php >/dev/null 2>&1; then
  echo "php is not on PATH. PHP syntax was not checked." >&2
  exit 0
fi

exec php -r '
$raw = stream_get_contents(STDIN);
$data = json_decode($raw, true);
if (!is_array($data) || !isset($data["tool_input"]) || !is_array($data["tool_input"])) {
    exit(0);
}
$input = $data["tool_input"];
$path = isset($input["file_path"]) ? (string) $input["file_path"] : "";
if ($path === "" || substr($path, -4) !== ".php") {
    exit(0);
}
if (!isset($input["content"]) || !is_string($input["content"])) {
    exit(0);
}
$tmp = tempnam(sys_get_temp_dir(), "csm");
if ($tmp === false) {
    fwrite(STDERR, "Could not create a temp file for php -l.\n");
    exit(2);
}
$target = $tmp . ".php";
file_put_contents($target, $input["content"]);
passthru("php -l " . escapeshellarg($target), $code);
@unlink($target);
@unlink($tmp);
if ($code !== 0) {
    fwrite(STDERR, "PHP syntax check failed for " . $path . "\n");
    exit(2);
}
exit(0);
'
