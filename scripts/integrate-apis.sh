#!/bin/bash

# Script to integrate user-data.js into all HTML pages
# This adds the script tag before </body> in all pages

# Base directory
BASE_DIR="c:/Users/brucy/OneDrive/Bureau/jakob-development"

# Pages to modify
PAGES=(
    "pages/user/wallet.html"
    "pages/user/notifications.html"
    "pages/main/home.html"
    "pages/main/explore.html"
    "pages/main/campaign-details.html"
    "pages/main/creator-profile.html"
    "pages/main/donation.html"
    "pages/main/payment-success.html"
    "pages/creator/my-campaigns.html"
    "pages/creator/campaign-editor.html"
    "pages/creator/analytics.html"
)

# Script tag to add
SCRIPT_TAG='    <!-- User Data Loader -->\n    <script src="/assets/js/user-data.js"></script>'

echo "🔧 Integrating APIs into pages..."

for page in "${PAGES[@]}"; do
    filepath="$BASE_DIR/$page"

    if [ -f "$filepath" ]; then
        # Check if script is already added
        if grep -q "user-data.js" "$filepath"; then
            echo "⏭️  Skipping $page (already has script)"
        else
            # Add script before </body>
            sed -i 's|</body>|'"$SCRIPT_TAG"'\n</body>|' "$filepath"
            echo "✅ Modified $page"
        fi
    else
        echo "⚠️  File not found: $page"
    fi
done

echo "✨ Done! All pages have been integrated."
