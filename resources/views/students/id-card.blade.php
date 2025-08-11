<!DOCTYPE html>
<html>
<head>
    <title>ID Card</title>
    <style>
        @page { margin: 0; }
        body { font-family: sans-serif; margin: 0; }
        .card {
            width: 85.6mm;
            height: 53.98mm;
            border: 1px solid #ccc;
            padding: 10px;
            box-sizing: border-box;
        }
    </style>
</head>
<body>
    <div class="card">
        <header>
            <img src="{{ public_path('images/logo.png') }}" alt="School Logo" style="width: 50px; height: 50px; float: left;">
            <div style="text-align: center;">
                <h3 style="margin: 0;">St. Joseph's VSS</h3>
                <p style="margin: 0;">Student ID Card</p>
            </div>
        </header>
        <div style="margin-top: 10px;">
            <img src="{{ $photoData }}" alt="Student Photo" style="width: 70px; height: 70px; float: left; margin-right: 10px;">
            <p><strong>Name:</strong> {{ $student->name }}</p>
            <p><strong>School ID:</strong> {{ $student->unique_id }}</p>
            <p><strong>LIN:</strong> {{ $student->lin }}</p>
            <p><strong>Issued:</strong> {{ $issue_date }}</p>
            <p><strong>Expires:</strong> {{ $expiry_date }}</p>
        </div>
        <div style="position: absolute; bottom: 10px; right: 10px;">
            <img src="data:image/png;base64,{{ $qrCode }}" alt="QR Code">
        </div>
    </div>
</body>
</html>
