# PowerShell script to add user-data.js to all HTML pages
# Adds the script tag before </body> if not already present

$baseDir = "C:\Users\brucy\OneDrive\Bureau\jakob-development\pages"
$scriptTag = "    <!-- User Data Loader -->`n    <script src=`"/assets/js/user-data.js`"></script>"

# Get all HTML files except auth pages (splash, login, signup)
$files = Get-ChildItem -Path $baseDir -Filter *.html -Recurse | Where-Object {
    $_.DirectoryName -notlike "*\auth"
}

Write-Host "Adding user-data.js script to pages...`n"

foreach ($file in $files) {
    $content = Get-Content $file.FullName -Raw

    # Check if script is already added
    if ($content -match "user-data\.js") {
        Write-Host "Skipping: $($file.Name) (already has script)"
        continue
    }

    # Add script before </body>
    $newContent = $content -replace '</body>', "$scriptTag`n</body>"

    # Save the file
    Set-Content -Path $file.FullName -Value $newContent -NoNewline

    Write-Host "Modified: $($file.Name)"
}

Write-Host "`nDone! Script added to all pages."
Write-Host "Pages in /auth were skipped (they don't need the script)"
