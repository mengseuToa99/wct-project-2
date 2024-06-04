<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
</head>
<body>
    <h1>Reset Your Password</h1>
    <p>Hello {{ $user->email }},</p>
    <p>Your new temporary password is: {{ $password }}</p>
    <p>Please use the following link to reset your password:</p>
    <a href="https://jomyeak.vercel.app/login">Reset Password</a>
    <p>If you did not request a password reset, please ignore this email.</p>
</body>
</html>
