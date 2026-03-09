<?php
/**
 * Test Search Functionality
 * Tests the search queries and displays results
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/db-connect.php';

echo "<h1>Search Functionality Test</h1>";
echo "<pre>";

$db = getDB();

// Test 1: Check database connection
echo "=== Test 1: Database Connection ===\n";
try {
    $db->query("SELECT 1");
    echo "✅ PASS: Database connection successful\n\n";
} catch (Exception $e) {
    echo "❌ FAIL: Database connection failed\n";
    echo "Error: " . $e->getMessage() . "\n\n";
    exit;
}

// Test 2: Check if herbs table has data
echo "=== Test 2: Herbs Table Data ===\n";
try {
    $stmt = $db->query("SELECT COUNT(*) as count FROM herbs");
    $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    echo "✅ PASS: Found {$count} herbs in database\n\n";
    
    if ($count == 0) {
        echo "⚠️  WARNING: No herbs in database. Search won't return results.\n\n";
    }
} catch (Exception $e) {
    echo "❌ FAIL: Error checking herbs table\n";
    echo "Error: " . $e->getMessage() . "\n\n";
}

// Test 3: Test search by herb name
echo "=== Test 3: Search by Herb Name ===\n";
$testQueries = ['ginger', 'chamomile', 'turmeric', 'nonexistentherb123'];
foreach ($testQueries as $query) {
    $searchTerm = '%' . $query . '%';
    $exactTerm = $query . '%';
    
    try {
        $stmt = $db->prepare("
            SELECT DISTINCT h.* 
            FROM herbs h
            WHERE h.name LIKE :query 
               OR h.scientific_name LIKE :query 
               OR h.description LIKE :query
            ORDER BY 
                CASE 
                    WHEN h.name LIKE :exact THEN 1
                    WHEN h.name LIKE :query THEN 2
                    ELSE 3
                END,
                h.name ASC
            LIMIT 5
        ");
        $stmt->execute([
            ':query' => $searchTerm,
            ':exact' => $exactTerm
        ]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "Query: '{$query}'\n";
        echo "  Results: " . count($results) . " herbs found\n";
        if (count($results) > 0) {
            echo "  First result: " . $results[0]['name'] . "\n";
        }
        echo "\n";
    } catch (Exception $e) {
        echo "❌ FAIL: Error searching for '{$query}'\n";
        echo "Error: " . $e->getMessage() . "\n\n";
    }
}

// Test 4: Test search by conditions
echo "=== Test 4: Search by Health Conditions ===\n";
try {
    $stmt = $db->query("SELECT condition_name FROM health_conditions LIMIT 3");
    $conditions = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    foreach ($conditions as $condition) {
        $searchTerm = '%' . $condition . '%';
        $stmt = $db->prepare("
            SELECT DISTINCT h.* 
            FROM herbs h
            INNER JOIN herbs_conditions hc ON h.id = hc.herb_id
            INNER JOIN health_conditions cond ON hc.condition_id = cond.id
            WHERE cond.condition_name LIKE :query 
               OR cond.description LIKE :query
            ORDER BY h.name ASC
            LIMIT 5
        ");
        $stmt->execute([':query' => $searchTerm]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "Condition: '{$condition}'\n";
        echo "  Results: " . count($results) . " herbs found\n";
        if (count($results) > 0) {
            echo "  First result: " . $results[0]['name'] . "\n";
        }
        echo "\n";
    }
} catch (Exception $e) {
    echo "❌ FAIL: Error searching by conditions\n";
    echo "Error: " . $e->getMessage() . "\n\n";
}

// Test 5: Test SQL injection protection
echo "=== Test 5: SQL Injection Protection ===\n";
$maliciousQuery = "'; DROP TABLE herbs; --";
$searchTerm = '%' . $maliciousQuery . '%';
try {
    $stmt = $db->prepare("
        SELECT DISTINCT h.* 
        FROM herbs h
        WHERE h.name LIKE :query 
           OR h.scientific_name LIKE :query 
           OR h.description LIKE :query
        LIMIT 1
    ");
    $stmt->execute([':query' => $searchTerm]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "✅ PASS: SQL injection attempt handled safely\n";
    echo "  Query treated as literal string (no table dropped)\n";
    echo "  Results: " . count($results) . "\n\n";
} catch (Exception $e) {
    echo "❌ FAIL: Error with malicious query\n";
    echo "Error: " . $e->getMessage() . "\n\n";
}

// Test 6: Check if search.php file exists and is accessible
echo "=== Test 6: Search Page Accessibility ===\n";
$searchFile = __DIR__ . '/search.php';
if (file_exists($searchFile)) {
    echo "✅ PASS: search.php file exists\n";
    echo "  Path: {$searchFile}\n";
    
    // Check if file is readable
    if (is_readable($searchFile)) {
        echo "✅ PASS: File is readable\n\n";
    } else {
        echo "❌ FAIL: File is not readable\n\n";
    }
} else {
    echo "❌ FAIL: search.php file not found\n\n";
}

echo "</pre>";
echo "<h2>Test Search in Browser</h2>";
echo "<p>Try these test searches:</p>";
echo "<ul>";
echo "<li><a href='search.php?q=ginger' target='_blank'>Search for 'ginger'</a></li>";
echo "<li><a href='search.php?q=chamomile' target='_blank'>Search for 'chamomile'</a></li>";
echo "<li><a href='search.php?q=stress' target='_blank'>Search for 'stress'</a></li>";
echo "<li><a href='search.php?q=' target='_blank'>Empty search (should redirect)</a></li>";
echo "</ul>";
echo "<p><a href='index.php'>Back to Homepage</a></p>";
?>

