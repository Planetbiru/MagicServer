<?php

/**
 * Ensure a directory exists and has 0777 permissions.
 *
 * @param string $path The path of the directory to ensure.
 */
function ensureDirectory($path)
{
    if (!file_exists($path)) {
        mkdir($path, 0777, true);
    } else {
        chmod($path, 0777);
    }
}

/**
 * Replace the ${INSTALL_DIR} placeholder in a template file and
 * write the result to the given output path.
 *
 * @param string $templatePath The source template file.
 * @param string $outputPath   The destination output file.
 */
function replaceAndWrite($templatePath, $outputPath)
{
    $installDir = str_replace("\\", "/", __DIR__);
    $content = file_get_contents($templatePath);
    $content = str_replace('${INSTALL_DIR}', $installDir, $content);
    file_put_contents($outputPath, $content);
}

/**
 * Adds a directory to the system PATH environment variable if it's not already present.
 *
 * @param string $newPath The directory path to add.
 */
function addPathToEnvironment($newPath)
{
    $newPath = rtrim($newPath, DIRECTORY_SEPARATOR);
    $os = strtoupper(substr(PHP_OS, 0, 3));

    if ($os === 'WIN') {
        // Windows system
        $currentPath = trim(shell_exec('echo %PATH%'));
        $separator = ';';
        $commandPrefix = 'setx PATH ';
    } else {
        // Unix/Linux/macOS system
        $currentPath = getenv('PATH');
        $separator = ':';
    }

    $paths = explode($separator, $currentPath);
    $normalizedPaths = array_map('trim', $paths);

    // Check if new path is already in PATH
    if (in_array($newPath, $normalizedPaths)) {
        echo "Path already exists in PATH.\n";
        return;
    }

    // Append the new path
    $updatedPath = $currentPath . $separator . $newPath;

    if ($os === 'WIN') {
        // Use setx to persist environment variable in Windows
        $command = $commandPrefix . escapeshellarg($updatedPath);
        exec($command, $output, $resultCode);

        if ($resultCode === 0) {
            echo "Path successfully added to PATH.\n";
        } else {
            echo "Failed to update PATH. You may need to run this script as administrator.\n";
        }
    } else {
        // For Unix/Linux, suggest user to manually update shell profile
        echo "To update your PATH, add the following to your shell profile:\n";
        echo 'export PATH="$PATH:' . $newPath . '"' . "\n";
    }
}

/**
 * Attempts to stop all running processes by their executable name (Windows only).
 *
 * @param string $name The process executable name (e.g., "notepad.exe").
 */
function stopProcessByName($name)
{
    echo "Stopping $name...\n";
    $output = [];
    exec("taskkill /F /IM $name", $output);
    exec("tasklist /FI \"IMAGENAME eq $name\" 2>NUL", $output);

    if (count($output) <= 1) {
        echo "  [INFO] $name is not running.\n";
        return;
    }

    // Kill all processes with this name
    exec("taskkill /F /IM $name", $result, $exitCode);

    if ($exitCode === 0) {
        echo "  [OK] $name stopped successfully.\n";
    } else {
        echo "  [ERROR] Failed to stop $name.\n";
    }
}

/**
 * Deletes a folder and all its contents.
 *
 * @param string $path The directory to delete.
 */
function deleteFolder($path)
{
    if (!file_exists($path)) {
        return;
    }

    $items = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST
    );

    foreach ($items as $item) {
        $item->isDir()
            ? rmdir($item->getRealPath())
            : unlink($item->getRealPath());
    }

    rmdir($path);
}

/**
 * Fetch JSON data from a given URL (with SSL verification disabled).
 *
 * @param string $url The URL to fetch.
 * @return array|null The parsed JSON response, or null on failure.
 */
function fetchJson($url)
{
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_USERAGENT => 'MagicServerInstaller/1.0',
        CURLOPT_HTTPHEADER => ['Accept: application/vnd.github.v3+json'],
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_TIMEOUT => 20,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false
    ]);

    $result = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err = curl_error($ch);
    curl_close($ch);

    if ($httpCode !== 200 || !$result) {
        echo "❌ Failed to fetch JSON. HTTP Code: $httpCode\n";
        if ($err) {
            echo "cURL Error: $err\n";
        }
        return null;
    }

    return json_decode($result, true);
}

/**
 * Fetch a binary stream from the given URL.
 *
 * @param string $url The URL to fetch.
 * @return string|false The downloaded data, or false on failure.
 */
function fetchStream($url)
{
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_USERAGENT => 'MagicServerInstaller/1.0',
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false
    ]);

    $data = curl_exec($ch);
    curl_close($ch);
    return $data;
}
