<?php
/**
 * Import translated language strings and generate vi_VN PHP language files.
 *
 * Usage (from repo root):
 *   php tools/import_strings.php tools/strings_vi_VN.json
 *
 * The input JSON must have the same structure produced by export_strings.php,
 * with values replaced by Vietnamese translations.
 *
 * Output: vi_VN.lang.php files alongside each source en_us.lang.php, and
 * vi_VN.* extension files for CANALI custom language files.
 */

if ($argc < 2) {
    fwrite(STDERR, "Usage: php tools/import_strings.php <path-to-translated-json>\n");
    exit(1);
}

$inputFile = $argv[1];
if (!file_exists($inputFile)) {
    fwrite(STDERR, "Error: file not found: $inputFile\n");
    exit(1);
}

$suiteRoot = realpath(__DIR__ . '/../SuiteCRM');
$data = json_decode(file_get_contents($inputFile), true);

if (!is_array($data)) {
    fwrite(STDERR, "Error: invalid JSON in $inputFile\n");
    exit(1);
}

$written = 0;

foreach ($data as $relPath => $vars) {
    // Determine the output path (replace en_us with vi_VN in the filename)
    $outRelPath = preg_replace('/\ben_us\b/', 'vi_VN', $relPath);
    $outAbsPath = $suiteRoot . '/' . $outRelPath;

    // Rebuild the nested PHP arrays from flat pipe-keyed strings
    $rebuilt = [];
    foreach ($vars as $varName => $flat) {
        $rebuilt[$varName] = unflattenStrings($flat);
    }

    $php = generatePhpFile($rebuilt);

    $dir = dirname($outAbsPath);
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }

    file_put_contents($outAbsPath, $php);
    echo "Wrote: $outRelPath\n";
    $written++;
}

echo "\nDone. $written files written.\n";
echo "\nNext steps:\n";
echo "  1. Add to SuiteCRM/config_override.php:\n";
echo "       \$sugar_config['languages']['vi_VN'] = 'Tiếng Việt';\n";
echo "  2. git add, commit, push\n";
echo "  3. On server: git pull && docker compose restart app\n";
echo "  4. Admin → Repair → Quick Repair & Rebuild\n";
echo "  5. Log in → MySettings → Language → Tiếng Việt\n";

// ── Helpers ───────────────────────────────────────────────────────────────────

/**
 * Unflatten pipe-joined keys back into nested arrays.
 */
function unflattenStrings(array $flat): array
{
    $result = [];
    foreach ($flat as $key => $value) {
        $parts = explode('|', $key);
        $ref = &$result;
        foreach ($parts as $part) {
            if (!isset($ref[$part]) || !is_array($ref[$part])) {
                $ref[$part] = [];
            }
            $ref = &$ref[$part];
        }
        $ref = $value;
    }
    return $result;
}

/**
 * Generate a valid PHP language file from an array of variable name => value.
 */
function generatePhpFile(array $vars): string
{
    $lines = [];
    $lines[] = '<?php';
    $lines[] = 'if (!defined(\'sugarEntry\') || !sugarEntry) die(\'Not A Valid Entry Point\');';
    $lines[] = '';

    foreach ($vars as $varName => $value) {
        $lines[] = '$' . $varName . ' = ' . varExport($value) . ';';
        $lines[] = '';
    }

    return implode("\n", $lines);
}

/**
 * var_export replacement that produces compact, readable PHP arrays.
 */
function varExport(mixed $value, int $depth = 0): string
{
    $indent  = str_repeat('    ', $depth);
    $indent1 = str_repeat('    ', $depth + 1);

    if (is_array($value)) {
        if (empty($value)) return 'array()';
        $lines = [];
        foreach ($value as $k => $v) {
            $key = is_int($k) ? $k : "'" . addcslashes((string)$k, "'\\") . "'";
            $lines[] = $indent1 . $key . ' => ' . varExport($v, $depth + 1);
        }
        return "array(\n" . implode(",\n", $lines) . ",\n" . $indent . ')';
    }

    if (is_string($value)) {
        return "'" . addcslashes($value, "'\\") . "'";
    }

    return var_export($value, true);
}
