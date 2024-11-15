<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Attendance Status Update</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 80%;
            max-width: 600px;
            margin: 0 auto;
            background-color: #fff;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }
        h1 {
            color: #333;
        }
        p {
            color: #555;
        }
        ul {
            list-style-type: none;
            padding: 0;
        }
        ul li {
            background-color: #e9ecef;
            margin: 5px 0;
            padding: 10px;
            border-radius: 4px;
        }
        .button {
            display: inline-block;
            padding: 10px 20px;
            margin-top: 20px;
            background-color: #007bff;
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
        }
        .button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Attendance Marked</h1>
        <p>Hello {{ $data['user_name'] }},</p>
        <p>Your attendance has been regularised for {{ $data['attendance_date'] }}.</p>
        <p>Details:</p>
        <ul>
            <li><strong>Type:</strong> {{ $data['regularization_type'] }}</li>
            <li><strong>In Time:</strong> {{ $data['in_time'] }}</li>
            <li><strong>Out Time:</strong> {{ $data['out_time'] }}</li>
        </ul>
        <br>
        <p>In case of any quires please reach out the Support team.</p>
        <p>Thank you.
        <br>
        <br>
        {{ config('app.name') }}
        </p>
    </div>
</body>
</html>