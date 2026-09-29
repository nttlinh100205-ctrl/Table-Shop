<?php
/**
 * Script đồng bộ dữ liệu Table Shop từ local MySQL lên Aiven MySQL
 * Cách chạy:
 *   php sync_to_aiven.php
 * hoặc truyền tham số:
 *   php sync_to_aiven.php --host=xxx --port=xxx --user=avnadmin --pass=xxx --db=defaultdb
 */

$options = getopt('', ['host:', 'port:', 'user:', 'pass:', 'db:', 'ca:']);

$host = $options['host'] ?? null;
$port = $options['port'] ?? null;
$user = $options['user'] ?? 'avnadmin';
$pass = $options['pass'] ?? null;
$db   = $options['db']   ?? 'defaultdb';
$ca   = $options['ca']   ?? (file_exists('C:/Users/Admin/Downloads/ca.pem') ? 'C:/Users/Admin/Downloads/ca.pem' : null);

echo "=====================================================\n";
echo "   DONG BO DU LIEU TABLE SHOP LEN AIVEN MYSQL\n";
echo "=====================================================\n\n";

if (!$host) {
    echo "Nhap DB_HOST tu Aiven (vi du: mysql-xxxx.aivencloud.com): ";
    $host = trim(fgets(STDIN));
}

if (!$port) {
    echo "Nhap DB_PORT tu Aiven (vi du: 16933): ";
    $port = trim(fgets(STDIN));
}

if (!$pass) {
    echo "Nhap DB_PASSWORD tu Aiven: ";
    $pass = trim(fgets(STDIN));
}

if (!$ca) {
    echo "Nhap duong dan file ca.pem (neu de trong se thu tim trong Downloads): ";
    $caInput = trim(fgets(STDIN));
    if ($caInput && file_exists($caInput)) {
        $ca = $caInput;
    }
}

echo "\nThong tin ket noi:\n";
echo "- Host: {$host}\n";
echo "- Port: {$port}\n";
echo "- User: {$user}\n";
echo "- Database: {$db}\n";
echo "- CA file: " . ($ca ?: 'Khong dung CA (co the loi neu Aiven bat SSL mode REQUIRED)') . "\n\n";

$sqlFile = __DIR__ . '/database/table_shop_dump.sql';
if (!file_exists($sqlFile)) {
    die("LOI: Khong tim thay file dump {$sqlFile}\n");
}

echo "Dang ket noi den Aiven MySQL...\n";

$pdoOptions = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_TIMEOUT => 30,
];

if ($ca && file_exists($ca)) {
    $pdoOptions[PDO::MYSQL_ATTR_SSL_CA] = $ca;
}

try {
    $dsn = "mysql:host={$host};port={$port};dbname={$db};charset=utf8mb4";
    $pdo = new PDO($dsn, $user, $pass, $pdoOptions);
    echo "==> Ket noi thanh cong den Aiven MySQL!\n\n";
} catch (Exception $e) {
    // Thu ket noi voi SSL verify false neu khong co ca hoac ca loi
    try {
        echo "Thu lai che do SSL tu do...\n";
        $pdoOptions[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
        $pdo = new PDO($dsn, $user, $pass, $pdoOptions);
        echo "==> Ket noi thanh cong!\n\n";
    } catch (Exception $e2) {
        die("LOI KET NOI AIVEN: " . $e2->getMessage() . "\n");
    }
}

echo "Dang nap du lieu tu database/table_shop_dump.sql vao Aiven...\n";

$sql = file_get_contents($sqlFile);

// Thuc thi theo tung cau lenh de tranh loi packet size
$statements = array_filter(array_map('trim', explode(";\n", $sql)));

$pdo->exec("SET FOREIGN_KEY_CHECKS=0");
$pdo->exec("SET sql_require_primary_key=0");

$total = count($statements);
$i = 0;
foreach ($statements as $stmt) {
    if (empty($stmt) || str_starts_with($stmt, '--')) {
        continue;
    }
    try {
        $pdo->exec($stmt);
        $i++;
    } catch (Exception $e) {
        echo "Canh bao tai cau lenh: " . substr($stmt, 0, 60) . "...\n";
        echo "Chi tiet: " . $e->getMessage() . "\n";
    }
}

$pdo->exec("SET FOREIGN_KEY_CHECKS=1");

echo "\n=====================================================\n";
echo " DONG BO THANH CONG! ({$i} cau lenh da duoc thuc thi)\n";
echo "=====================================================\n";

// Kiem tra lai so luong san pham tren Aiven
try {
    $stmt = $pdo->query("SELECT COUNT(*) as cnt FROM products");
    $cnt = $stmt->fetch(PDO::FETCH_ASSOC)['cnt'] ?? 0;
    echo "- So luong san pham tren Aiven hien tai: {$cnt}\n";

    $stmt = $pdo->query("SELECT name FROM categories");
    $cats = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "- Cac danh muc tren Aiven: " . implode(', ', $cats) . "\n";
} catch (Exception $e) {
    // ignore
}
