#!/usr/bin/env php
<?php

/**
 * Quick ACL Setup Script
 * Run this script to quickly set up the ACL system
 */

echo "=== ACL Setup Script ===\n";
echo "This script will help you set up the ACL system for Cluster Managers.\n\n";

// Check if running from command line
if (php_sapi_name() !== 'cli') {
    echo "This script must be run from the command line.\n";
    exit(1);
}

// Get database configuration
echo "Please provide your database configuration:\n";
echo "Host (default: localhost): ";
$host = trim(fgets(STDIN)) ?: 'localhost';

echo "Database name: ";
$database = trim(fgets(STDIN));

echo "Username: ";
$username = trim(fgets(STDIN));

echo "Password: ";
$password = trim(fgets(STDIN));

if (empty($database) || empty($username)) {
    echo "Database name and username are required.\n";
    exit(1);
}

try {
    // Connect to database
    $pdo = new PDO("mysql:host=$host;dbname=$database", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "\n✓ Database connection successful!\n\n";
    
    // Read and execute migration SQL
    $migrationFile = __DIR__ . '/database_migration.sql';
    
    if (!file_exists($migrationFile)) {
        echo "❌ Migration file not found: $migrationFile\n";
        exit(1);
    }
    
    echo "📁 Reading migration file...\n";
    $sql = file_get_contents($migrationFile);
    
    // Split SQL into individual statements
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    
    echo "🔄 Executing migration statements...\n";
    
    $successCount = 0;
    $errorCount = 0;
    
    foreach ($statements as $statement) {
        if (empty($statement) || strpos($statement, '--') === 0) {
            continue;
        }
        
        try {
            $pdo->exec($statement);
            $successCount++;
            echo "✓ Executed statement successfully\n";
        } catch (PDOException $e) {
            $errorCount++;
            echo "⚠️  Warning: " . $e->getMessage() . "\n";
        }
    }
    
    echo "\n=== Migration Summary ===\n";
    echo "✅ Successful statements: $successCount\n";
    echo "⚠️  Warnings/Errors: $errorCount\n";
    
    // Test the setup
    echo "\n🧪 Testing setup...\n";
    
    // Check if columns were added
    $result = $pdo->query("SHOW COLUMNS FROM alert_cluster_master LIKE 'region_id'");
    if ($result->rowCount() > 0) {
        echo "✅ Hierarchy columns added successfully\n";
    } else {
        echo "❌ Hierarchy columns not found\n";
    }
    
    // Check data population
    $result = $pdo->query("SELECT COUNT(*) as count FROM alert_cluster_master WHERE region_id IS NOT NULL");
    $row = $result->fetch(PDO::FETCH_ASSOC);
    if ($row['count'] > 0) {
        echo "✅ Data population successful ({$row['count']} clusters with regions)\n";
    } else {
        echo "⚠️  No clusters have been assigned to regions yet\n";
    }
    
    echo "\n🎉 ACL setup completed!\n";
    echo "\nNext steps:\n";
    echo "1. Visit /Admin/DataMigration to run additional migration steps\n";
    echo "2. Visit /Admin/ACLTest to test the ACL system\n";
    echo "3. Login as a cluster manager to test the restrictions\n";
    echo "4. Visit /Masters/User to test the enhanced user management\n\n";
    
} catch (PDOException $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
    exit(1);
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}

echo "Setup completed successfully! 🚀\n";
?>