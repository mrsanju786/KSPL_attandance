<!-- leave_approval.blade.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leave Approval</title>
</head>
<body>
    <p>Dear <strong>{{ $data['employee_name'] }}</strong>,</p>

    <p>Your leave request has been <strong>{{ $data['leave_status'] }}</strong>.</p>

    <p><strong>Leave Details:</strong></p>
    <ul>
        <li><strong>Type:</strong> {{ $data['leave_type'] }}</li>
        <li><strong>Start Date:</strong> {{ $data['from_date'] }}</li>
        <li><strong>End Date:</strong> {{ $data['to_date'] }}</li>
    </ul>

    <p>If you have any questions, feel free to contact us.</p>

    <p>Thank you,</p>
    <p>Support KSPL EMS</p>
</body>
</html>
