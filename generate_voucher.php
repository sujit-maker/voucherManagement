<?php 
// Database connection
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];

    if ($action === 'fetchProfiles') {
        // Fetch profiles from the database
        $query = "SELECT id, profile_name FROM profile";
        $result = $conn->query($query);

        $profiles = [];
        while ($row = $result->fetch_assoc()) {
            $profiles[] = $row;
        }
        echo json_encode($profiles);
        exit;
    }

    if ($action === 'generateVoucher') {
        $mobile = $_POST['mobile'] ?? null;
        $profileId = $_POST['profileId'] ?? null;

        if (!$mobile || !$profileId) {
            echo json_encode(['success' => false, 'message' => 'Mobile number and profile ID are required.']);
            exit;
        }

        // Fetch the voucher for the selected profile where mobile is NULL
        $query = "SELECT id, voucher_code FROM vouchers WHERE profile_id = ? AND mobile IS NULL LIMIT 1";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('i', $profileId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            $voucherId = $row['id'];
            $voucherCode = $row['voucher_code'];

            // Update the mobile number and let MySQL handle timestamp auto-update
            $updateQuery = "UPDATE vouchers SET mobile = ? WHERE id = ?";
            $updateStmt = $conn->prepare($updateQuery);
            $updateStmt->bind_param('si', $mobile, $voucherId);
            $updateStmt->execute();

            // Fetch the updated timestamp after the update
            $timestampQuery = "SELECT timestamp FROM vouchers WHERE id = ?";
            $timestampStmt = $conn->prepare($timestampQuery);
            $timestampStmt->bind_param('i', $voucherId);
            $timestampStmt->execute();
            $timestampResult = $timestampStmt->get_result();
            $timestamp = $timestampResult->fetch_assoc()['timestamp'];

            // Return the voucher code and the updated timestamp to the frontend
            echo json_encode(['success' => true, 'voucher' => $voucherCode, 'timestamp' => $timestamp]);
        } else {
            echo json_encode(['success' => false, 'message' => 'No available vouchers for the selected profile.']);
        }
        exit;
    }

    if ($action === 'fetchVouchers') {
        // Fetch only vouchers with a mobile number
        $query = "SELECT v.voucher_code, v.mobile, p.profile_name, v.timestamp 
                  FROM vouchers v
                  JOIN profile p ON v.profile_id = p.id
                  WHERE v.mobile IS NOT NULL
                  ORDER BY v.id DESC";
        
        $result = $conn->query($query);
        
        $vouchers = [];
        while ($row = $result->fetch_assoc()) {
            $vouchers[] = $row;
        }
        echo json_encode($vouchers);
        exit;
    }
}
?>
 <?php include('manage.php'); // Including the sidebar ?>


 <!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generate Voucher</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        /* Container for overall page */
        .container {
            width: 100%;
            max-width: 960px; /* Maximum container width for large screens */
        }
        /* For Card styling */
        .card {
            width: 100%;
            max-width: 600px;
            margin: auto;
        }

        /* Search bar and CSV button styling */
        .search-bar-wrapper {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 10px;
            margin-bottom: 20px;
        }

        .search-bar-wrapper input,
        .search-bar-wrapper button {
            max-width: 250px;
            width: 100%;
        }

        /* Table Responsiveness */
        .table-responsive {
            overflow-x: auto;
        }

        /* Customizing table for laptop screens */
        @media (min-width: 768px) and (max-width: 1024px) {
            .table-wrapper {
                width: 10%; /* Set to 90% of the screen for laptops */
            
            }
        }

        /* Media query for mobile view */
        @media (max-width: 768px) {
            .card {
                max-width: 100%; /* Card takes full width on small screens */
                margin-top: 10px;
            }

            .search-bar-wrapper {
                flex-direction: column;
            }

            .search-bar-wrapper input,
            .search-bar-wrapper button {
                max-width: 100%;
                width: 100%;
            }

            .table-wrapper {
                width: 100%; /* Ensure table takes full width on mobile */
                margin-top: 10px;
            }


        }
    </style>
</head>
<body>

<div class="container mt-5">
    <!-- Card for Generate Voucher -->
    <div class="card">
        <div class="card-header bg-secondary text-white text-center">
            <h3>Generate Voucher</h3>
        </div>
        <div class="card-body">
            <!-- Mobile Number Input -->
            <div class="mb-3">
                <label for="mobileNumber" class="form-label">Enter Mobile Number:</label>
                <input type="text" class="form-control" id="mobileNumber" placeholder="Enter Mobile Number" maxlength="15">
            </div>

            <!-- Profile Dropdown -->
            <div class="mb-3">
                <label for="profileDropdown" class="form-label">Select Profile Name:</label>
                <select id="profileDropdown" class="form-select">
                    <option value="">Select Profile</option>
                </select>
            </div>

            <!-- Generate Voucher Button -->
            <div class="d-grid gap-2">
                <button id="generateButton" class="btn btn-primary">Generate Voucher</button>
            </div>

            <!-- Voucher Display -->
            <div id="voucherDisplay" class="mt-4 text-center text-success fw-bold" style="display: none;"></div>
        </div>
    </div>

    <!-- Table to Display Generated Vouchers -->
    <div class="table-wrapper mt-4">
        <h4 class="text-center">Generated Vouchers</h4>
        
        <!-- Search and Download Button Section -->
        <div class="search-bar-wrapper">
            <input type="text" id="searchInput" class="form-control" placeholder="Search Mobile..." />
            <button id="downloadCsvBtn" class="btn btn-success">
                <i class="bi bi-download"></i> Download CSV
            </button>
        </div>

        <!-- Voucher Table -->
        <div class="table-responsive">
            <table id="voucherTable" class="table table-bordered table-striped mt-2">
                <thead class="table-primary">
                    <tr>
                        <th>Voucher Code</th>
                        <th>Mobile</th>
                        <th>Profile Name</th>
                        <th>Timestamp</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Entries will be dynamically added here -->
                </tbody>
            </table>
        </div>
    </div>
</div>





<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.js"></script>

<script>
    function fetchProfiles() {
        $.ajax({
            url: 'generate_voucher.php',
            method: 'POST',
            data: { action: 'fetchProfiles' },
            dataType: 'json',
            success: function (profiles) {
                let options = '<option value="">Select Profile</option>';
                profiles.forEach(profile => {
                    options += `<option value="${profile.id}">${profile.profile_name}</option>`;
                });
                $('#profileDropdown').html(options);
            },
            error: function () {
                alert('Failed to fetch profiles. Please try again.');
            }
        });
    }

    $('#generateButton').click(function () {
        const mobile = $('#mobileNumber').val().trim();
        const profileId = $('#profileDropdown').val();
        const profileName = $('#profileDropdown option:selected').text();

        if (!mobile || !profileId) {
            alert('Please enter a mobile number and select a profile.');
            return;
        }

        $.ajax({
            url: 'generate_voucher.php',
            method: 'POST',
            data: { action: 'generateVoucher', mobile, profileId },
            dataType: 'json',
            success: function (response) {
                if (response.success) {
                    $('#voucherDisplay').text(`Voucher Code: ${response.voucher}`).show();
                    setTimeout(() => $('#voucherDisplay').fadeOut(), 900000);

                    const newRow = `
                        <tr>
                            <td>${response.voucher}</td>
                            <td>${mobile}</td>
                            <td>${profileName}</td>
                            <td>${response.timestamp}</td>
                        </tr>`;
                    $('#voucherTable tbody').prepend(newRow);
                } else {
                    alert(response.message || 'Failed to generate voucher. Please try again.');
                }
            },
            error: function () {
                alert('An error occurred. Please try again.');
            }
        });
    });

    function fetchVouchers() {
        $.ajax({
            url: 'generate_voucher.php',
            method: 'POST',
            data: { action: 'fetchVouchers' },
            dataType: 'json',
            success: function (vouchers) {
                const tableBody = $('#voucherTable tbody');
                tableBody.empty();
                vouchers.forEach(voucher => {
                    const row = `
                        <tr>
                            <td>${voucher.voucher_code}</td>
                            <td>${voucher.mobile}</td>
                            <td>${voucher.profile_name}</td>
                            <td>${voucher.timestamp}</td>
                        </tr>`;
                    tableBody.append(row);
                });
            },
            error: function () {
                alert('Failed to fetch vouchers. Please try again.');
            }
        });
    }

    // Function to filter table based on search input
    $('#searchInput').on('keyup', function () {
        const searchTerm = $(this).val().toLowerCase();
        $('#voucherTable tbody tr').each(function () {
            const rowText = $(this).text().toLowerCase();
            $(this).toggle(rowText.indexOf(searchTerm) > -1);
        });
    });

    // Function to download the table as CSV
    $('#downloadCsvBtn').click(function () {
        let csv = 'Voucher Code,Mobile,Profile Name,Timestamp\n';
        
        $('#voucherTable tbody tr').each(function () {
            const row = $(this).find('td').map(function () {
                let cellText = $(this).text();
                
                // Check if the cell is a date or timestamp, if so, wrap it in single quotes
                if (cellText.includes('-') || cellText.includes('/')) {
                    cellText = `'${cellText}`; // Force Excel to treat it as text
                }
                return cellText;
            }).get().join(',');

            csv += row + '\n';
        });

        const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement('a');
        link.href = URL.createObjectURL(blob);
        link.download = 'vouchers.csv';
        link.click();
    });

    $(document).ready(function () {
        fetchProfiles();
        fetchVouchers();
    });
</script>

</body>
</html>
