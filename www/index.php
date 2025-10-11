<?php

use MagicObject\SecretObject;

$autoloadPath = __DIR__ . "/MagicAppBuilder/inc.lib/vendor/autoload.php";

function getDirectories($path) {
    $allDirs = array_filter(glob($path . '/*'), 'is_dir');
    $filteredDirs = array_filter($allDirs, function($dir) {
        $dirName = basename($dir);
        return !in_array($dirName, ['phpMyAdmin', 'MagicAppBuilder', '__assets']);
    });
    return $filteredDirs;
}

function getDirectoryContent($dir) {
    $files = array_diff(scandir($dir), ['.', '..']);
    return count($files) . ' item(s)';
}

function getDescription($applicationConfig) {
    $secretObject = new SecretObject();
    $secretObject->loadYamlFile($applicationConfig, false, true, true);
    return $secretObject->application->description;
}
$magicAppBuilderExists = false;
$phpMyAdminExists = false;
if(file_exists($autoloadPath)) {
    include($autoloadPath);
    $magicAppBuilderExists = true;
}

if(file_exists(__DIR__ . "/phpMyAdmin")) {
    $phpMyAdminExists = true;
}


$basePath = __DIR__;
$directories = getDirectories($basePath);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="__assets/images/favicon.ico" />
    <link rel="shortcut icon" type="image/x-icon" href="__assets/images/favicon.ico" />
    <title>MagicServer Index</title>
    <link rel="stylesheet" href="__assets/css/style.css">
    <script>
    function filterApplications() {
        let input = document.getElementById('filterInput');
        let filter = input.value.toUpperCase();
        let ul = document.getElementById("applicationList");
        let items = ul.getElementsByTagName('a');
        for (let i = 0; i < items.length; i++) {
            let nameDiv = items[i].getElementsByClassName("name")[0];
            let txtValue = nameDiv.textContent || nameDiv.innerText;
            if (txtValue.toUpperCase().indexOf(filter) > -1) {
                items[i].style.display = "";
            } else {
                items[i].style.display = "none";
            }
        }
    }
    </script>
</head>
<body>
    <div class="container">
        <h2>📂 Tools</h2>
        <ul class="list-group">
            <?php if($magicAppBuilderExists) { ?>
            <li class="list-group-item">
                <div class="icon"><img src="__assets/images/magicappbuilder-icon.png"></div>
                <div class="details">
                    <div class="name"><a href="MagicAppBuilder">MagicAppBuilder</a></div>
                    <div class="desc">A low-code platform designed to accelerate application development by minimizing manual coding</div>
                </div>
            </li>
            <?php } ?>
            <?php if($phpMyAdminExists) { ?>
            <li class="list-group-item">
                <div class="icon"><img src="__assets/images/phpmyadmin-icon.png"></div>
                <div class="details">
                    <div class="name"><a href="phpMyAdmin">phpMyAdmin</a></div>
                    <div class="desc">A web-based interface for managing MySQL/MariaDB databases visually</div>
                </div>
            </li>
            <?php } ?>
        </ul>
        <h2>📂 Application List</h2>
        <input type="text" id="filterInput" class="filter-input" onkeyup="filterApplications()" placeholder="Search for application...">
        <ul class="list-group" id="applicationList">
            <?php foreach ($directories as $dir){ 
                if(file_exists($dir . "/inc.cfg/application.yml") && $magicAppBuilderExists)
                {
                    $applicationConfig = $dir . "/inc.cfg/application.yml";
                    $description = getDescription($applicationConfig);
                }
                else
                {
                    $description = getDirectoryContent($dir);
                }
                $basename = basename($dir);
                ?>
                <a href="<?= $basename ?>" class="list-group-item-link">
                    <li class="list-group-item">
                        <div class="icon"><img src="<?= $basename ?>/favicon.ico"></div>
                        <div class="details">
                            <div class="name"><?= $basename ?></div>
                            <div class="desc"><?= htmlspecialchars($description) ?></div>
                        </div>
                    </li>
                </a>
            <?php } ?>
        </ul>
    </div>

    
</body>
</html>