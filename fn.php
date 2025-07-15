<?php

/**
 * Ensure a directory exists and has 0777 permissions.
 *
 * @param string $path
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
 * Replace placeholder in a template file and write to destination.
 *
 * @param string $templatePath
 * @param string $outputPath
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
        // Unix/Linux/macOS
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
        // For Unix/Linux, suggest user to manually update ~/.bashrc or ~/.zshrc
        echo "To update your PATH, add the following to your shell profile:\n";
        echo 'export PATH="$PATH:' . $newPath . '"' . "\n";
    }
}

/**
 * Attempts to stop all running processes by their executable name (Windows only).
 *
 * @param string $name The process executable name (e.g., "notepad.exe").
 */
function stopProcessByName($name) {
    echo "Stopping $name...\n";
    $output = [];
    exec("taskkill /im $name /F", $output);
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
