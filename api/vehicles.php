<?php
header('Content-Type: application/json');
require_once '../config/config.php';

$vehicles = getVehicles($pdo);
$result = array_map(function($v) {
    return [
        'id' => $v['id'],
        'brand' => $v['brand'],
        'model' => $v['model'],
        'year' => $v['year'],
        'price' => number_format($v['price'], 2, '.', ''),
        'priceUnit' => $v['price_unit'],
        'kilometers' => $v['mileage'],
        'exteriorColor' => $v['exterior_color'],
        'interiorColor' => $v['interior_color'],
        'imageBase' => $v['image_base'],
        'imageExtension' => $v['image_extension'],
        'totalImages' => $v['total_images'],
        'specs' => [
            'motor' => $v['engine'],
            'potencia' => $v['potencia'],
            'aceleracion' => $v['aceleracion'],
            'velocidadMax' => $v['velocidad_max'],
            'transmision' => $v['transmision'],
            'traccion' => $v['traccion'],
            'consumo' => $v['consumo']
        ],
        'features' => $v['features'] ?? []
    ];
}, $vehicles);

echo json_encode($result, JSON_UNESCAPED_UNICODE);
?>