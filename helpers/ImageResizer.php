<?php
class ImageResizer {
    public static function resizeImage($filePath, $maxWidth = 1200, $maxHeight = 1200) {
        // Prevent enormous original files (over ~20MB)
        if (filesize($filePath) > 20 * 1024 * 1024) {
            @unlink($filePath);
            throw new RuntimeException('⚠️ Image too large. Maximum 20MB.');
        }

        [$width, $height, $type] = getimagesize($filePath);
        if ($width <= 0 || $height <= 0) {
            return;
        }

        // Avoid invalid aspect ratios
        $maxAspect = 3.0;
        $minAspect = 1.0 / 3.0;
        $aspect = $width / $height;

        // Crop extreme aspect ratios to center
        if ($aspect > $maxAspect) {
            $newWidth = (int)($height * $maxAspect);
            $xOffset = (int)(($width - $newWidth) / 2);
            $yOffset = 0;
            $cropWidth = $newWidth;
            $cropHeight = $height;
        } elseif ($aspect < $minAspect) {
            $newHeight = (int)($width / $minAspect);
            $yOffset = (int)(($height - $newHeight) / 2);
            $xOffset = 0;
            $cropWidth = $width;
            $cropHeight = $newHeight;
        } else {
            $xOffset = 0;
            $yOffset = 0;
            $cropWidth = $width;
            $cropHeight = $height;
        }

        // Create source image
        switch ($type) {
            case IMAGETYPE_JPEG: $src = imagecreatefromjpeg($filePath); break;
            case IMAGETYPE_PNG: $src = imagecreatefrompng($filePath); break;
            case IMAGETYPE_GIF: $src = imagecreatefromgif($filePath); break;
            default: return;
        }

        // Create cropped version
        $cropped = imagecreatetruecolor($cropWidth, $cropHeight);
        imagecopy($cropped, $src, 0, 0, $xOffset, $yOffset, $cropWidth, $cropHeight);

        // Calculate resize dimensions
        $ratio = min($maxWidth / $cropWidth, $maxHeight / $cropHeight, 1);
        $newWidth = max(1, (int)($cropWidth * $ratio));
        $newHeight = max(1, (int)($cropHeight * $ratio));

        $dst = imagecreatetruecolor($newWidth, $newHeight);

        // Preserve transparency
        if ($type == IMAGETYPE_PNG || $type == IMAGETYPE_GIF) {
            imagecolortransparent($dst, imagecolorallocatealpha($dst, 0, 0, 0, 127));
            imagealphablending($dst, false);
            imagesavealpha($dst, true);
        }

        // Resample
        imagecopyresampled($dst, $cropped, 0, 0, 0, 0, $newWidth, $newHeight, $cropWidth, $cropHeight);

        // Save result
        switch ($type) {
            case IMAGETYPE_JPEG: imagejpeg($dst, $filePath, 90); break;
            case IMAGETYPE_PNG: imagepng($dst, $filePath, 9); break;
            case IMAGETYPE_GIF: imagegif($dst, $filePath); break;
        }

        // Ensure final file isn’t still absurdly large
        if (filesize($filePath) > 5 * 1024 * 1024) {
            if ($type == IMAGETYPE_JPEG) {
                imagejpeg($dst, $filePath, 80);
            }
        }

        // Clean up
        imagedestroy($src);
        imagedestroy($cropped);
        imagedestroy($dst);
    }
}
?>
