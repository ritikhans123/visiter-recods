<?php
$host = "localhost";
$user = "root";
$pass = "";
$db = "Visitors_db";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    http_response_code(500); 
} 

$data = json_decode(file_get_contents("php://input"), true);

if ($data) {
    $name         = $conn->real_escape_string(trim($data['name']));
    $purpose      = $conn->real_escape_string(trim($data['purpose']));
    $date         = $conn->real_escape_string(trim($data['date']));
    $visitorCount = (int) $data['visitorCount'];
    $phone        = $conn->real_escape_string(trim($data['phone']));
    $address      = $conn->real_escape_string(trim($data['address']));
    $imageData    = $data['image'];

    // Handle image
    $img = str_replace('data:image/png;base64,', '', $imageData);
    $img = str_replace(' ', '+', $img);
    $imagePath = "images/" . uniqid("face_", true) . ".png";

    if (!is_dir('images')) {
        mkdir('images', 0777, true);
    }

    if (file_put_contents($imagePath, base64_decode($img))) {
        // Save to DB (update table columns as needed)
        $stmt = $conn->prepare("
            INSERT INTO visitor (name, purpose, visit_date, visitor_count, phone, address, image_path)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("sssisss", $name, $purpose, $date, $visitorCount, $phone, $address, $imagePath);

        if ($stmt->execute()) {
            echo "✅ Successfully saved!";
        } else {
            echo "❌ DB error: " . $stmt->error;
        }

        $stmt->close();
    } else {
        echo "❌ Image upload failed.";
    }
} else {
    echo "❌ No data received.";
}

$conn->close();
?>







