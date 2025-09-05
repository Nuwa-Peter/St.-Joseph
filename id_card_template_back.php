<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>ID Card Back</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;700&display=swap');

        .card-back {
            width: 85.6mm;
            height: 53.98mm;
            background: #f1f1f1;
            border-radius: 3mm;
            overflow: hidden;
            font-family: 'Open Sans', sans-serif;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            page-break-inside: avoid;
            page-break-after: always;
            border: 1px solid #ccc;
            text-align: center;
            padding: 5mm;
            box-sizing: border-box;
        }
        .back-header {
            font-weight: 700;
            font-size: 9pt;
            color: #333;
            margin-bottom: 4mm;
        }
        .back-content {
            font-size: 7pt;
            color: #555;
            line-height: 1.6;
        }
        .back-footer {
            font-size: 6pt;
            color: #888;
            margin-top: 5mm;
            border-top: 0.5px solid #ccc;
            padding-top: 3mm;
        }
    </style>
</head>
<body>
    <!-- This template will be included in a loop -->
    <div class="card-back">
        <div class="back-header">
            PROPERTY OF
            <br>
            <?php echo htmlspecialchars(strtoupper($school_settings['school_name'] ?? 'SCHOOL NAME')); ?>
        </div>
        <div class="back-content">
            This card is for identification purposes only and is not transferable. If found, please return to the school office.
            <br><br>
            <strong>P.O. Box:</strong> <?php echo htmlspecialchars($school_settings['school_po_box'] ?? 'N/A'); ?>
            <br>
            <strong>Tel:</strong> <?php echo htmlspecialchars($school_settings['school_tel'] ?? 'N/A'); ?>
        </div>
        <div class="back-footer">
            <?php echo htmlspecialchars($school_settings['school_email'] ?? 'school@email.com'); ?>
        </div>
    </div>
</body>
</html>
