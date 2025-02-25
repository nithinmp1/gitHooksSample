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

// Get staged files
exec("git diff --cached --name-only", $modifiedFiles);

foreach ($modifiedFiles as $file) {
    $fileName = pathinfo($file, PATHINFO_FILENAME);

    // Extract version number from filename (Format: file-XX.php)
    if (preg_match('/^(.*)-(\d+\.\d+)$/', $fileName, $matches)) {
        $baseName = $matches[1]; // "file"
        $currentVersion = (float) $matches[2]; // Extract version

        if (isset($config[$baseName])) {
            $latestVersion = (float) $config[$baseName];

            // Block commit if modifying an outdated file
            var_dump($currentVersion .'<'. $latestVersion);
            if ($currentVersion < $latestVersion) {
                echo "❌ Error: You are editing an outdated version of '$file'. Latest version is $latestVersion.\n";
                exit(1);
            }
        }
    }
}

exit(0);
