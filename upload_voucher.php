<?php
include 'db.php';  // Include your database connection file

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES['voucher_file'])) {
    // Get file details
    $fileName = $_FILES['voucher_file']['name'];
    $fileTmpName = $_FILES['voucher_file']['tmp_name'];
    $fileType = $_FILES['voucher_file']['type'];
    $profile_name = $_POST['profile_name'];  // Get the profile name from the form

    // Check if the uploaded file is a CSV
    if ($fileType !== 'text/csv' && pathinfo($fileName, PATHINFO_EXTENSION) !== 'csv') {
        echo "Please upload a valid CSV file.";
        exit;
    }

    // Check if profile exists and get the profile ID
    $sql = "SELECT id FROM profile WHERE profile_name = '$profile_name' LIMIT 1";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $profile_id = $row['id']; // Fetch the profile ID based on the profile name
    } else {
        echo "Profile not found.";
        exit;
    }

    // Open the uploaded CSV file for reading
    if (($handle = fopen($fileTmpName, 'r')) !== FALSE) {
        // Read CSV row by row
        while (($data = fgetcsv($handle)) !== FALSE) {
            $voucher_code = $data[0];  // First column: voucher_code

            // Insert voucher code and profile_id into the 'vouchers' table
            $sql = "INSERT INTO vouchers (voucher_code, profile_id) VALUES ('$voucher_code', '$profile_id')";
            if ($conn->query($sql) !== TRUE) {
                echo "Error: " . $conn->error;
            }
        }
        fclose($handle);  // Close the file
        echo "Voucher codes uploaded successfully!";
    } else {
        echo "Failed to open the CSV file.";
    }
} else {
    echo "No file uploaded.";
}

$conn->close();
?>
