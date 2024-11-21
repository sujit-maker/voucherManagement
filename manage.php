<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Responsive Sidebar</title>
    <!-- Link to Bootstrap CSS (CDN) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Custom styles for the sidebar */
        .sidebar {
            height: 100vh;
            background-color: #343a40;
            position: fixed;
            top: 0;
            left: 0;
            width: 250px;
            padding-top: 20px;
            transition: all 0.3s ease;
        }
        .sidebar .nav-link {
            color: white;
            padding: 15px 20px;
            text-align: left;
            font-size: 18px;
            font-weight: 500;
        }
        .sidebar .nav-link:hover {
            background-color: #575757;
        }
        .sidebar .nav-link.active {
            background-color: #007bff;
            color: white;
        }
        .content {
            margin-left: 250px;
            padding: 20px;
        }
        /* Style for the content area */
        .container {
            margin-top: 20px;
        }
        /* Hide sidebar in small screens */
        @media (max-width: 767px) {
            .sidebar {
                transform: translateX(-250px);
            }
            .sidebar.show {
                transform: translateX(0);
            }
            .content {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>

    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <nav class="navbar navbar-dark">
            <ul class="navbar-nav flex-column">
                <li class="nav-item">
                    <a class="nav-link active" href="http://localhost/openwan/index.php" id="projectManagementLink">
                        <i class="bi bi-house-door"></i> Project Management
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="http://localhost/openwan/generate_voucher.php" id="generateVoucherLink">
                        <i class="bi bi-file-earmark-check"></i> Generate Voucher
                    </a>
                </li>
            </ul>
        </nav>
    </div>


    <!-- Link to Bootstrap JS and Popper.js (CDN) -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>

    <!-- Optional: Add icons using Bootstrap Icons -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.js"></script>

    <script>
        // This script ensures that the active class is added to the correct link based on the current page
        document.addEventListener('DOMContentLoaded', function () {
            // Get the current page URL
            var currentPage = window.location.pathname;

            // Select the links
            var projectManagementLink = document.getElementById('projectManagementLink');
            var generateVoucherLink = document.getElementById('generateVoucherLink');

            // Remove active class from all links
            projectManagementLink.classList.remove('active');
            generateVoucherLink.classList.remove('active');

            // Add active class to the current page link
            if (currentPage.includes('index.php')) {
                projectManagementLink.classList.add('active');
            } else if (currentPage.includes('generate_voucher.php')) {
                generateVoucherLink.classList.add('active');
            }
        });
    </script>
</body>
</html>
