<!DOCTYPE html>
<html lang="en">
<head><meta charset="utf-8"></head>
<body>
    <p>Hello{{ $invitation->name ? ', ' . $invitation->name : '' }},</p>
    <p>You have been invited to join <strong>{{ $invitation->company->name }}</strong> on URL Shortener.</p>
    <p>Click the link below to set your password and get started:</p>
    <p><a href="{{ $acceptUrl }}">{{ $acceptUrl }}</a></p>
    <p>This invitation expires on {{ $invitation->expires_at->format('d M Y, H:i') }}.</p>
    <p>If you did not expect this invitation, you can safely ignore this email.</p>
</body>
</html>

