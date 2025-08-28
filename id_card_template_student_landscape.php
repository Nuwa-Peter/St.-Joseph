<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Student ID Card</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;700&display=swap');

        .student-card {
            width: 85.6mm;
            height: 53.98mm;
            background: #fff;
            border-radius: 3mm;
            overflow: hidden;
            display: flex;
            font-family: 'Open Sans', sans-serif;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            page-break-inside: avoid;
        }

        .student-card .left-panel {
            width: 28mm;
            background: #154284; /* Deep Blue */
            color: white;
            text-align: center;
            padding: 4mm 2mm;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            align-items: center;
        }
        .left-panel .school-logo {
            width: 18mm;
            height: 18mm;
            border-radius: 50%;
            background: white;
            padding: 1mm;
        }
        .left-panel .role-label {
            font-weight: 700;
            font-size: 10pt;
            transform: rotate(-90deg);
            white-space: nowrap;
        }

        .student-card .right-panel {
            width: 57.6mm;
            padding: 3mm;
            position: relative;
        }
        .right-panel .school-name {
            font-size: 9pt;
            font-weight: 700;
            color: #154284;
            text-align: center;
        }
        .right-panel .id-label {
            font-size: 7pt;
            color: #777;
            text-align: center;
            margin-bottom: 2mm;
        }

        .details-grid {
            display: flex;
            margin-top: 3mm;
        }
        .details-grid .photo {
            width: 25mm;
            height: 30mm;
            border: 1mm solid #154284;
            margin-right: 3mm;
        }
        .details-grid .info {
            font-size: 7pt;
        }
        .info .info-item { margin-bottom: 1.5mm; }
        .info .label { font-weight: 700; color: #333; }
        .info .value { color: #555; }

        .card-footer-landscape {
            position: absolute;
            bottom: 2mm;
            left: 3mm;
            right: 3mm;
            display: flex;
            justify-content: space-between;
            font-size: 5pt;
            color: #555;
        }

        @media print {
            body { background: #fff; }
            .student-card {
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
    <div class="student-card">
        <div class="left-panel">
            <img src="<?php echo htmlspecialchars($school_settings['school_logo_path'] ?? 'images/logo.png'); ?>" alt="Logo" class="school-logo">
            <span class="role-label">STUDENT</span>
        </div>
        <div class="right-panel">
            <div class="school-name"><?php echo htmlspecialchars(strtoupper($school_settings['school_name'] ?? 'SCHOOL NAME')); ?></div>
            <div class="id-label">STUDENT IDENTIFICATION CARD</div>
            <div class="details-grid">
                <img src="<?php echo htmlspecialchars($user['photo_path']); ?>" alt="Student Photo" class="photo">
                <div class="info">
                    <div class="info-item">
                        <span class="label">Name:</span>
                        <span class="value"><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="label">ID No:</span>
                        <span class="value"><?php echo htmlspecialchars($user['unique_id'] ?? 'N/A'); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="label">Class:</span>
                        <span class="value"><?php echo htmlspecialchars(trim(($user['class_name'] ?? '') . ' ' . ($user['stream_name'] ?? ''))); ?></span>
                    </div>
                     <div class="info-item">
                        <span class="label">LIN:</span>
                        <span class="value"><?php echo htmlspecialchars($user['lin'] ?? 'N/A'); ?></span>
                    </div>
                </div>
            </div>
            <div class="card-footer-landscape">
                <span><strong>Issued:</strong> <?php echo htmlspecialchars($issue_date); ?></span>
                <span><strong>Expires:</strong> <?php echo htmlspecialchars($expiry_date); ?></span>
            </div>
        </div>
    </div>
</body>
</html>
