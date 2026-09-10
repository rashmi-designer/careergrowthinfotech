<?php
declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/config.php';

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

function build_password_reset_email_html(string $resetUrl): string
{
    $brandName = htmlspecialchars(project_config_get('MAIL_FROM_NAME', 'Career Grow Infotech'), ENT_QUOTES, 'UTF-8');
    $safeResetUrl = htmlspecialchars($resetUrl, ENT_QUOTES, 'UTF-8');

    return sprintf(
        '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Your Password</title>
</head>
<body style="margin:0; padding:0; background-color:#f4f7fb; font-family:Arial, Helvetica, sans-serif; color:#1f2937;">
    <table role="presentation" width="100%%" cellpadding="0" cellspacing="0" style="background-color:#f4f7fb; padding:24px 0;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%%" max-width="600" cellpadding="0" cellspacing="0" style="background:#ffffff; border:1px solid #e5e7eb; border-radius:12px; overflow:hidden;">
                    <tr>
                        <td style="background:#0f172a; padding:24px 32px; color:#ffffff; font-size:24px; font-weight:bold;">
                            %s
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:32px;">
                            <p style="margin:0 0 16px; font-size:16px; line-height:1.6;">A password reset request was received for your account.</p>
                            <p style="margin:0 0 24px; font-size:16px; line-height:1.6;">Click the button below to choose a new password.</p>
                            <p style="margin:0 0 24px; text-align:center;">
                                <a href="%s" style="display:inline-block; background:#2563eb; color:#ffffff; text-decoration:none; padding:14px 26px; border-radius:8px; font-weight:bold;">
                                    Reset Password
                                </a>
                            </p>
                            <p style="margin:0 0 12px; font-size:14px; line-height:1.6; color:#4b5563;">This password reset link will expire in approximately 1 hour.</p>
                            <p style="margin:0; font-size:14px; line-height:1.6; color:#4b5563;">If you did not request this reset, you can safely ignore this email.</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>',
        $brandName,
        $safeResetUrl
    );
}

function build_password_reset_email_text(string $resetUrl): string
{
    return "Reset Your Password\n\nA password reset request was received for your account.\n\nClick the link below to choose a new password:\n\n" . $resetUrl . "\n\nThis password reset link will expire in approximately 1 hour.\nIf you did not request this reset, you can safely ignore this email.\n";
}

function send_password_reset_email(string $toEmail, string $toName, string $resetUrl): void
{
    $toEmail = trim($toEmail);
    $toName = trim($toName);
    $resetUrl = trim($resetUrl);

    if ($toEmail === '' || !filter_var($toEmail, FILTER_VALIDATE_EMAIL)) {
        throw new RuntimeException('Invalid recipient email address.');
    }

    if ($resetUrl === '') {
        throw new RuntimeException('Password reset URL is missing.');
    }

    $smtpHost = project_config_get('MAIL_HOST');
    $smtpPort = (int) project_config_get('MAIL_PORT', 587);
    $smtpUsername = project_config_get('MAIL_USERNAME');
    $smtpPassword = project_config_get('MAIL_PASSWORD');
    $smtpEncryption = strtolower((string) project_config_get('MAIL_ENCRYPTION', 'tls'));
    $fromAddress = project_config_get('MAIL_FROM_ADDRESS', 'ankitkumarbup@gmail.com');
    $fromName = project_config_get('MAIL_FROM_NAME', 'Career Grow Infotech');
    $replyToAddress = project_config_get('MAIL_REPLY_TO_ADDRESS', '');
    $replyToName = (string) project_config_get('MAIL_REPLY_TO_NAME', '');

    if ($smtpHost === null || $smtpHost === '' || $smtpUsername === null || $smtpUsername === '' || $smtpPassword === null || $smtpPassword === '') {
        throw new RuntimeException('Mailer configuration is incomplete.');
    }

    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host = $smtpHost;
    $mail->SMTPAuth = true;
    $mail->Username = $smtpUsername;
    $mail->Password = $smtpPassword;
    $mail->Port = $smtpPort;
    $mail->CharSet = 'UTF-8';

    if ($smtpEncryption === 'tls' || $smtpEncryption === 'ssl') {
        $mail->SMTPSecure = $smtpEncryption;
    } else {
        $mail->SMTPSecure = false;
    }

    $mail->setFrom($fromAddress, $fromName);

    if ($replyToAddress !== '') {
        $mail->addReplyTo($replyToAddress, $replyToName);
    }

    $mail->addAddress($toEmail, $toName);
    $mail->isHTML(true);
    $mail->Subject = 'Reset Your Password';
    $mail->Body = build_password_reset_email_html($resetUrl);
    $mail->AltBody = build_password_reset_email_text($resetUrl);

    try {
        $mail->send();
    } catch (Exception $e) {
        error_log('Password reset email failed: ' . $e->getMessage());
        throw new RuntimeException('Unable to send password reset email at this time.');
    }
}
