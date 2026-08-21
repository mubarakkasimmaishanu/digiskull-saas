<?php
// Generate PNG icons from PHP GD
$sizes = [
    'public/global_assets/icons/favicon-16x16.png' => 16,
    'public/global_assets/icons/favicon-32x32.png' => 32,
    'public/global_assets/icons/apple-touch-icon.png' => 180,
    'public/global_assets/images/logo.png' => 256,
];

if (!is_dir('public/global_assets/icons')) {
    mkdir('public/global_assets/icons', 0755, true);
}

foreach ($sizes as $path => $size) {
    $img = imagecreatetruecolor($size, $size);
    imagealphablending($img, false);
    imagesavealpha($img, true);

    // Dark rounded background
    $transparent = imagecolorallocatealpha($img, 0, 0, 0, 127);
    imagefill($img, 0, 0, $transparent);

    $darkBg = imagecolorallocate($img, 11, 15, 25);
    $navy = imagecolorallocate($img, 29, 78, 216);
    $cyan = imagecolorallocate($img, 56, 189, 248);

    // Draw background rounded box
    $r = (int)($size * 0.18);
    imagefilledrectangle($img, $r, 0, $size - $r, $size, $darkBg);
    imagefilledrectangle($img, 0, $r, $size, $size - $r, $darkBg);
    imagefilledellipse($img, $r, $r, $r * 2, $r * 2, $darkBg);
    imagefilledellipse($img, $size - $r, $r, $r * 2, $r * 2, $darkBg);
    imagefilledellipse($img, $r, $size - $r, $r * 2, $r * 2, $darkBg);
    imagefilledellipse($img, $size - $r, $size - $r, $r * 2, $r * 2, $darkBg);

    // Scale factor
    $s = $size / 100.0;

    // Draw D outer shape
    $x1 = (int)(22 * $s);
    $y1 = (int)(18 * $s);
    $w = (int)(56 * $s);
    $h = (int)(64 * $s);
    
    // Filled blue arc for D
    imagefilledrectangle($img, $x1, $y1, $x1 + (int)($w * 0.5), $y1 + $h, $navy);
    imagefilledellipse($img, $x1 + (int)($w * 0.4), $y1 + (int)($h * 0.5), (int)($w * 1.1), $h, $navy);
    
    // Cutout inner D
    $cutoutW = (int)($w * 0.5);
    $cutoutH = (int)($h * 0.55);
    imagefilledellipse($img, $x1 + (int)($w * 0.35), $y1 + (int)($h * 0.5), $cutoutW, $cutoutH, $darkBg);
    imagefilledrectangle($img, $x1 + (int)(10 * $s), $y1 + (int)(12 * $s), $x1 + (int)(22 * $s), $y1 + $h - (int)(12 * $s), $darkBg);

    // Draw 3 Cyan Pixels
    $pxSize = max(2, (int)(12 * $s));
    $px1X = (int)(22 * $s);
    $px1Y = (int)(44 * $s);
    imagefilledrectangle($img, $px1X, $px1Y, $px1X + $pxSize, $px1Y + $pxSize, $cyan);

    $px2X = (int)(38 * $s);
    $px2Y = (int)(44 * $s);
    imagefilledrectangle($img, $px2X, $px2Y, $px2X + $pxSize, $px2Y + $pxSize, $cyan);

    $px3X = (int)(22 * $s);
    $px3Y = (int)(60 * $s);
    imagefilledrectangle($img, $px3X, $px3Y, $px3X + $pxSize, $px3Y + $pxSize, $cyan);

    imagepng($img, $path);
    imagedestroy($img);
    echo "Generated $path ($size x $size)\n";
}

// Copy 32x32 PNG to favicon.ico
copy('public/global_assets/icons/favicon-32x32.png', 'public/favicon.ico');
echo "Updated public/favicon.ico\n";
