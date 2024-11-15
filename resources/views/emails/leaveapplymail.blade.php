<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leave Application Details</title>
    <!-- Add your styles here -->
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 20px;
        }

        .container {
            max-width: 600px;
            margin: auto;
        }

        .header {
            text-align: center;
            font-size: 24px;
            margin-bottom: 20px;
        }

        .details {
            font-size: 16px;
            margin-bottom: 20px;
        }

        .footer {
            text-align: center;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
         <h2>Hello,</h2>
        <h3>The employee have applied for leaves, please find the details below</h3>
        </div>
        
        <div class="details">
            <p><strong>Name:</strong> {{ $data['username'] ?? "-" }}</p>
            <p><strong>Emp-ID:</strong> {{ $data['emp_id'] ?? "-" }}</p>
            <p><strong>Date From:</strong> {{ $data['leave_from'] ?? "-" }}</p>
            <p><strong>Date To:</strong> {{ $data['leave_to'] ?? "-" }}</p>
            <p><strong>Type:</strong> {{ $data['leave_duration'] ?? "-" }}</p>
            <p><strong>Type of Leave:</strong> {{ $data['leave_type'] ?? "-" }}</p>
            <p><strong>Reason:</strong> {{ $data['reason'] }}</p>
        </div>

        <div class="footer">
            <p>From: Support-KSPL-EMS</p>
        </div>
    </div>
</body>
</html>
