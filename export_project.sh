#!/bin/bash

# Output file
OUTPUT_FILE="project_export.txt"

# Clear or create the output file
> "$OUTPUT_FILE"

# First, output the directory tree with specific exclusions
echo "Directory Structure:" >> "$OUTPUT_FILE"
echo "" >> "$OUTPUT_FILE"
# Exclude vendor, node_modules, configuration files, and other non-essential items
tree -I 'node_modules|ui|vendor|tests|public|dist|build|*.json|*.lock|*.svg|*.png|*.env|*.git*|*.eslint*|*.config.*|language' . >> "$OUTPUT_FILE"
echo "" >> "$OUTPUT_FILE"
echo "====================" >> "$OUTPUT_FILE"
echo "File Contents:" >> "$OUTPUT_FILE"
echo "====================" >> "$OUTPUT_FILE"
echo "" >> "$OUTPUT_FILE"

# Function to check if a file should be included
should_include() {
    local file="$1"
    
    # Skip certain directories
    [[ "$file" == *"node_modules"* ]] && return 1
    [[ "$file" == *"vendor"* ]] && return 1
    [[ "$file" == *"public"* ]] && return 1
    [[ "$file" == *"tests"* ]] && return 1
    [[ "$file" == *"ui"* ]] && return 1
    
    # Skip configuration and build files
    [[ "$file" == *"config."* ]] && return 1
    [[ "$file" == *".json" ]] && return 1
    [[ "$file" == *".lock" ]] && return 1
    [[ "$file" == *".eslintrc"* ]] && return 1
    [[ "$file" == *".env"* ]] && return 1
    [[ "$file" == *".gitignore"* ]] && return 1
    [[ "$file" == *"vite.config"* ]] && return 1
    [[ "$file" == *"postcss.config"* ]] && return 1
    [[ "$file" == *"tailwind.config"* ]] && return 1
    
    # Skip asset files
    [[ "$file" == *".svg" ]] && return 1
    [[ "$file" == *".png" ]] && return 1
    [[ "$file" == *".jpg" ]] && return 1
    [[ "$file" == *".jpeg" ]] && return 1
    
    # Include only specific file types (add or remove as needed)
    case "$file" in
        *.php|*.tsx|*.ts|*.jsx|*.js|*.css)
            # Exclude specific files even if they match the extension
            [[ "$file" == *"vite-env.d.ts"* ]] && return 1
            [[ "$file" == *"/ui/"* ]] && return 1
            return 0
            ;;
        *)
            return 1
            ;;
    esac
}

# Function to process files
process_files() {
    local base_dir="."
    
    # Find all files recursively from current directory
    while IFS= read -r -d '' file; do
        # Check if file should be included
        if should_include "$file"; then
            echo "File: $file" >> "$OUTPUT_FILE"
            echo "----------------------------------------" >> "$OUTPUT_FILE"
            cat "$file" >> "$OUTPUT_FILE"
            echo "" >> "$OUTPUT_FILE"
            echo "========================================" >> "$OUTPUT_FILE"
            echo "" >> "$OUTPUT_FILE"
        fi
    done < <(find "$base_dir" -type f -print0)
}

# Process the current directory
process_files

echo "Export completed to $OUTPUT_FILE"
