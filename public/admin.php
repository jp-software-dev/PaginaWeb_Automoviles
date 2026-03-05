<?php
session_start();
// Ruta corregida a config.php (sube un nivel)
require_once '../config/config.php';
define('IMAGE_PATH', __DIR__ . '/assets/images');

$action = $_GET['action'] ?? 'dashboard';

// LOGIN
if (!isset($_SESSION['admin_id']) && $action !== 'login') {
    $action = 'login';
}

if ($action === 'login' && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ?");
    $stmt->execute([$username]);
    $admin = $stmt->fetch();
    if ($admin && password_verify($password, $admin['password_hash'])) {
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_user'] = $admin['username'];
        header('Location: admin.php?action=dashboard');
        exit;
    } else {
        $error = "Usuario o contraseña incorrectos";
    }
}

// LOGOUT
if ($action === 'logout') {
    session_destroy();
    header('Location: admin.php');
    exit;
}

// ELIMINAR
if ($action === 'delete' && isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = (int)$_GET['id'];
    deleteCarImages($pdo, $id, IMAGE_PATH);
    $pdo->prepare("DELETE FROM vehicles WHERE id = ?")->execute([$id]);
    header('Location: admin.php?action=dashboard&msg=deleted');
    exit;
}

// GUARDAR NUEVO
if ($action === 'save' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $brand = $_POST['brand'];
    $model = $_POST['model'];
    $year = (int)$_POST['year'];
    $mileage = $_POST['mileage'];
    $exterior_color = $_POST['exterior_color'];
    $interior_color = $_POST['interior_color'];
    $engine = $_POST['engine'];
    $price = (float)$_POST['price'];
    $potencia = $_POST['potencia'] ?? null;
    $aceleracion = $_POST['aceleracion'] ?? null;
    $velocidad_max = $_POST['velocidad_max'] ?? null;
    $transmision = $_POST['transmision'] ?? null;
    $traccion = $_POST['traccion'] ?? null;
    $consumo = $_POST['consumo'] ?? null;
    $features = isset($_POST['features']) ? explode("\n", trim($_POST['features'])) : [];

    $stmt = $pdo->prepare("INSERT INTO vehicles (brand, model, year, mileage, exterior_color, interior_color, engine, price, potencia, aceleracion, velocidad_max, transmision, traccion, consumo) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$brand, $model, $year, $mileage, $exterior_color, $interior_color, $engine, $price, $potencia, $aceleracion, $velocidad_max, $transmision, $traccion, $consumo]);
    $vehicleId = $pdo->lastInsertId();

    $stmtFeat = $pdo->prepare("INSERT INTO vehicle_features (vehicle_id, feature) VALUES (?, ?)");
    foreach ($features as $feat) {
        $feat = trim($feat);
        if ($feat !== '') $stmtFeat->execute([$vehicleId, $feat]);
    }

    $totalImages = 0;
    $imageBase = '';
    $imageExt = '';
    if (!empty($_FILES['images']['name'][0])) {
        $uploaded = $_FILES['images'];
        $numFiles = count($uploaded['name']);
        $imageBase = 'car_' . $vehicleId;
        $firstExt = strtolower(pathinfo($uploaded['name'][0], PATHINFO_EXTENSION));
        $allowed = ['jpg','jpeg','png','webp'];
        if (in_array($firstExt, $allowed)) {
            $imageExt = '.' . $firstExt;
            $cont = 1;
            for ($i=0; $i<$numFiles; $i++) {
                if ($uploaded['error'][$i]===0) {
                    $tmp = $uploaded['tmp_name'][$i];
                    $ext = strtolower(pathinfo($uploaded['name'][$i], PATHINFO_EXTENSION));
                    if (in_array($ext, $allowed)) {
                        $newName = $imageBase . $cont . '.' . $ext;
                        if (move_uploaded_file($tmp, IMAGE_PATH.'/'.$newName)) $cont++;
                    }
                }
            }
            $totalImages = $cont - 1;
        }
    }
    $pdo->prepare("UPDATE vehicles SET image_base=?, image_extension=?, total_images=? WHERE id=?")->execute([$imageBase, $imageExt, $totalImages, $vehicleId]);
    header('Location: admin.php?action=dashboard&msg=added');
    exit;
}

// ACTUALIZAR
if ($action === 'update' && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = (int)$_POST['id'];
    $brand = $_POST['brand'];
    $model = $_POST['model'];
    $year = (int)$_POST['year'];
    $mileage = $_POST['mileage'];
    $exterior_color = $_POST['exterior_color'];
    $interior_color = $_POST['interior_color'];
    $engine = $_POST['engine'];
    $price = (float)$_POST['price'];
    $potencia = $_POST['potencia'] ?? null;
    $aceleracion = $_POST['aceleracion'] ?? null;
    $velocidad_max = $_POST['velocidad_max'] ?? null;
    $transmision = $_POST['transmision'] ?? null;
    $traccion = $_POST['traccion'] ?? null;
    $consumo = $_POST['consumo'] ?? null;

    $stmt = $pdo->prepare("UPDATE vehicles SET brand=?, model=?, year=?, mileage=?, exterior_color=?, interior_color=?, engine=?, price=?, potencia=?, aceleracion=?, velocidad_max=?, transmision=?, traccion=?, consumo=? WHERE id=?");
    $stmt->execute([$brand, $model, $year, $mileage, $exterior_color, $interior_color, $engine, $price, $potencia, $aceleracion, $velocidad_max, $transmision, $traccion, $consumo, $id]);

    $pdo->prepare("DELETE FROM vehicle_features WHERE vehicle_id = ?")->execute([$id]);
    $features = isset($_POST['features']) ? explode("\n", trim($_POST['features'])) : [];
    $stmtFeat = $pdo->prepare("INSERT INTO vehicle_features (vehicle_id, feature) VALUES (?, ?)");
    foreach ($features as $feat) {
        $feat = trim($feat);
        if ($feat !== '') $stmtFeat->execute([$id, $feat]);
    }

    if (!empty($_FILES['new_images']['name'][0])) {
        $car = getVehicle($pdo, $id);
        $imageBase = $car['image_base'];
        $imageExt = $car['image_extension'];
        $totalImages = $car['total_images'];
        $uploaded = $_FILES['new_images'];
        $numFiles = count($uploaded['name']);
        $cont = $totalImages + 1;
        $allowed = ['jpg','jpeg','png','webp'];
        for ($i=0; $i<$numFiles; $i++) {
            if ($uploaded['error'][$i]===0) {
                $tmp = $uploaded['tmp_name'][$i];
                $ext = strtolower(pathinfo($uploaded['name'][$i], PATHINFO_EXTENSION));
                if (in_array($ext, $allowed)) {
                    if (!$imageBase) {
                        $imageBase = 'car_' . $id;
                        $imageExt = '.' . $ext;
                    }
                    $newName = $imageBase . $cont . '.' . $ext;
                    if (move_uploaded_file($tmp, IMAGE_PATH.'/'.$newName)) $cont++;
                }
            }
        }
        $newTotal = $cont - 1;
        $pdo->prepare("UPDATE vehicles SET image_base=?, image_extension=?, total_images=? WHERE id=?")->execute([$imageBase, $imageExt, $newTotal, $id]);
    }

    if (isset($_POST['delete_images'])) {
        $toDelete = $_POST['delete_images'];
        $car = getVehicle($pdo, $id);
        $base = $car['image_base'];
        $ext = $car['image_extension'];
        $total = $car['total_images'];
        $keep = [];
        for ($i=1; $i<=$total; $i++) {
            if (!in_array($i, $toDelete)) {
                $keep[] = $i;
            } else {
                $file = IMAGE_PATH . '/' . $base . $i . $ext;
                if (file_exists($file)) unlink($file);
            }
        }
        $newTotal = count($keep);
        if ($newTotal > 0) {
            sort($keep);
            $cont = 1;
            foreach ($keep as $oldIdx) {
                $oldFile = IMAGE_PATH . '/' . $base . $oldIdx . $ext;
                $newFile = IMAGE_PATH . '/' . $base . $cont . $ext;
                if ($oldIdx != $cont) rename($oldFile, $newFile);
                $cont++;
            }
        }
        $pdo->prepare("UPDATE vehicles SET total_images = ? WHERE id = ?")->execute([$newTotal, $id]);
    }
    header('Location: admin.php?action=dashboard&msg=updated');
    exit;
}

// INCLUIR VISTAS (están en el mismo directorio)
if ($action === 'login') {
    include 'admin_login.php';
} elseif ($action === 'dashboard') {
    $vehicles = getVehicles($pdo);
    include 'admin_dashboard.php';
} elseif ($action === 'add') {
    include 'admin_form.php';
} elseif ($action === 'edit' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $car = getVehicle($pdo, $id);
    include 'admin_form.php';
}
?>