<!-- index.php -->
<?php include('manage.php'); ?>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        /* Custom styles for better mobile responsiveness */
        .container {
            width: 100%;
            max-width: 960px; /* Maximum container width for large screens */
        }

        /* Adjusting modal size on small screens */
        .modal-dialog {
            max-width: 90%; /* Ensure the modal fits on small screens */
        }

        /* Making sure table headers are properly aligned on mobile */
        th,
        td {
            text-align: center;
        }

        /* Ensuring the table is scrollable on small screens */
        .table-responsive {
            overflow-x: auto;
        }

        /* Responsive styles for larger screens */
        @media (max-width: 768px) {
            /* Center-align text for mobile devices */
            h1 {
                text-align: center;
                font-size: 1.5rem;
            }

            /* Adjusting margins on small screens */
            .container {
                padding-left: 15px;
                padding-right: 15px;
            }

            /* Modifying the button size for smaller screens */
            #addProfileBtn {
                width: 100%; /* Make the button full-width on small screens */
            }

            /* Ensure table columns have enough space and are scrollable on mobile */
            .table-responsive {
                margin-top: 20px;
                overflow-x: auto;
            }
        }
    </style>
</head>

<body>

    <div class="container mt-5">
        <!-- Heading and Add Profile button -->
        <div class="d-flex justify-content-between align-items-center mb-4 flex-column flex-md-row">
            <h1 class="mb-3 mb-md-0">Profile Management</h1>
            <button id="addProfileBtn" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addProfileModal">Add Profile</button>
        </div>

        <!-- Profile Table -->
        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead class="table-dark">
                    <tr>
                        <th>Profile Name</th>
                        <th>Available Code</th>
                        <th>Used Code</th>
                        <th>Upload</th>
                    </tr>
                </thead>
                <tbody id="profileTable">
                    <!-- Profiles will be populated here -->
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add Profile Modal -->
    <div class="modal fade" id="addProfileModal" tabindex="-1" aria-labelledby="addProfileModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addProfileModalLabel">Add New Profile</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="addProfileForm" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="profile_name" class="form-label">Profile Name</label>
                            <input type="text" name="profile_name" class="form-control" id="profile_name" placeholder="Enter profile name" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Save Profile</button>
                    </form>
                </div>
            </div>
        </div>
    </div>


<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>

<script>
   // Function to fetch and display profiles
function fetchProfiles() {
    // Fetch profiles
    $.ajax({
        url: 'fetch_profile.php',  // Fetch profile data
        type: 'GET',
        dataType: 'json',
        success: function (profileData) {
            // Fetch voucher counts
            $.ajax({
                url: 'get_voucher_counts.php', // Fetch voucher counts
                type: 'GET',
                dataType: 'json',
                success: function (voucherData) {
                    let rows = '';

                    // Loop through profiles and match with voucher data
                    profileData.forEach(profile => {
                        let voucherCount = 0;
                        let usedCount = 0;

                        // Find the voucher count and used count for the current profile
                        voucherData.forEach(voucher => {
                            if (voucher.profile_id === profile.id) {
                                voucherCount = voucher.total_codes; // Total vouchers
                                usedCount = voucher.used_codes;    // Used vouchers
                            }
                        });

                        let availableCount = voucherCount - usedCount; // Calculate available vouchers

                        // Add row for each profile
                        rows += `<tr>
                            <td>${profile.profile_name}</td>
                            <td>${availableCount}</td>   <!-- Available Codes -->
                            <td>${usedCount}</td>       <!-- Used Codes -->
                            <td>
                                <form action="upload_voucher.php" method="POST" enctype="multipart/form-data">
    <input type="hidden" name="profile_name" value="${profile.profile_name}">  <!-- Add profile_name here -->
    <input type="hidden" name="profile_id" value="${profile.id}">
    <input type="file" class="form-control" name="voucher_file" accept=".csv" required>
    <button type="submit" class="btn btn-primary mt-2">Upload CSV</button>
     </form>

                            </td>
                        </tr>`;
                    });

                    // Add rows to the table
                    $('#profileTable').html(rows);
                },
                error: function () {
                    alert("Failed to fetch voucher counts. Please try again.");
                }
            });
        },
        error: function () {
            alert("Failed to fetch profiles. Please try again.");
        }
    });
}

    

    // Document ready function
    $(document).ready(function () {
        fetchProfiles();

        // Handle form submission to add a profile
        $('#addProfileForm').submit(function (e) {
            e.preventDefault();
            $.ajax({
                url: 'add_profile.php',
                type: 'POST',
                data: $(this).serialize(),
                success: function (response) {
                    alert(response);
                    $('#addProfileModal').modal('hide');
                    $('#addProfileForm')[0].reset();
                    fetchProfiles();
                },
                error: function () {
                    alert("Failed to add profile. Please try again.");
                }
            });
        });
    });
</script>

</body>
</html>
