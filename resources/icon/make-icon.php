<?php

// Generates public/icon.png (1024×1024, opaque) used by NativePHP for the Android/iOS app icon.
// Placeholder: navy background, white sheet with a table and a green check. Replace with the school logo if wanted.
// Run: php resources/icon/make-icon.php

$size = 1024;
$img = imagecreatetruecolor($size, $size);
imageantialias($img, true);

$navy = imagecolorallocate($img, 0x1F, 0x4E, 0x78);
$white = imagecolorallocate($img, 0xFF, 0xFF, 0xFF);
$light = imagecolorallocate($img, 0xDB, 0xE7, 0xF2);
$green = imagecolorallocate($img, 0x16, 0xA3, 0x4A);

imagefilledrectangle($img, 0, 0, $size, $size, $navy);

// Rounded sheet
$x1 = 262;
$y1 = 182;
$x2 = 762;
$y2 = 842;
$r = 48;
imagefilledrectangle($img, $x1 + $r, $y1, $x2 - $r, $y2, $white);
imagefilledrectangle($img, $x1, $y1 + $r, $x2, $y2 - $r, $white);
foreach ([[$x1 + $r, $y1 + $r], [$x2 - $r, $y1 + $r], [$x1 + $r, $y2 - $r], [$x2 - $r, $y2 - $r]] as [$cx, $cy]) {
    imagefilledellipse($img, $cx, $cy, $r * 2, $r * 2, $white);
}

// Table: header row + grid
$tx1 = 322;
$tx2 = 702;
$ty1 = 262;
imagefilledrectangle($img, $tx1, $ty1, $tx2, $ty1 + 80, $navy);
imagesetthickness($img, 10);
for ($row = 1; $row <= 5; $row++) {
    $y = $ty1 + 80 + $row * 80;
    imageline($img, $tx1, $y, $tx2, $y, $light);
}
foreach ([450, 575] as $x) {
    imageline($img, $x, $ty1 + 80, $x, $ty1 + 480, $light);
}

// Check badge
imagefilledellipse($img, 742, 802, 260, 260, $white);
imagefilledellipse($img, 742, 802, 220, 220, $green);
// Check mark as a filled polygon (GD's antialiasing breaks thick lines).
imageantialias($img, false);
imagefilledpolygon($img, [672, 806, 696, 782, 728, 814, 790, 746, 814, 770, 728, 862], $white);

imagepng($img, __DIR__.'/../../public/icon.png');
echo "public/icon.png written\n";
