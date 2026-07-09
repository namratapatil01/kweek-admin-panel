<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Password Reset</title>
</head>
<body>
    <p>Hello {{ $user->firstName ?? 'Worker' }},</p>

    <p>You requested a password reset for your KWEEK worker account.</p>

    <p>Use the token below in the app to reset your password:</p>

    <p><strong>{{ $token }}</strong></p>

    <p>This token expires in {{ config('auth.passwords.app_users.expire', 60) }} minutes.</p>

    <p>If you did not request a password reset, you can ignore this email.</p>
</body>
</html>
