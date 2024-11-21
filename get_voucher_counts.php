<?php
include 'db.php';
$response = array();

try {
    // Query to get counts for available and used codes
    $query = "
        SELECT 
            p.id AS profile_id, 
            p.profile_name,
            COUNT(v.voucher_code) AS total_codes,
            SUM(CASE WHEN v.mobile IS NOT NULL AND v.mobile != '' THEN 1 ELSE 0 END) AS used_codes
        FROM profile p
        LEFT JOIN vouchers v ON p.id = v.profile_id
        GROUP BY p.id, p.profile_name
    ";

    $result = $conn->query($query);

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            // Calculate available codes
            $row['available_codes'] = $row['total_codes'] - $row['used_codes'];
            $response[] = $row;
        }
    } else {
        $response = ['message' => 'No data found'];
    }

    echo json_encode($response);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}

$conn->close();
?>
