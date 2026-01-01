#!/usr/bin/env python3
"""Add favicon to all HTML pages in the project"""

import os
import re
from pathlib import Path

# Favicon line to add
FAVICON_LINE = '    <link rel="icon" type="image/svg+xml" href="/assets/images/favicon.svg">\n'

def add_favicon_to_file(file_path):
    """Add favicon link to an HTML file if not already present"""
    with open(file_path, 'r', encoding='utf-8') as f:
        content = f.read()

    # Check if favicon is already present
    if 'favicon.svg' in content or 'rel="icon"' in content:
        print(f"[OK] Skipped {file_path} (favicon already present)")
        return False

    # Find the title tag and add favicon after it
    # Pattern: match </title> and add favicon on the next line
    pattern = r'(<title>.*?</title>)\n'
    match = re.search(pattern, content, re.IGNORECASE | re.DOTALL)

    if match:
        # Insert favicon after </title>
        new_content = content[:match.end()] + FAVICON_LINE + content[match.end():]

        with open(file_path, 'w', encoding='utf-8') as f:
            f.write(new_content)

        print(f"[+] Added favicon to {file_path}")
        return True
    else:
        print(f"[!] Could not find <title> tag in {file_path}")
        return False

def main():
    """Main function to process all HTML files"""
    root_dir = Path(__file__).parent

    # Find all HTML files
    html_files = list(root_dir.rglob('*.html'))

    print(f"Found {len(html_files)} HTML files\n")

    added_count = 0
    skipped_count = 0
    failed_count = 0

    for html_file in html_files:
        try:
            result = add_favicon_to_file(html_file)
            if result:
                added_count += 1
            elif result is False and 'favicon' in open(html_file, 'r', encoding='utf-8').read():
                skipped_count += 1
        except Exception as e:
            print(f"[!] Error processing {html_file}: {e}")
            failed_count += 1

    print(f"\n{'='*50}")
    print(f"Summary:")
    print(f"  Added favicon: {added_count} files")
    print(f"  Already had favicon: {skipped_count} files")
    print(f"  Failed: {failed_count} files")
    print(f"  Total processed: {len(html_files)} files")
    print(f"{'='*50}")

if __name__ == '__main__':
    main()
