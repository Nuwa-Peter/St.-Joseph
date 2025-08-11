<!DOCTYPE html>
<html>
<head>
    <title>Students List</title>
    <style>
        body { font-family: sans-serif; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 8px; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <h1>Students List</h1>
    <table>
        <thead>
            <tr>
                <th>Student ID</th>
                <th>First Name</th>
                <th>Last Name</th>
                <th>Other Name</th>
                <th>LIN</th>
                <th>Email</th>
                <th>Gender</th>
            </tr>
        </thead>
        <tbody>
            @foreach($students as $student)
                <tr>
                    <td>{{ $student->unique_id }}</td>
                    <td>{{ $student->first_name }}</td>
                    <td>{{ $student->last_name }}</td>
                    <td>{{ $student->other_name }}</td>
                    <td>{{ $student->lin }}</td>
                    <td>{{ $student->email }}</td>
                    <td>{{ $student->gender }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
