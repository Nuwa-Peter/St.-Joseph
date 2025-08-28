<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>ID Card</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap');

        body {
            margin: 0;
            padding: 0;
            background: #e0e0e0;
            font-family: 'Roboto', sans-serif;
        }

        .card-container {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            padding: 20px;
            justify-content: center;
        }

        .id-card {
            width: 53.98mm;
            height: 85.6mm;
            background: #fff;
            border-radius: 5mm;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            page-break-inside: avoid;
        }

        .id-card-header {
            background: #0d47a1; /* Professional Blue */
            padding: 5mm 4mm;
            text-align: center;
            color: white;
        }
        .id-card-header .school-logo {
            width: 12mm;
            height: 12mm;
            border-radius: 50%;
            border: 1mm solid white;
            margin: 0 auto 2mm;
        }
        .id-card-header .school-name {
            font-size: 7pt;
            font-weight: 700;
            line-height: 1.2;
        }

        .id-card-body {
            flex-grow: 1;
            padding: 4mm;
            text-align: center;
        }
        .photo-wrapper {
            width: 30mm;
            height: 30mm;
            margin: 0 auto 3mm;
            border-radius: 50%;
            overflow: hidden;
            border: 2mm solid #e0e0e0;
        }
        .photo-wrapper img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .user-name {
            font-size: 11pt;
            font-weight: 700;
            color: #333;
        }
        .user-role {
            font-size: 8pt;
            font-weight: 400;
            color: #0d47a1;
            text-transform: uppercase;
            margin-top: 1mm;
        }

        .id-card-footer {
            padding: 3mm;
            text-align: center;
        }
        .user-unique-id {
            font-size: 8pt;
            color: #555;
        }
        .qr-code img {
            width: 18mm;
            height: 18mm;
            margin: 2mm auto 0;
        }

        @media print {
            body { background: #fff; }
            .card-container {
                padding: 0;
                gap: 0;
            }
            .id-card {
                box-shadow: none;
                margin: 0;
                border-radius: 0;
            }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <!-- This template will be included in a loop -->
    <div class="id-card">
        <div class="id-card-header">
            <img src="<?php echo htmlspecialchars($school_settings['school_logo_path'] ?? 'images/logo.png'); ?>" alt="Logo" class="school-logo">
            <div class="school-name"><?php echo htmlspecialchars(strtoupper($school_settings['school_name'] ?? 'SCHOOL NAME')); ?></div>
        </div>
        <div class="id-card-body">
            <div class="photo-wrapper">
                <img src="<?php echo htmlspecialchars($user['photo_path']); ?>" alt="User Photo">
            </div>
            <div class="user-name"><?php echo htmlspecialchars(strtoupper($user['first_name'] . ' ' . $user['last_name'])); ?></div>
            <div class="user-role"><?php echo htmlspecialchars(strtoupper($user['role'])); ?></div>
        </div>
        <div class="id-card-footer">
            <div class="user-unique-id">ID: <?php echo htmlspecialchars($user['unique_id'] ?? 'N/A'); ?></div>
            <div class="qr-code">
                <img src="data:image/png;base64,<?php echo base64_encode($user['qr_code']); ?>" alt="QR Code">
            </div>
        </div>
    </div>
</body>
</html>
