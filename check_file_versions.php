<?php

// Configuration file for latest versions
$configFile = 'config.php';
if (!file_exists($configFile)) {
    echo "❌ Error: Missing configuration file ($configFile).\n";
    exit(1);
}

// Load file versions from config
$config = include $configFile;

// Get the current branch
$currentBranch = trim(shell_exec("git rev-parse --abbrev-ref HEAD"));

// Get all remote branches sorted by creation date
$branches = explode("\n", trim(shell_exec("git for-each-ref --sort=creatordate --format='%(refname:short)' refs/heads/")));

if (empty($branches)) {
    echo "❌ Error: Could not retrieve branches.\n";
    exit(1);
}

// Find branches that were created after the current branch
$foundCurrentBranch = false;
$newerBranches = [];

foreach ($branches as $branch) {
    if ($branch === $currentBranch) {
        $foundCurrentBranch = true;
    } elseif ($foundCurrentBranch) {
        $newerBranches[] = $branch;
    }
}

if (!empty($newerBranches)) {
    echo "⚠️ Warning: There are newer branches than your current branch:\n";
    foreach ($newerBranches as $branch) {
        echo "   - $branch\n";
    }
}

// Get the list of modified files
$modifiedFiles = array_slice($argv, 1);
var_dump($modifiedFiles);
foreach ($modifiedFiles as $file) {
    $fileName = pathinfo($file, PATHINFO_FILENAME);

    // Extract version number from filename (assume format: file-XX)
    if (preg_match('/^(.*)-(\d+\.\d+)\.php$/', $file, $matches)) {
        var_dump($matches);
        $baseName = $matches[1]; // "file"
        $currentVersion = (float) $matches[2]; // 01 -> 1.0

        if (isset($config[$baseName])) {
            $latestVersion = (float) $config[$baseName];
            var_dump($currentVersion .'<'. $latestVersion);
            if ($currentVersion < $latestVersion) {
                echo "❌ Error: You are editing an outdated version of '$file'. Latest version is $latestVersion.\n";
                exit(1);
            }
        }
    }
}

exit(0);
