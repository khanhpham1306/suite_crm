<?php
/**
 * Export all SuiteCRM en_us language strings to a single JSON file.
 *
 * Usage (from repo root):
 *   php tools/export_strings.php
 *
 * Output: tools/strings_en_us.json
 *
 * The JSON structure is:
 *   { "<relative-path>": { "<var-name>": { "<key>": "<value>", ... } } }
 *
 * Send strings_en_us.json to a translator, who produces strings_vi_VN.json
 * (same structure, values replaced with Vietnamese). Then run import_strings.php.
 */

$suiteRoot = __DIR__ . '/../SuiteCRM';
$outputFile = __DIR__ . '/strings_en_us.json';

// ── 1. Collect all en_us.lang.php paths ──────────────────────────────────────

$files = [];

// Core module + infrastructure language files
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($suiteRoot));
foreach ($iterator as $file) {
    if ($file->getFilename() === 'en_us.lang.php') {
        $files[] = $file->getRealPath();
    }
}

// CANALI custom Extension language files (not named en_us.lang.php)
$customExtLang = [
    $suiteRoot . '/custom/Extension/application/Ext/Language/en_us.canali_branding.php',
    $suiteRoot . '/custom/Extension/modules/Contacts/Ext/Language/en_us.canali_labels.php',
    $suiteRoot . '/custom/Extension/modules/CANALI_GarmentOrders/Ext/Language/en_us.canali_go_labels.php',
];
foreach ($customExtLang as $path) {
    if (file_exists($path)) {
        $files[] = $path;
    }
}

sort($files);

// ── 2. Extract strings from each file ────────────────────────────────────────

$output = [];

foreach ($files as $absPath) {
    $relPath = ltrim(str_replace(realpath($suiteRoot), '', realpath($absPath)), '/');

    // Reset variables before include
    $mod_strings      = null;
    $app_strings      = null;
    $app_list_strings = null;

    // Stub out the sugarEntry guard so the file doesn't die()
    if (!defined('sugarEntry')) {
        define('sugarEntry', true);
    }

    // Stub SugarThemeRegistry for the core language file which calls getImage()
    if (!class_exists('SugarThemeRegistry')) {
        eval('class SugarThemeRegistry { public static function current() { return new self(); } public function getImage() { return ""; } }');
    }

    try {
        @include $absPath;
    } catch (Throwable $e) {
        fwrite(STDERR, "WARNING: could not include $relPath — {$e->getMessage()}\n");
        continue;
    }

    $vars = [];
    if (is_array($mod_strings))      $vars['mod_strings']      = flattenStrings($mod_strings);
    if (is_array($app_strings))      $vars['app_strings']      = flattenStrings($app_strings);
    if (is_array($app_list_strings)) $vars['app_list_strings'] = flattenStrings($app_list_strings);

    if (!empty($vars)) {
        $output[$relPath] = $vars;
    }
}

// ── 3. Write JSON ─────────────────────────────────────────────────────────────

file_put_contents($outputFile, json_encode($output, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

$fileCount   = count($output);
$stringCount = array_sum(array_map(fn($v) => array_sum(array_map('count', $v)), $output));
echo "Exported $fileCount files, $stringCount strings → $outputFile\n";

// ── Helpers ───────────────────────────────────────────────────────────────────

/**
 * Flatten a potentially nested array to only leaf string values.
 * Nested keys are joined with a pipe: "moduleList|Contacts" => "Clients"
 */
function flattenStrings(array $arr, string $prefix = ''): array
{
    $result = [];
    foreach ($arr as $k => $v) {
        $key = $prefix === '' ? (string)$k : $prefix . '|' . $k;
        if (is_array($v)) {
            $result = array_merge($result, flattenStrings($v, $key));
        } else {
            $result[$key] = (string)$v;
        }
    }
    return $result;
}
