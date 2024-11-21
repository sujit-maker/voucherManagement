<?php
// add_profile.php
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $profile_name = $_POST['profile_name'];

    $sql = "INSERT INTO profile (profile_name) VALUES ('$profile_name')";

    if ($conn->query($sql) === TRUE) {
        echo "Profile added successfully!";
    } else {
        echo "Error: " . $conn->error;
    }
}


$conn->close();
?>
