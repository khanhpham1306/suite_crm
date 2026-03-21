"""
Translate all untranslated strings in strings_vi_VN.json using Google Translate.

Usage:
    pip install deep-translator
    python tools/translate_strings.py

- Reads tools/strings_en_us.json (source)
- Reads tools/strings_vi_VN.json (existing translations — keeps already-translated strings)
- Translates only strings that are still identical to English
- Saves progress after every file so you can resume if interrupted
- Writes result back to tools/strings_vi_VN.json
"""

import json
import time
import sys
from pathlib import Path
from deep_translator import GoogleTranslator

TOOLS_DIR   = Path(__file__).parent
EN_FILE     = TOOLS_DIR / "strings_en_us.json"
VI_FILE     = TOOLS_DIR / "strings_vi_VN.json"
BATCH_SIZE    = 50    # strings per API call
MAX_CHARS     = 4000  # max total chars per batch (Google limit is ~5000)
SLEEP_SEC     = 0.3   # pause between batches to avoid rate-limiting

def load_json(path):
    with open(path, encoding="utf-8") as f:
        return json.load(f)

def save_json(path, data):
    with open(path, "w", encoding="utf-8") as f:
        json.dump(data, f, ensure_ascii=False, indent=2)

def translate_batch(strings: list[str]) -> list[str]:
    """Translate a list of strings from English to Vietnamese in one API call."""
    # Join with a unique separator that won't appear in the text
    SEP = " ||| "
    joined = SEP.join(strings)
    translated = GoogleTranslator(source="en", target="vi").translate(joined)
    parts = translated.split(SEP)
    # If the separator got mangled, fall back to translating one by one
    if len(parts) != len(strings):
        parts = []
        for s in strings:
            try:
                parts.append(GoogleTranslator(source="en", target="vi").translate(s))
            except Exception:
                parts.append(s)  # keep original on error
    return parts

def collect_untranslated(en_data, vi_data):
    """Return a flat list of (file, var_name, key) tuples that still need translation."""
    pending = []
    for file_path, vars_ in en_data.items():
        for var_name, strings in vars_.items():
            if not isinstance(strings, dict):
                continue  # skip empty or list-type entries
            for key, en_val in strings.items():
                if not isinstance(en_val, str) or not en_val.strip():
                    continue  # skip non-string or blank values
                vi_val = vi_data.get(file_path, {}).get(var_name, {}).get(key, "")
                if vi_val == en_val or vi_val == "":
                    pending.append((file_path, var_name, key, en_val))
    return pending

def main():
    en_data = load_json(EN_FILE)
    vi_data = load_json(VI_FILE) if VI_FILE.exists() else json.loads(json.dumps(en_data))

    pending = collect_untranslated(en_data, vi_data)
    total   = len(pending)

    if total == 0:
        print("Nothing to translate — all strings are already translated.")
        return

    print(f"Strings to translate: {total}")
    print(f"Batch size: {BATCH_SIZE} | Sleep between batches: {SLEEP_SEC}s")
    print()

    translated_count = 0
    errors           = 0

    # Build batches respecting both BATCH_SIZE and MAX_CHARS
    batches = []
    current_batch = []
    current_chars = 0
    for item in pending:
        s = item[3]
        if current_batch and (len(current_batch) >= BATCH_SIZE or current_chars + len(s) > MAX_CHARS):
            batches.append(current_batch)
            current_batch = []
            current_chars = 0
        current_batch.append(item)
        current_chars += len(s)
    if current_batch:
        batches.append(current_batch)

    for batch in batches:
        en_values = [item[3] for item in batch]

        try:
            vi_values = translate_batch(en_values)
        except Exception as e:
            print(f"  ERROR on batch (size {len(batch)}): {e}", file=sys.stderr)
            vi_values = en_values  # keep English on failure
            errors += 1

        # Write results back into vi_data
        for (file_path, var_name, key, _), vi_val in zip(batch, vi_values):
            vi_data.setdefault(file_path, {}).setdefault(var_name, {})[key] = vi_val

        translated_count += len(batch)
        pct = translated_count / total * 100
        print(f"  [{translated_count}/{total}] {pct:.1f}%  — {en_values[0][:50]!r}")

        # Save progress after every batch
        save_json(VI_FILE, vi_data)

        time.sleep(SLEEP_SEC)

    print()
    print(f"Done. {translated_count} strings processed, {errors} batch errors.")
    print(f"Output: {VI_FILE}")

if __name__ == "__main__":
    main()
