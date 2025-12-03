
<?php
function getConnection() {
    try {
        $db_path = __DIR__ . '/../database/pizzano.db';
        
        if (!file_exists(dirname($db_path))) {
            mkdir(dirname($db_path), 0755, true);
        }
        
        $pdo = new PDO('sqlite:' . $db_path);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        
        if (!file_exists($db_path) || filesize($db_path) == 0) {
            initDatabase($pdo);
        }
        
        return $pdo;
    } catch (PDOException $e) {
        die("Database connection failed: " . $e->getMessage());
    }
}

function initDatabase($pdo) {
    $schema = file_get_contents(__DIR__ . '/../database/schema.sql');
    $pdo->exec($schema);
    
    $seed = file_get_contents(__DIR__ . '/../database/seed.sql');
    if ($seed) {
        $pdo->exec($seed);
    }
}
?>
