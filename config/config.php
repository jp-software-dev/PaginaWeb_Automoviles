<?php
if (session_status() === PHP_SESSION_NONE) session_start();

$host = 'localhost';
$dbname = 'collection_cars';
$user = 'root';
$pass = '';     

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}

// Obtiene todos los vehículos con sus características
function getVehicles($pdo) {
    $stmt = $pdo->query("SELECT * FROM vehicles ORDER BY id DESC");
    $vehicles = $stmt->fetchAll();
    foreach ($vehicles as &$v) {
        $stmtF = $pdo->prepare("SELECT feature FROM vehicle_features WHERE vehicle_id = ?");
        $stmtF->execute([$v['id']]);
        $v['features'] = $stmtF->fetchAll(PDO::FETCH_COLUMN);
    }
    return $vehicles;
}

// Obtiene un vehículo por ID
function getVehicle($pdo, $id) {
    $stmt = $pdo->prepare("SELECT * FROM vehicles WHERE id = ?");
    $stmt->execute([$id]);
    $v = $stmt->fetch();
    if ($v) {
        $stmtF = $pdo->prepare("SELECT feature FROM vehicle_features WHERE vehicle_id = ?");
        $stmtF->execute([$id]);
        $v['features'] = $stmtF->fetchAll(PDO::FETCH_COLUMN);
    }
    return $v;
}

// Elimina las imágenes físicas de un vehículo
function deleteCarImages($pdo, $id, $basePath) {
    $stmt = $pdo->prepare("SELECT image_base, image_extension, total_images FROM vehicles WHERE id = ?");
    $stmt->execute([$id]);
    $car = $stmt->fetch();
    if ($car && $car['image_base']) {
        for ($i = 1; $i <= $car['total_images']; $i++) {
            $file = $basePath . '/' . $car['image_base'] . $i . $car['image_extension'];
            if (file_exists($file)) unlink($file);
        }
    }
}
?>