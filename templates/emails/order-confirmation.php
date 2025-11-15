<?php

/**
 * Order confirmation email template
 * Variables required:
 * - $orderNumber (string) - Order reference number
 * - $customerName (string) - Name of the customer
 * - $orderDate (string) - Date of the order
 * - $items (array) - Array of order items with properties: name, quantity, unit_price, total
 * - $subtotal (float) - Order subtotal
 * - $tax (float) - Tax amount
 * - $shipping (float) - Shipping cost
 * - $total (float) - Total order amount
 * - $shippingAddress (array) - Shipping address details
 * - $paymentMethod (string) - Payment method used
 * - $companyName (string) - Company name
 * - $companyLogo (string) - URL to company logo (optional)
 * - $supportEmail (string) - Support email address
 * - $supportPhone (string) - Support phone number
 */
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation - #<?php echo htmlspecialchars($orderNumber); ?></title>
</head>

<body
    style="margin: 0; padding: 0; font-family: Arial, sans-serif; line-height: 1.6; color: #333333; background-color: #f4f4f4;">
    <div
        style="max-width: 600px; margin: 0 auto; background-color: #ffffff; padding: 20px; border-radius: 5px; box-shadow: 0 0 10px rgba(0,0,0,0.1);">
        <!-- Header -->
        <div style="text-align: center; padding: 20px 0; border-bottom: 2px solid #eeeeee;">
            <?php if (isset($companyLogo) && !empty($companyLogo)): ?>
                <img src="<?php echo htmlspecialchars($companyLogo); ?>" alt="<?php echo htmlspecialchars($companyName); ?>"
                    style="max-height: 70px; margin-bottom: 10px;">
            <?php endif; ?>
            <h1 style="color: #2c3e50; margin: 0;">Order Confirmation</h1>
            <p style="color: #7f8c8d; margin: 5px 0;">Order #<?php echo htmlspecialchars($orderNumber); ?></p>
        </div>

        <!-- Customer Info -->
        <div style="padding: 20px 0;">
            <p>Dear <?php echo htmlspecialchars($customerName); ?>,</p>
            <p>Thank you for your order! We're pleased to confirm that we've received your order and it's being
                processed.</p>
        </div>

        <!-- Payment Status -->
        <div style="margin-bottom: 20px; padding: 15px; border-radius: 5px; 
            <?php
            $statusColor = [
                'paid' => '#dff0d8',
                'pending' => '#fcf8e3',
                'failed' => '#f2dede',
                'partially_paid' => '#d9edf7'
            ];
            $textColor = [
                'paid' => '#3c763d',
                'pending' => '#8a6d3b',
                'failed' => '#a94442',
                'partially_paid' => '#31708f'
            ];
            $status = strtolower($paymentStatus);
            $bgColor = isset($statusColor[$status]) ? $statusColor[$status] : '#f8f9fa';
            $txtColor = isset($textColor[$status]) ? $textColor[$status] : '#333333';
            ?>
            background-color: <?php echo $bgColor; ?>;">
            <h2 style="color: <?php echo $txtColor; ?>; margin-top: 0; font-size: 18px;">Payment Status</h2>
            <p style="margin: 5px 0; color: <?php echo $txtColor; ?>;">
                Status: <strong><?php echo htmlspecialchars(ucfirst($paymentStatus)); ?></strong>
                <?php if (isset($paymentNote)): ?>
                    <br><?php echo htmlspecialchars($paymentNote); ?>
                <?php endif; ?>
            </p>
            <?php if ($status === 'pending' || $status === 'partially_paid'): ?>
                <p style="margin: 10px 0;">
                    <a href="<?php echo htmlspecialchars($paymentLink); ?>"
                        style="background-color: #3498db; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block;">
                        Complete Payment
                    </a>
                </p>
            <?php endif; ?>
        </div>

        <!-- Order Summary -->
        <div style="background-color: #f8f9fa; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
            <h2 style="color: #2c3e50; margin-top: 0; font-size: 18px;">Order Summary</h2>
            <p style="margin: 5px 0;">Order Date: <?php echo htmlspecialchars($orderDate); ?></p>
            <p style="margin: 5px 0;">Payment Method: <?php echo htmlspecialchars($paymentMethod); ?></p>
        </div>

        <!-- Order Items -->
        <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">
            <thead>
                <tr style="background-color: #f8f9fa;">
                    <th style="padding: 10px; text-align: left; border-bottom: 2px solid #eeeeee;">Item</th>
                    <th style="padding: 10px; text-align: center; border-bottom: 2px solid #eeeeee;">Qty</th>
                    <th style="padding: 10px; text-align: right; border-bottom: 2px solid #eeeeee;">Price</th>
                    <th style="padding: 10px; text-align: right; border-bottom: 2px solid #eeeeee;">Total</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $item): ?>
                    <tr>
                        <td style="padding: 10px; border-bottom: 1px solid #eeeeee;">
                            <?php echo htmlspecialchars($item['name']); ?></td>
                        <td style="padding: 10px; text-align: center; border-bottom: 1px solid #eeeeee;">
                            <?php echo htmlspecialchars($item['quantity']); ?></td>
                        <td style="padding: 10px; text-align: right; border-bottom: 1px solid #eeeeee;">Rs.
                            <?php echo number_format($item['unit_price'], 2); ?></td>
                        <td style="padding: 10px; text-align: right; border-bottom: 1px solid #eeeeee;">Rs.
                            <?php echo number_format($item['total'], 2); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3" style="padding: 10px; text-align: right;">Subtotal:</td>
                    <td style="padding: 10px; text-align: right;">Rs. <?php echo number_format($subtotal, 2); ?></td>
                </tr>
                <?php if ($tax > 0): ?>
                    <tr>
                        <td colspan="3" style="padding: 10px; text-align: right;">Tax:</td>
                        <td style="padding: 10px; text-align: right;">Rs. <?php echo number_format($tax, 2); ?></td>
                    </tr>
                <?php endif; ?>
                <?php if ($shipping > 0): ?>
                    <tr>
                        <td colspan="3" style="padding: 10px; text-align: right;">Shipping:</td>
                        <td style="padding: 10px; text-align: right;">Rs. <?php echo number_format($shipping, 2); ?></td>
                    </tr>
                <?php endif; ?>
                <tr>
                    <td colspan="3" style="padding: 10px; text-align: right; font-weight: bold;">Total:</td>
                    <td style="padding: 10px; text-align: right; font-weight: bold;">Rs.
                        <?php echo number_format($total, 2); ?></td>
                </tr>
            </tfoot>
        </table>

        <!-- Shipping Address -->
        <div style="background-color: #f8f9fa; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
            <h2 style="color: #2c3e50; margin-top: 0; font-size: 18px;">Shipping Address</h2>
            <p style="margin: 5px 0;"><?php echo htmlspecialchars($shippingAddress['name']); ?></p>
            <p style="margin: 5px 0;"><?php echo htmlspecialchars($shippingAddress['street']); ?></p>
            <p style="margin: 5px 0;">
                <?php echo htmlspecialchars($shippingAddress['city']); ?>,
                <?php echo htmlspecialchars($shippingAddress['province']); ?>
            </p>
            <p style="margin: 5px 0;"><?php echo htmlspecialchars($shippingAddress['postal_code']); ?></p>
            <p style="margin: 5px 0;"><?php echo htmlspecialchars($shippingAddress['phone']); ?></p>
        </div>

        <!-- Promotional Section -->
        <?php if (isset($promotionalContent) && !empty($promotionalContent)): ?>
            <div
                style="margin: 30px 0; padding: 20px; background-color: #fff8dc; border-radius: 5px; border: 1px dashed #deb887;">
                <h2 style="color: #8b4513; margin-top: 0; font-size: 18px;">Special Offers</h2>
                <div style="margin-top: 15px;">
                    <?php if (isset($promotionalImage)): ?>
                        <img src="<?php echo htmlspecialchars($promotionalImage); ?>" alt="Special Offer"
                            style="max-width: 100%; height: auto; margin-bottom: 15px;">
                    <?php endif; ?>
                    <div style="color: #444;">
                        <?php echo $promotionalContent; ?>
                    </div>
                    <?php if (isset($promotionalButtonLink) && isset($promotionalButtonText)): ?>
                        <p style="margin-top: 15px;">
                            <a href="<?php echo htmlspecialchars($promotionalButtonLink); ?>"
                                style="background-color: #e67e22; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block;">
                                <?php echo htmlspecialchars($promotionalButtonText); ?>
                            </a>
                        </p>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Footer -->
        <div style="border-top: 2px solid #eeeeee; padding-top: 20px; text-align: center;">
            <p style="margin: 5px 0;">Need help? Contact our support team:</p>
            <p style="margin: 5px 0;">
                Email: <a href="mailto:<?php echo htmlspecialchars($supportEmail); ?>"
                    style="color: #3498db;"><?php echo htmlspecialchars($supportEmail); ?></a>
            </p>
            <p style="margin: 5px 0;">
                Phone: <?php echo htmlspecialchars($supportPhone); ?>
            </p>
            <p style="margin: 20px 0 0; font-size: 12px; color: #7f8c8d;">
                &copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars($companyName); ?>. All rights reserved.
            </p>
        </div>
    </div>
</body>

</html>