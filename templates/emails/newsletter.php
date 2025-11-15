<?php

/**
 * Newsletter email template
 * Variables required:
 * - $heroTitle (string) - Main hero title (e.g., "Didn't get your Tea Jar yet?")
 * - $heroSubtitle (string) - Hero subtitle
 * - $mainImage (string) - URL to main hero image
 * - $tagline (string) - Main tagline text
 * - $description (string) - Description text
 * - $featuredProducts (array) - Array of featured products with properties: name, image, oldPrice, newPrice
 * - $productImages (array) - Array of product thumbnail images
 * - $companyName (string) - Company name
 * - $companyLogo (string) - URL to company logo
 * - $socialLinks (array) - Array of social media links
 * - $year (string) - Current year
 */
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($heroTitle); ?></title>
</head>

<body style="margin: 0; padding: 0; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; line-height: 1.6; background-color: #f4f4f4;">
    <div style="max-width: 600px; margin: 0 auto; background-color: #ffffff;">
        <!-- Header Logo -->
        <div style="text-align: center; padding: 20px;">
            <img src="<?php echo htmlspecialchars($companyLogo); ?>" alt="<?php echo htmlspecialchars($companyName); ?>" style="max-height: 50px;">
        </div>

        <!-- Hero Section -->
        <div style="position: relative; text-align: center; color: white;">
            <img src="<?php echo htmlspecialchars($mainImage); ?>" alt="Hero Image" style="width: 100%; height: auto;">
            <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 80%;">
                <h1 style="font-size: 32px; margin: 0; text-shadow: 2px 2px 4px rgba(0,0,0,0.5);">
                    <?php echo htmlspecialchars($heroTitle); ?>
                </h1>
                <p style="font-size: 18px; margin: 10px 0; text-shadow: 1px 1px 2px rgba(0,0,0,0.5);">
                    <?php echo htmlspecialchars($heroSubtitle); ?>
                </p>
            </div>
        </div>

        <!-- Tagline Section -->
        <div style="text-align: center; padding: 40px 20px; background-color: #ffffff;">
            <h2 style="font-size: 28px; color: #333; margin: 0;">
                <?php echo htmlspecialchars($tagline); ?>
            </h2>
            <p style="color: #666; margin: 15px 0;">
                <?php echo htmlspecialchars($description); ?>
            </p>
            <a href="<?php echo htmlspecialchars($shopNowLink); ?>" style="display: inline-block; background-color: #333; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; margin-top: 20px;">
                SHOP NOW
            </a>
        </div>

        <!-- Featured Products Section -->
        <div style="padding: 20px; background-color: #002121;">
            <h2 style="color: white; text-align: center; margin: 0 0 20px 0;">YOU MAY ALSO LIKE</h2>
            <?php foreach ($featuredProducts as $product): ?>
                <div style="margin-bottom: 20px; display: flex; align-items: center; padding: 10px; border-bottom: 1px solid rgba(255,255,255,0.1);">
                    <img src="<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" style="width: 100px; height: 100px; object-fit: cover;">
                    <div style="margin-left: 15px; flex-grow: 1;">
                        <h3 style="color: #FFD700; margin: 0;"><?php echo htmlspecialchars($product['name']); ?></h3>
                        <div style="color: white; margin-top: 5px;">
                            <span style="text-decoration: line-through; color: #999;">LKR <?php echo number_format($product['oldPrice'], 2); ?></span>
                            <span style="color: #ff4444; margin-left: 10px;">LKR <?php echo number_format($product['newPrice'], 2); ?></span>
                        </div>
                    </div>
                    <a href="<?php echo htmlspecialchars($product['link']); ?>" style="background-color: transparent; color: white; padding: 8px 20px; text-decoration: none; border: 1px solid white; border-radius: 5px;">
                        SHOP NOW
                    </a>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Product Thumbnails -->
        <div style="display: flex; flex-wrap: wrap; justify-content: center; padding: 20px; background-color: white;">
            <?php foreach ($productImages as $image): ?>
                <div style="width: 33.33%; padding: 5px; box-sizing: border-box;">
                    <img src="<?php echo htmlspecialchars($image['url']); ?>" alt="<?php echo htmlspecialchars($image['name']); ?>" style="width: 100%; height: auto;">
                    <a href="<?php echo htmlspecialchars($image['link']); ?>" style="display: block; text-align: center; padding: 5px; text-decoration: none; color: #333; font-size: 12px;">
                        SHOP NOW
                    </a>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Footer -->
        <div style="text-align: center; padding: 40px 20px; background-color: #f8f8f8;">
            <img src="<?php echo htmlspecialchars($companyLogo); ?>" alt="<?php echo htmlspecialchars($companyName); ?>" style="max-height: 40px; margin-bottom: 20px;">

            <p style="color: #666; margin: 20px 0; font-style: italic;">
                Wishing you a year filled with warmth, kindness, and unforgettable moments.
                <br>From our <?php echo htmlspecialchars($companyName); ?> family to yours.
            </p>

            <p style="color: #666; margin: 20px 0;">
                Need help? Our experts are here to assist you.
                <br><a href="<?php echo htmlspecialchars($contactLink); ?>" style="color: #333; text-decoration: underline;">Contact us!</a>
            </p>

            <div style="margin: 20px 0;">
                <p style="color: #333; margin-bottom: 10px;">Follow us on social media:</p>
                <div style="display: flex; justify-content: center; gap: 15px;">
                    <?php foreach ($socialLinks as $social): ?>
                        <a href="<?php echo htmlspecialchars($social['link']); ?>" style="color: #333; text-decoration: none;">
                            <img src="<?php echo htmlspecialchars($social['icon']); ?>" alt="<?php echo htmlspecialchars($social['name']); ?>" style="width: 24px; height: 24px;">
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <p style="color: #999; font-size: 12px; margin-top: 20px;">
                © <?php echo date('Y'); ?> <?php echo htmlspecialchars($companyName); ?>. All rights reserved.
            </p>
        </div>
    </div>
</body>

</html>