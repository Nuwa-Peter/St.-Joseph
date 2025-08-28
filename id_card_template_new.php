<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>ID Card</title>
    <style>
        /* General Styles */
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f0f2f5;
        }

        /* Styles for each ID card page */
        .page {
            width: 85.6mm;
            height: 53.98mm;
            margin: 20mm auto;
            page-break-after: always;
            overflow: hidden;
            position: relative;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            background-color: white;
            display: flex;
            flex-direction: column;
        }

        /* Card Header */
        .card-header {
            background-color: #04174f; /* Dark Navy Blue */
            color: white;
            padding: 4mm;
            text-align: center;
            display: flex;
            align-items: center;
        }
        .card-header img {
            width: 10mm;
            height: 10mm;
            border-radius: 50%;
            margin-right: 3mm;
        }
        .card-header .school-name {
            font-size: 8pt;
            font-weight: bold;
            line-height: 1.1;
        }

        /* Card Body */
        .card-body {
            text-align: center;
            padding-top: 4mm;
            flex-grow: 1;
        }
        .photo-container {
            width: 24mm;
            height: 24mm;
            margin: 0 auto;
            border: 1.5mm solid #04174f;
            border-radius: 4mm;
            overflow: hidden;
        }
        .photo-container img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .user-name {
            font-size: 9pt;
            font-weight: bold;
            margin-top: 3mm;
        }
        .user-role {
            font-size: 7pt;
            color: #555;
            text-transform: uppercase;
        }
        .user-id {
            font-size: 7pt;
            color: #555;
        }

        /* Card Footer */
        .card-footer {
            background-color: #f0f2f5;
            padding: 2mm;
            font-size: 5pt;
            color: #333;
            text-align: center;
        }
        .card-footer .dates {
            display: flex;
            justify-content: space-around;
        }
        .qr-code {
            position: absolute;
            bottom: 2mm;
            right: 2mm;
            width: 12mm;
            height: 12mm;
        }

        /* Print-specific styles */
        @media print {
            body {
                background-color: white;
            }
            .page {
                margin: 0;
                box-shadow: none;
                width: 85.6mm;
                height: 53.98mm;
            }
            /* Hide the back of the card when printing for simplicity, can be expanded later */
            .card-back {
                display: none;
            }
        }
    </style>
</head>
<body>
    <!-- This is a template, the main generator file will loop and include this -->
    <div class="page card-front">
        <div class="card-header">
            <img src="<?php echo htmlspecialchars($user['logo_path'] ?? 'images/logo.png'); ?>" alt="School Logo">
            <div class="school-name">ST. JOSEPH'S VSS<br>NYAMITYOBORA</div>
        </div>
        <div class="card-body">
            <div class="photo-container">
                <img src="<?php echo htmlspecialchars($user['photo_path']); ?>" alt="User Photo">
            </div>
            <div class="user-name"><?php echo htmlspecialchars(strtoupper($user['first_name'] . ' ' . $user['last_name'])); ?></div>
            <div class="user-role"><?php echo htmlspecialchars(strtoupper($user['role'])); ?></div>
            <div class="user-id">ID: <?php echo htmlspecialchars($user['unique_id'] ?? 'N/A'); ?></div>
        </div>
        <div class="card-footer">
            <div class="dates">
                <span><strong>Issued:</strong> <?php echo htmlspecialchars($issue_date); ?></span>
                <span><strong>Expires:</strong> <?php echo htmlspecialchars($expiry_date); ?></span>
            </div>
        </div>
        <div class="qr-code">
             <img src="data:image/png;base64,<?php echo base64_encode($user['qr_code']); ?>" alt="QR Code">
        </div>
    </div>
</body>
</html>
