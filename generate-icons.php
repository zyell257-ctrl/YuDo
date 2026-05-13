#!/usr/bin/env php
<?php
/**
 * Generate PWA icons untuk Ludo Tracker
 * Jalankan: php generate-icons.php
 * Membutuhkan ekstensi GD (biasanya sudah terinstall dengan PHP)
 */

$outputDir = __DIR__ . '/public/icons';
if (!is_dir($outputDir)) mkdir($outputDir, 0755, true);

$sizes = [192, 512];

foreach ($sizes as $size) {
    $img = imagecreatetruecolor($size, $size);

    // Background gelap (#0d0f1a)
    $bg = imagecolorallocate($img, 13, 15, 26);
    imagefill($img, 0, 0, $bg);

    // Rounded corners (simulasi dengan lingkaran di sudut)
    $radius  = (int)($size * 0.22);
    $roundBg = imagecolorallocate($img, 21, 25, 41);

    // Lingkaran tengah (latar icon)
    $cx = $cy = (int)($size / 2);
    $r  = (int)($size * 0.42);
    imagefilledellipse($img, $cx, $cy, $r * 2, $r * 2, $roundBg);

    // Teks emoji dadu (karena GD tidak support emoji, tulis teks alternatif)
    $gold = imagecolorallocate($img, 244, 196, 48);

    $fontSize = (int)($size * 0.28);
    $text     = 'LT';

    // Gunakan font built-in
    $fontFile = null; // null = pakai font GD built-in

    // Hitung posisi teks
    $bbox   = imagettfbbox($fontSize, 0, '/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf', $text) ?? null;
    if ($bbox && file_exists('/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf')) {
        $tw = $bbox[2] - $bbox[0];
        $th = $bbox[1] - $bbox[7];
        $tx = ($size - $tw) / 2;
        $ty = ($size + $th) / 2;
        imagettftext($img, $fontSize, 0, (int)$tx, (int)$ty, $gold, '/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf', $text);
    } else {
        // Fallback: imagestring (font built-in, ukuran terbatas)
        $scale = (int)($size / 128);
        imagestring($img, 5, (int)($size * 0.3), (int)($size * 0.38), 'LUDO', $gold);
        imagestring($img, 5, (int)($size * 0.28), (int)($size * 0.52), 'TRACK', $gold);
    }

    // Simpan sebagai PNG
    $filename = "{$outputDir}/icon-{$size}.png";
    imagepng($img, $filename);
    imagedestroy($img);

    echo "✓ Generated: {$filename}\n";
}

echo "\nDone! Icons saved to public/icons/\n";
echo "Note: For better icons, replace with proper PNG files (192x192 dan 512x512).\n";
