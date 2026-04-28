<?php
// Database Setup Script for FAST University Management System
// Run this file in your browser to set up the database

require_once 'includes/config.php';
require_once 'includes/db.php';

echo "<h1>FAST University Management System - Database Setup</h1>";

try {
    // Choose the schema SQL file
    $schemaFile = file_exists('database_schema.sql') ? 'database_schema.sql' : 'database.sql';
    $sql = file_get_contents($schemaFile);

    echo "<p>Using schema file: <strong>$schemaFile</strong></p>";

    // Split into individual statements
    $statements = array_filter(array_map('trim', explode(';', $sql)));

    $successCount = 0;
    $errorCount = 0;

    echo "<h2>Executing Database Setup...</h2>";
    echo "<pre>";

    foreach ($statements as $statement) {
        if (!empty($statement) && !preg_match('/^--/', $statement)) {
            try {
                $pdo->exec($statement);
                echo "✓ Executed: " . substr($statement, 0, 60) . "...\n";
                $successCount++;
            } catch (PDOException $e) {
                echo "✗ Error: " . $e->getMessage() . "\n";
                echo "   Statement: " . substr($statement, 0, 100) . "...\n";
                $errorCount++;
            }
        }
    }

    echo "</pre>";
    echo "<h2>Setup Complete</h2>";
    echo "<p>Successful statements: $successCount</p>";
    echo "<p>Errors: $errorCount</p>";

    if ($errorCount == 0) {
        echo "<p style='color: green; font-weight: bold;'>✓ Database setup completed successfully!</p>";
        echo "<p><a href='auth/login.php'>Click here to login</a></p>";
        echo "<p>Default admin credentials:</p>";
        echo "<ul>";
        echo "<li>Username: admin</li>";
        echo "<li>Password: password123</li>";
        echo "</ul>";
    } else {
        echo "<p style='color: red; font-weight: bold;'>⚠ Some errors occurred. Please check the output above.</p>";
    }

} catch (Exception $e) {
    echo "<p style='color: red; font-weight: bold;'>Fatal error: " . $e->getMessage() . "</p>";
}
?>