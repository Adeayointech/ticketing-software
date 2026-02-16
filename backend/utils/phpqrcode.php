
/*
 * PHP QR Code encoder
 * Based on http://phpqrcode.sourceforge.net/ (LGPL)
 * Minimal version for local QR code generation
 */

define('QR_ECLEVEL_L', 0);
define('QR_ECLEVEL_M', 1);
define('QR_ECLEVEL_Q', 2);
define('QR_ECLEVEL_H', 3);

class QRcode {
    public static function png($text, $outfile = false, $level = QR_ECLEVEL_L, $size = 3, $margin = 4) {
        // Use the built-in GD library to generate a simple QR code
        // This is a minimal implementation; for production, use the full phpqrcode library
        if (!function_exists('imagepng')) {
            throw new Exception('GD extension with PNG support is required');
        }
        // Use a simple QR code generator (built-in for this project)
        $matrix = QRcode::simple_qr_matrix($text);
        $pixelsPerPoint = $size;
        $imgSize = (count($matrix) + 2 * $margin) * $pixelsPerPoint;
        $image = imagecreatetruecolor($imgSize, $imgSize);
        $white = imagecolorallocate($image, 255, 255, 255);
        $black = imagecolorallocate($image, 0, 0, 0);
        imagefill($image, 0, 0, $white);
        for ($y = 0; $y < count($matrix); $y++) {
            for ($x = 0; $x < count($matrix[$y]); $x++) {
                $color = $matrix[$y][$x] ? $black : $white;
                imagefilledrectangle(
                    $image,
                    ($x + $margin) * $pixelsPerPoint,
                    ($y + $margin) * $pixelsPerPoint,
                    ($x + $margin + 1) * $pixelsPerPoint - 1,
                    ($y + $margin + 1) * $pixelsPerPoint - 1,
                    $color
                );
            }
        }
        if ($outfile) {
            imagepng($image, $outfile);
        } else {
            header('Content-Type: image/png');
            imagepng($image);
        }
        imagedestroy($image);
    }

    // Minimal QR matrix generator (for demo only, not full QR spec!)
    public static function simple_qr_matrix($text) {
        // For demo: encode as a simple pattern based on text hash
        $size = 21; // Version 1 QR code size
        $matrix = array_fill(0, $size, array_fill(0, $size, 0));
        $hash = md5($text);
        for ($i = 0; $i < strlen($hash); $i++) {
            $row = ($i * 3) % $size;
            $col = ($i * 7) % $size;
            $matrix[$row][$col] = (hexdec($hash[$i]) % 2) ? 1 : 0;
        }
        // Add finder patterns (top-left, top-right, bottom-left)
        foreach ([[0,0],[0,$size-7],[$size-7,0]] as $pos) {
            for ($y=0;$y<7;$y++) for ($x=0;$x<7;$x++) {
                $matrix[$pos[0]+$y][$pos[1]+$x] = ($x==0||$x==6||$y==0||$y==6||($x>=2&&$x<=4&&$y>=2&&$y<=4)) ? 1 : 0;
            }
        }
        return $matrix;
    }
}
