<?php

declare(strict_types=1);

namespace App\Utils;

use App\Core\Config;

class Mailer
{
    /**
     * Send an email using SMTP configured in .env
     * 
     * @return array ['success' => bool, 'message' => string]
     */
    public static function send(string $toEmail, string $toName, string $subject, string $htmlBody, string $textBody = ''): array
    {
        $host = Config::get('MAIL_HOST', '');
        $port = (int) Config::get('MAIL_PORT', 587);
        $username = Config::get('MAIL_USERNAME', '');
        $password = Config::get('MAIL_PASSWORD', '');
        $encryption = strtolower((string) Config::get('MAIL_ENCRYPTION', 'tls'));
        $fromEmail = Config::get('MAIL_FROM_ADDRESS', 'notifications@classaid.com');
        $fromName = Config::get('MAIL_FROM_NAME', 'Class Aid Suite');

        if (empty($host) || empty($fromEmail)) {
            // SMTP not configured yet - simulate success in dev environment
            error_log("[Mailer] SMTP not configured. Simulated dispatch to {$toEmail}: {$subject}");
            return [
                'success' => true,
                'simulated' => true,
                'message' => 'Email simulated (SMTP credentials not configured in .env).',
            ];
        }

        if (empty($textBody)) {
            $textBody = strip_tags(str_replace(['<br>', '<br/>', '<p>', '</p>'], ["\n", "\n", "\n", "\n"], $htmlBody));
        }

        try {
            $socketHost = ($encryption === 'ssl') ? "ssl://{$host}" : $host;
            $timeout = 10;
            
            $socket = @fsockopen($socketHost, $port, $errno, $errstr, $timeout);
            if (!$socket) {
                throw new \RuntimeException("Could not connect to SMTP server {$host}:{$port} - {$errstr} ({$errno})");
            }

            self::readResponse($socket, 220);
            self::sendCommand($socket, "EHLO " . gethostname(), 250);

            if ($encryption === 'tls') {
                self::sendCommand($socket, "STARTTLS", 220);
                if (!stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                    throw new \RuntimeException("Failed to establish TLS encryption with SMTP server.");
                }
                self::sendCommand($socket, "EHLO " . gethostname(), 250);
            }

            if (!empty($username) && !empty($password)) {
                self::sendCommand($socket, "AUTH LOGIN", 334);
                self::sendCommand($socket, base64_encode($username), 334);
                self::sendCommand($socket, base64_encode($password), 235);
            }

            self::sendCommand($socket, "MAIL FROM: <{$fromEmail}>", 250);
            self::sendCommand($socket, "RCPT TO: <{$toEmail}>", 250);
            self::sendCommand($socket, "DATA", 354);

            $boundary = "----=_ClassAid_" . md5(uniqid((string) time(), true));

            $headers = [
                "From: {$fromName} <{$fromEmail}>",
                "To: {$toName} <{$toEmail}>",
                "Subject: {$subject}",
                "Date: " . date('r'),
                "MIME-Version: 1.0",
                "Content-Type: multipart/alternative; boundary=\"{$boundary}\"",
                "X-Mailer: ClassAid-PHP8.4-Mailer",
            ];

            $message = implode("\r\n", $headers) . "\r\n\r\n";
            $message .= "--{$boundary}\r\n";
            $message .= "Content-Type: text/plain; charset=UTF-8\r\n";
            $message .= "Content-Transfer-Encoding: base64\r\n\r\n";
            $message .= chunk_split(base64_encode($textBody)) . "\r\n";

            $message .= "--{$boundary}\r\n";
            $message .= "Content-Type: text/html; charset=UTF-8\r\n";
            $message .= "Content-Transfer-Encoding: base64\r\n\r\n";
            $message .= chunk_split(base64_encode($htmlBody)) . "\r\n";
            $message .= "--{$boundary}--\r\n";
            $message .= ".";

            self::sendCommand($socket, $message, 250);
            self::sendCommand($socket, "QUIT", 221);
            fclose($socket);

            return [
                'success' => true,
                'simulated' => false,
                'message' => "Email sent successfully to {$toEmail}.",
            ];
        } catch (\Throwable $e) {
            error_log("[Mailer Error] " . $e->getMessage());
            return [
                'success' => false,
                'simulated' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    private static function sendCommand($socket, string $cmd, int $expectedCode): void
    {
        fwrite($socket, $cmd . "\r\n");
        self::readResponse($socket, $expectedCode);
    }

    private static function readResponse($socket, int $expectedCode): string
    {
        $response = "";
        while ($line = fgets($socket, 515)) {
            $response .= $line;
            if (substr($line, 3, 1) === ' ') {
                break;
            }
        }

        $code = (int) substr($response, 0, 3);
        if ($code !== $expectedCode) {
            throw new \RuntimeException("SMTP Error [Expected {$expectedCode}, Got {$code}]: " . trim($response));
        }

        return $response;
    }

    /**
     * Test SMTP Connection settings.
     */
    public static function testConnection(): array
    {
        $testEmail = Config::get('MAIL_FROM_ADDRESS', 'test@example.com');
        return self::send($testEmail, 'Admin Test', 'Class Aid SMTP Test Dispatch', '<p>Your Class Aid SMTP server is operational!</p>');
    }

    /**
     * Send Invoice Email to Customer
     */
    public static function sendInvoiceEmail(array $invoice, array $user): array
    {
        $appUrl = rtrim((string) Config::get('APP_URL', 'http://localhost:8000'), '/');
        $fromEmail = (string) Config::get('MAIL_FROM_ADDRESS', 'notifications@classaid.com');
        $invoiceUrl = "{$appUrl}/subscription/invoice/{$invoice['id']}";
        $isPaid = ($invoice['status'] === 'paid');
        $statusLabel = $isPaid ? 'PAID' : 'PENDING VERIFICATION';
        $statusColor = $isPaid ? '#10b981' : '#f59e0b';

        $subject = "Class Aid Invoice #{$invoice['invoice_number']} - {$statusLabel}";

        $html = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Class Aid Invoice</title>
</head>
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #0b0f19; color: #f1f5f9; margin: 0; padding: 24px;">
    <div style="max-width: 600px; margin: 0 auto; background: #1e293b; border-radius: 16px; border: 1px solid #334155; overflow: hidden; box-shadow: 0 10px 25px rgba(0,0,0,0.5);">
        
        <!-- Header -->
        <div style="background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%); padding: 32px 24px; text-align: center; color: #ffffff;">
            <h1 style="margin: 0; font-size: 24px; font-weight: 800; letter-spacing: -0.5px;">Class Aid Suite</h1>
            <p style="margin: 4px 0 0 0; font-size: 13px; opacity: 0.9;">Educational Workspace Billing Receipt</p>
        </div>

        <!-- Body -->
        <div style="padding: 28px 24px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; border-bottom: 1px solid #334155; padding-bottom: 16px;">
                <div>
                    <span style="font-size: 11px; text-transform: uppercase; color: #94a3b8; font-weight: 700;">Invoice Number</span>
                    <div style="font-size: 18px; font-weight: 800; color: #ffffff; font-family: monospace;">{$invoice['invoice_number']}</div>
                </div>
                <div style="text-align: right;">
                    <span style="display: inline-block; padding: 4px 12px; border-radius: 999px; font-size: 11px; font-weight: 800; background: {$statusColor}22; color: {$statusColor}; border: 1px solid {$statusColor}55;">
                        {$statusLabel}
                    </span>
                </div>
            </div>

            <p style="font-size: 14px; line-height: 1.5; color: #cbd5e1; margin-bottom: 20px;">
                Hello <strong>{$user['name']}</strong>,<br>
                Thank you for choosing Class Aid! Here are the details of your educator workspace subscription.
            </p>

            <!-- Line Items Table -->
            <table style="width: 100%; border-collapse: collapse; margin-bottom: 24px; font-size: 13px;">
                <thead>
                    <tr style="border-bottom: 1px solid #334155; color: #94a3b8; text-align: left; font-size: 11px; text-transform: uppercase;">
                        <th style="padding: 8px 0;">Description</th>
                        <th style="padding: 8px 0; text-align: center;">Billing Cycle</th>
                        <th style="padding: 8px 0; text-align: right;">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <tr style="border-bottom: 1px solid #334155;">
                        <td style="padding: 12px 0; color: #ffffff; font-weight: 600;">{$invoice['plan_name']}</td>
                        <td style="padding: 12px 0; text-align: center; color: #94a3b8; text-transform: uppercase; font-family: monospace; font-size: 11px;">{$invoice['billing_cycle']}</td>
                        <td style="padding: 12px 0; text-align: right; color: #ffffff; font-weight: 700; font-family: monospace;">\${$invoice['amount']}</td>
                    </tr>
                    <tr>
                        <td colspan="2" style="padding: 16px 0; font-weight: 700; color: #ffffff; text-align: right;">Total Due:</td>
                        <td style="padding: 16px 0; font-weight: 900; color: #818cf8; text-align: right; font-size: 18px; font-family: monospace;">\${$invoice['amount']}</td>
                    </tr>
                </tbody>
            </table>

            <!-- Payment Details Box -->
            <div style="background: #0f172a; border-radius: 12px; padding: 16px; margin-bottom: 24px; border: 1px solid #334155; font-size: 12px; color: #94a3b8;">
                <div style="margin-bottom: 6px;"><strong style="color: #ffffff;">Payment Method:</strong> {$invoice['payment_method']}</div>
                <div style="margin-bottom: 6px;"><strong style="color: #ffffff;">Reference / TRX:</strong> <span style="font-family: monospace; color: #818cf8;">{$invoice['payment_reference']}</span></div>
                <div><strong style="color: #ffffff;">Issue Date:</strong> {$invoice['issue_date']}</div>
            </div>

            <!-- Call to action -->
            <div style="text-align: center; margin-top: 24px;">
                <a href="{$invoiceUrl}" style="display: inline-block; background: #4f46e5; color: #ffffff; text-decoration: none; font-weight: 700; font-size: 13px; padding: 12px 28px; border-radius: 10px; box-shadow: 0 4px 12px rgba(79, 70, 229, 0.4);">
                    View & Print Official PDF Invoice &rarr;
                </a>
            </div>
        </div>

        <!-- Footer -->
        <div style="background: #0f172a; padding: 16px 24px; text-align: center; font-size: 11px; color: #64748b; border-top: 1px solid #334155;">
            Class Aid &bull; Multi-Class Educational Assistant &bull; Need help? Contact {$fromEmail}
        </div>
    </div>
</body>
</html>
HTML;

        return self::send($user['email'], $user['name'], $subject, $html);
    }

    public static function sendPasswordResetEmail(array $user, string $resetUrl): array
    {
        $fromEmail = Config::get('MAIL_FROM_ADDRESS', 'notifications@classaid.com');
        $subject = "Password Reset Request - Class Aid";

        $html = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reset Your Password</title>
</head>
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #0b0f19; color: #f1f5f9; margin: 0; padding: 24px;">
    <div style="max-width: 560px; margin: 0 auto; background: #1e293b; border-radius: 16px; border: 1px solid #334155; overflow: hidden; box-shadow: 0 10px 25px rgba(0,0,0,0.5);">
        
        <!-- Header -->
        <div style="background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%); padding: 32px 24px; text-align: center; color: #ffffff;">
            <h1 style="margin: 0; font-size: 22px; font-weight: 800; letter-spacing: -0.5px;">Class Aid Suite</h1>
            <p style="margin: 4px 0 0 0; font-size: 13px; opacity: 0.9;">Secure Account Access Recovery</p>
        </div>

        <!-- Body -->
        <div style="padding: 28px 24px;">
            <p style="font-size: 14px; line-height: 1.6; color: #cbd5e1; margin-top: 0;">
                Hello <strong>{$user['name']}</strong>,<br>
                A password reset request was initiated for your Class Aid educator account by the administrator.
            </p>

            <p style="font-size: 13px; line-height: 1.5; color: #94a3b8;">
                Click the button below to set a new password for your account:
            </p>

            <!-- Call to Action Button -->
            <div style="text-align: center; margin: 28px 0;">
                <a href="{$resetUrl}" style="display: inline-block; background: #4f46e5; color: #ffffff; text-decoration: none; font-weight: 700; font-size: 14px; padding: 14px 32px; border-radius: 10px; box-shadow: 0 4px 14px rgba(79, 70, 229, 0.4);">
                    Set New Password &rarr;
                </a>
            </div>

            <!-- Monospace URL box -->
            <div style="background: #0f172a; border-radius: 10px; padding: 12px; margin-bottom: 20px; border: 1px solid #334155; font-size: 11px; word-break: break-all; color: #818cf8; font-family: monospace;">
                {$resetUrl}
            </div>

            <p style="font-size: 11px; color: #64748b; margin-bottom: 0;">
                This link will expire in 24 hours. If you did not request this, please notify your school administrator immediately.
            </p>
        </div>

        <!-- Footer -->
        <div style="background: #0f172a; padding: 16px 24px; text-align: center; font-size: 11px; color: #64748b; border-top: 1px solid #334155;">
            Class Aid &bull; Multi-Class Educational Assistant &bull; Support: {$fromEmail}
        </div>
    </div>
</body>
</html>
HTML;

        return self::send($user['email'], $user['name'], $subject, $html);
    }
}
