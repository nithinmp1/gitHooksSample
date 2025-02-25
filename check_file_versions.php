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

// Get all branches sorted by creation date
$branches = explode("\n", trim(shell_exec("git for-each-ref --sort=creatordate --format='%(refname:short)' refs/heads/")));

if (empty($branches)) {
    echo "❌ Error: Could not retrieve branches.\n";
    exit(1);
}

// Find branches created after the current branch
$newerBranches = [];
$foundCurrentBranch = false;

foreach ($branches as $branch) {
    if ($branch === $currentBranch) {
        $foundCurrentBranch = true;
    } elseif ($foundCurrentBranch) {
        $newerBranches[] = $branch;
    }
}

// Show a warning if newer branches exist
if (!empty($newerBranches)) {
    echo "⚠️ Warning: There are newer branches than your current branch:\n";
    foreach ($newerBranches as $branch) {
        echo "   - $branch\n";
    }
}

// Function to find the latest file version across all branches
function getLatestVersionAcrossBranches($fileBaseName) {
    global $branches;
    $latestVersion = 0.0;

    foreach ($branches as $branch) {
        // Get the list of files in each branch
        $files = explode("\n", trim(shell_exec("git ls-tree -r --name-only $branch")));
        
        foreach ($files as $file) {
            if (preg_match('/^(.*)-(\d+\.\d+)\.php$/', $file, $matches)) {
                $baseName = $matches[1]; 
                $version = (float) $matches[2];

                if ($baseName === $fileBaseName && $version > $latestVersion) {
                    $latestVersion = $version;
                }
            }
        }
    }

    return $latestVersion;
}

// Get staged files
exec("git diff --cached --name-only", $modifiedFiles);

foreach ($modifiedFiles as $file) {
    $fileName = pathinfo($file, PATHINFO_FILENAME);

    // Extract version number from filename (Format: file-XX.php)
    if (preg_match('/^(.*)-(\d+\.\d+)$/', $fileName, $matches)) {
        $baseName = $matches[1]; // "file"
        $currentVersion = (float) $matches[2]; // Extract version

        // Get latest version across all branches
        $latestVersion = getLatestVersionAcrossBranches($baseName);

        // Block commit if modifying an outdated file
        var_dump($currentVersion .'<'. $latestVersion);
        if ($currentVersion < $latestVersion) {
            echo "❌ Error: You are editing an outdated version of '$file'. Latest version is $latestVersion.\n";
            exit(1);
        }
    }
}

exit(0);
