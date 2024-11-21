<?php
// fetch_profile.php
include 'db.php';

$sql = "SELECT * FROM profile";
$result = $conn->query($sql);

$profiles = [];
while ($row = $result->fetch_assoc()) {
    $profiles[] = $row;
}

echo json_encode($profiles);

$conn->close();
?>
