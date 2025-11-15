<?php

/**
 * Default email template
 * Variables available:
 * - $title (optional)
 * - $content (required)
 */
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($title) ? htmlspecialchars($title) : 'Email Notification'; ?></title>
</head>

<body style="margin: 0; padding: 20px; font-family: Arial, sans-serif; line-height: 1.6;">
    <div
        style="max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 5px; padding: 20px; box-shadow: 0 0 10px rgba(0,0,0,0.1);">
        <h1 style="color: #333; margin-bottom: 20px;">
            <?php echo isset($title) ? htmlspecialchars($title) : 'Email Notification'; ?></h1>
        <div style="color: #666;">
            <?php echo $content; ?>
        </div>
        <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee; color: #999; font-size: 12px;">
            <p>This is an automated email from the ERP System.</p>
        </div>
    </div>
</body>

</html>