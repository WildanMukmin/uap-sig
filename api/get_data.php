<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once '../config/database.php';

$conn = getConnection();

if (!$conn) {
    jsonResponse(false, "Database connection failed", null, 500);
}

// Query untuk mengambil semua data
$sql = "SELECT * FROM sarana_ibadah ORDER BY created_at DESC";
$result = $conn->query($sql);

if (!$result) {
    jsonResponse(false, "Query failed: " . $conn->error, null, 500);
}

// Membuat struktur GeoJSON
$geojson = [
    'type' => 'FeatureCollection',
    'features' => []
];

while ($row = $result->fetch_assoc()) {
    $feature = [
        'type' => 'Feature',
        'geometry' => [
            'type' => 'Point',
            'coordinates' => [
                floatval($row['longitude']),
                floatval($row['latitude'])
            ]
        ],
        'properties' => [
            'id' => intval($row['id']),
            'nama' => $row['nama'],
            'jenis' => $row['jenis'],
            'alamat' => $row['alamat'],
            'kecamatan' => $row['kecamatan'],
            'kapasitas' => intval($row['kapasitas']),
            'tahun_berdiri' => $row['tahun_berdiri'],
            'keterangan' => $row['keterangan'],
            'foto' => $row['foto'],
            'created_at' => $row['created_at'],
            'updated_at' => $row['updated_at']
        ]
    ];
    
    array_push($geojson['features'], $feature);
}

$conn->close();

// Return GeoJSON
echo json_encode($geojson, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
?>