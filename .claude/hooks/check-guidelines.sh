#!/bin/bash
# PostToolUse hook — runs after Write and Edit tool calls.
# Reads the tool input from stdin (JSON), checks the file extension,
# and outputs a guidelines reminder for PHP and Vue/TS files.

INPUT=$(cat)

# Extract file_path from the JSON input
FILE_PATH=$(python3 -c "
import json, sys
try:
    d = json.load(sys.stdin)
    print(d.get('file_path', d.get('path', '')) or '')
except:
    print('')
" <<< "$INPUT" 2>/dev/null)

if [[ -z "$FILE_PATH" ]]; then
    exit 0
fi

# PHP files — check architecture and helpers guidelines
if [[ "$FILE_PATH" == *.php ]]; then
    echo "---"
    echo "Guidelines reminder for PHP file: $FILE_PATH"
    echo ""
    echo "Please verify this code follows the standards in .ai/guidelines/:"
    echo ""
    echo "  architecture-patterns.md:"
    echo "    - Business logic → app/Actions/{Domain}/ (not in controllers)"
    echo "    - Validation → dedicated FormRequest classes (never inline)"
    echo "    - Controllers stay thin — delegate to Actions/Services"
    echo "    - Always use named routes"
    echo "    - UUIDs for primary/foreign keys, no enum column types"
    echo ""
    echo "  laravel-helpers-first.md:"
    echo "    - collect() over array_map/array_filter"
    echo "    - Arr::get() / data_get() over nested array access"
    echo "    - Str::slug/contains/startsWith over native string functions"
    echo "    - optional() / blank() / filled() over manual null checks"
    echo ""
    echo "  spatie-packages.md:"
    echo "    - DTOs extend Spatie\LaravelData\Data"
    echo "    - File uploads use Spatie Medialibrary"
    echo "    - State machines use Spatie ModelStates"
    echo "---"
    exit 0
fi

# Vue / TypeScript files — check frontend conventions
if [[ "$FILE_PATH" == *.vue ]] || [[ "$FILE_PATH" == *.ts ]] || [[ "$FILE_PATH" == *.tsx ]]; then
    echo "---"
    echo "Guidelines reminder for frontend file: $FILE_PATH"
    echo ""
    echo "Please verify this code follows the standards in .ai/guidelines/:"
    echo ""
    echo "  architecture-patterns.md (frontend):"
    echo "    - Use Ziggy route() helper, never hardcode paths"
    echo "    - TypeScript types from Spatie Data: php artisan typescript:transform"
    echo ""
    echo "  General Vue/Inertia conventions:"
    echo "    - App name should be dynamic via page.props.appName (not hardcoded)"
    echo "    - Use <script setup lang=\"ts\"> for new components"
    echo "    - Import computed from vue when using reactive derived values"
    echo "---"
    exit 0
fi

exit 0
