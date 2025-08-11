<!DOCTYPE html>
<html>
<head>
    <title>Report Card</title>
    <style>
        /* Add CSS for the report card here */
    </style>
</head>
<body>
    <div class="report-card">
        <header>
            <img src="{{ public_path('images/logo.png') }}" alt="School Logo" class="logo">
            <h1>St. Joseph's Vocational SS Nyamityobora</h1>
            <h2>Student Report Card</h2>
        </header>

        <section class="student-details">
            <div class="photo">
                @if($student->photo)
                    <img src="{{ public_path('storage/' . $student->photo) }}" alt="Student Photo">
                @endif
            </div>
            <div class="info">
                <p><strong>Name:</strong> {{ $student->name }}</p>
                <p><strong>Class:</strong> {{ $stream->classLevel->name }} {{ $stream->name }}</p>
                <p><strong>Student ID:</strong> {{ $student->unique_id }}</p>
                <p><strong>LIN:</strong> {{ $student->lin }}</p>
            </div>
        </section>

        <section class="marks-table">
            <table>
                <thead>
                    <tr>
                        <th>Subject</th>
                        <th>Paper</th>
                        <th>Score</th>
                    </tr>
                </thead>
                <tbody>
                    {{-- Loop through subjects and papers here --}}
                </tbody>
            </table>
        </section>

        <footer>
            {{-- Add footer content here --}}
        </footer>
    </div>
</body>
</html>
