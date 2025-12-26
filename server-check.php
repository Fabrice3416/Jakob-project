<?php
/**
 * Server Configuration Check
 * Verify server permissions and configuration
 */

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Server Check - JaKòb</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #211111 0%, #2f1a1b 100%);
            color: #fff;
            padding: 2rem;
        }
        .container {
            max-width: 900px;
            margin: 0 auto;
            background: rgba(47, 26, 27, 0.8);
            border-radius: 1rem;
            padding: 2rem;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
        }
        h1 { color: #ea2a33; margin-bottom: 1.5rem; }
        .section {
            background: rgba(255, 255, 255, 0.05);
            border-left: 4px solid #ea2a33;
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: 0.5rem;
        }
        .success { border-left-color: #4ade80; color: #4ade80; }
        .error { border-left-color: #f87171; color: #f87171; }
        .warning { border-left-color: #fbbf24; color: #fbbf24; }
        table { width: 100%; border-collapse: collapse; margin-top: 1rem; }
        td { padding: 0.5rem; border-bottom: 1px solid rgba(255,255,255,0.1); }
        td:first-child { font-weight: bold; width: 40%; }
        .badge { padding: 0.25rem 0.5rem; border-radius: 0.25rem; font-size: 0.875rem; }
        .badge-success { background: #4ade80; color: #000; }
        .badge-error { background: #f87171; color: #fff; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 Server Configuration Check</h1>

        <!-- Server Info -->
        <div class="section">
            <h2>📊 Server Information</h2>
            <table>
                <tr>
                    <td>Server Software</td>
                    <td><?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown'; ?></td>
                </tr>
                <tr>
                    <td>PHP Version</td>
                    <td><?php echo PHP_VERSION; ?>
                        <?php if (version_compare(PHP_VERSION, '7.4.0') >= 0): ?>
                            <span class="badge badge-success">✓ Compatible</span>
                        <?php else: ?>
                            <span class="badge badge-error">✗ Upgrade required</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <td>Document Root</td>
                    <td><?php echo $_SERVER['DOCUMENT_ROOT'] ?? 'Unknown'; ?></td>
                </tr>
                <tr>
                    <td>Server Name</td>
                    <td><?php echo $_SERVER['SERVER_NAME'] ?? 'Unknown'; ?></td>
                </tr>
                <tr>
                    <td>Client IP</td>
                    <td><?php echo $_SERVER['REMOTE_ADDR'] ?? 'Unknown'; ?></td>
                </tr>
                <tr>
                    <td>Request Method</td>
                    <td><?php echo $_SERVER['REQUEST_METHOD'] ?? 'Unknown'; ?></td>
                </tr>
            </table>
        </div>

        <!-- PHP Extensions -->
        <div class="section <?php echo (extension_loaded('pdo') && extension_loaded('pdo_mysql')) ? 'success' : 'error'; ?>">
            <h2>🔌 Required PHP Extensions</h2>
            <table>
                <tr>
                    <td>PDO</td>
                    <td>
                        <?php if (extension_loaded('pdo')): ?>
                            <span class="badge badge-success">✓ Loaded</span>
                        <?php else: ?>
                            <span class="badge badge-error">✗ Missing</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <td>PDO MySQL</td>
                    <td>
                        <?php if (extension_loaded('pdo_mysql')): ?>
                            <span class="badge badge-success">✓ Loaded</span>
                        <?php else: ?>
                            <span class="badge badge-error">✗ Missing</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <td>JSON</td>
                    <td>
                        <?php if (extension_loaded('json')): ?>
                            <span class="badge badge-success">✓ Loaded</span>
                        <?php else: ?>
                            <span class="badge badge-error">✗ Missing</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <td>cURL</td>
                    <td>
                        <?php if (extension_loaded('curl')): ?>
                            <span class="badge badge-success">✓ Loaded</span>
                        <?php else: ?>
                            <span class="badge badge-error">✗ Missing</span>
                        <?php endif; ?>
                    </td>
                </tr>
            </table>
        </div>

        <!-- File Permissions -->
        <div class="section">
            <h2>📁 File Permissions</h2>
            <table>
                <?php
                $paths = [
                    'config/database.php' => 'readable',
                    '.env' => 'readable',
                    'api/' => 'writable',
                    'uploads/' => 'writable'
                ];

                foreach ($paths as $path => $perm) {
                    $fullPath = __DIR__ . '/' . $path;
                    $exists = file_exists($fullPath);
                    $readable = is_readable($fullPath);
                    $writable = is_writable($fullPath);

                    echo "<tr><td>{$path}</td><td>";

                    if (!$exists) {
                        echo '<span class="badge badge-error">✗ Not Found</span>';
                    } elseif ($perm === 'readable' && $readable) {
                        echo '<span class="badge badge-success">✓ Readable</span>';
                    } elseif ($perm === 'writable' && $writable) {
                        echo '<span class="badge badge-success">✓ Writable</span>';
                    } else {
                        echo '<span class="badge badge-error">✗ Permission Denied</span>';
                    }

                    echo "</td></tr>";
                }
                ?>
            </table>
        </div>

        <!-- Access Test -->
        <div class="section success">
            <h2>✅ Access Test</h2>
            <p><strong>SUCCESS!</strong> If you can see this page, external access is working.</p>
            <p style="margin-top: 0.5rem;">Remote IP: <strong><?php echo $_SERVER['REMOTE_ADDR']; ?></strong></p>
            <p style="margin-top: 0.5rem;">User Agent: <strong><?php echo $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'; ?></strong></p>
        </div>

        <!-- Recommendations -->
        <div class="section warning">
            <h2>⚠️ Security Recommendations</h2>
            <ul style="margin-left: 1.5rem; margin-top: 0.5rem; line-height: 1.8;">
                <li>Delete this file (server-check.php) after verification</li>
                <li>Ensure .env file is not publicly accessible</li>
                <li>Configure SSL certificate for HTTPS</li>
                <li>Set up proper firewall rules</li>
                <li>Enable PHP error logging (not display)</li>
            </ul>
        </div>

        <div style="margin-top: 2rem; padding-top: 1rem; border-top: 1px solid rgba(255,255,255,0.1); text-align: center; opacity: 0.6;">
            <p>JaKòb Platform • Server Configuration Check</p>
        </div>
    </div>
</body>
</html>
