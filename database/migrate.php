<?php



require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;

// Load environment variables
$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->safeLoad();

$host = $_ENV['DB_HOST'] ?? 'localhost';
$port = $_ENV['DB_PORT'] ?? '3306';
$database = $_ENV['DB_DATABASE'] ?? 'projectshare';
$username = $_ENV['DB_USERNAME'] ?? 'root';
$password = $_ENV['DB_PASSWORD'] ?? '';

try {
    // First connect without database to create it if needed
    $pdo = new PDO("mysql:host={$host};port={$port};charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);

    // Create database if not exists
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$database}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "Database '{$database}' ready.\n";

    // Connect to the database
    $pdo->exec("USE `{$database}`");

    // Create migrations tracking table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS migrations (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            migration VARCHAR(255) NOT NULL UNIQUE,
            executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");

    // Get already executed migrations
    $executed = $pdo->query("SELECT migration FROM migrations")->fetchAll(PDO::FETCH_COLUMN);

    // Get migration files
    $migrationsPath = __DIR__ . '/migrations';
    $files = glob($migrationsPath . '/*.sql');
    sort($files);

    $count = 0;
    foreach ($files as $file) {
        $filename = basename($file);

        if (in_array($filename, $executed)) {
            echo "Skipping: {$filename} (already executed)\n";
            continue;
        }

        echo "Running: {$filename}...\n";

        $sql = file_get_contents($file);

        // Execute the migration
        $pdo->exec($sql);

        // Record the migration
        $stmt = $pdo->prepare("INSERT INTO migrations (migration) VALUES (?)");
        $stmt->execute([$filename]);

        echo "Completed: {$filename}\n";
        $count++;
    }

    if ($count === 0) {
        echo "\nNo new migrations to run.\n";
    } else {
        echo "\n{$count} migration(s) executed successfully.\n";
    }

} catch (PDOException $e) {
    echo "Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}
