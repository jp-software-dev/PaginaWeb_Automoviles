<?php
header('Content-Type: application/json');
require_once '../config/config.php';

$stmt = $pdo->query("SELECT id, brand, model, year FROM vehicles ORDER BY brand, model");
$options = [];
while ($row = $stmt->fetch()) {
    $options[] = [
        'value' => $row['id'],
        'label' => $row['brand'] . ' ' . $row['model'] . ' ' . $row['year']
    ];
}
echo json_encode($options, JSON_UNESCAPED_UNICODE);
?>