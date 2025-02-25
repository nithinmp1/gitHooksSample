<?php

// Configuration file name
$configFileName = 'config.php';

// Get the current branch
$currentBranch = trim(shell_exec("git rev-parse --abbrev-ref HEAD"));

if ($currentBranch === 'master') {
    exit(0); // Allow commit without checking
}
// Get all branches sorted by creation date
$branches = explode("\n", trim(shell_exec("git for-each-ref --sort=creatordate --format='%(refname:short)' refs/heads/")));

if (empty($branches)) {
    echo "❌ Error: Could not retrieve branches.\n";
    exit(1);
}

// Find branches created after the current branch
$newerBranches = [];
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
// Show a warning if newer branches exist
if (!empty($newerBranches)) {
    echo "⚠️ Warning: There are newer branches than your current branch:\n";
    foreach ($newerBranches as $branch) {
        echo "   - $branch\n";
    }
}

/**
 * Reads the latest file versions from config.php in a given branch.
 */
function getLatestVersionsFromBranch($branch) {
    global $configFileName;
    $configContent = trim(shell_exec("git show $branch:$configFileName 2>/dev/null"));

    if (!$configContent) {
        return [];
    }

    // Convert PHP config file content into an array
    $versions = eval(substr($configContent, strpos($configContent, 'return '))); 

    return is_array($versions) ? $versions : [];
}

/**
 * Gets the highest version of a file across all branches.
 */
function getHighestVersionAcrossBranches($fileBaseName) {
    global $branches;
    $highestVersion = 0.0;
    foreach ($branches as $branch) {
        $versions = getLatestVersionsFromBranch($branch);
        
        if (isset($versions[$fileBaseName])) {
            $branchVersion = (float) $versions[$fileBaseName];
            if ($branchVersion > $highestVersion) {
                $highestVersion = $branchVersion;
            }
        }
    }

    return $highestVersion;
}

// Get staged files
exec("git diff --cached --name-only", $modifiedFiles);

foreach ($modifiedFiles as $file) {
    $fileName = pathinfo($file, PATHINFO_FILENAME);

    // Extract version number from filename (Format: file-XX.php)
    if (preg_match('/^(.*)-(\d+\.\d+)$/', $fileName, $matches)) {
    // Extract version number from filename (Format: file-XX.php)
        if (preg_match('/^(.*)-(\d+\.\d+)$/', $fileName, $matches)) {
            $baseName = $matches[1]; // "file"
            $currentVersion = (float) $matches[2]; // Extract version

            // Get the highest version across all branches from config.php
            $latestVersion = getHighestVersionAcrossBranches($baseName);

            // Block commit if modifying an outdated file
            if ($currentVersion < $latestVersion) {
                echo "❌ Error: You are editing an outdated version of '$file'. Latest version is $latestVersion.\n";
                exit(1);
            }
        }
    }
}

exit(0);