<?php

namespace Drupal\brebo_europakozijn\Service;

/**
 * Bounded raster previews from existing uploads; never fetches remote URLs.
 *
 * PDF/HEIC preview generation remains outside this presentation layer. No
 * original is exposed through a new URL or extracted onto a public filesystem.
 */
final class ProjectResultThumbnail {

  private const MAX_BYTES = 5242880;
  private const MAX_PIXELS = 12000000;

  public function create(string $directory, array $upload, array $document): string {
    if (!function_exists('imagecreatefromstring') || !function_exists('imagejpeg')) {
      return '';
    }
    $name = $document['filename'] ?? '';
    $stored = $upload['stored_name'] ?? '';
    if (!is_string($name) || !is_string($stored) || !$this->safeName($name) || !$this->safeName($stored) || basename($stored) !== $stored) {
      return '';
    }
    if (!in_array(strtolower(pathinfo($name, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'webp'], TRUE)) {
      return '';
    }
    $root = realpath($directory);
    $path = $root === FALSE ? FALSE : realpath($root . DIRECTORY_SEPARATOR . $stored);
    if ($path === FALSE || !str_starts_with($path, $root . DIRECTORY_SEPARATOR) || !is_file($path)) {
      return '';
    }
    try {
      if (strtolower(pathinfo($stored, PATHINFO_EXTENSION)) === 'zip') {
        $bytes = $this->archiveImage($path, $name);
      }
      else {
        if ($name !== ($upload['name'] ?? '') || filesize($path) > self::MAX_BYTES) {
          return '';
        }
        $bytes = @file_get_contents($path, FALSE, NULL, 0, self::MAX_BYTES + 1);
      }
      return is_string($bytes) && strlen($bytes) <= self::MAX_BYTES ? $this->raster($bytes) : '';
    }
    catch (\Throwable) {
      // Preview failure must never make a successful Office result unavailable.
      return '';
    }
  }

  private function safeName(string $name): bool {
    return $name !== '' && strlen($name) <= 1024 && !preg_match('~(^/|[\\\\:\x00-\x1f]|(^|/)\.\.?(/|$))~', $name);
  }

  private function archiveImage(string $path, string $name): string {
    if (!class_exists(\ZipArchive::class)) {
      return '';
    }
    $zip = new \ZipArchive();
    if ($zip->open($path, \ZipArchive::RDONLY) !== TRUE) {
      return '';
    }
    try {
      if ($zip->numFiles > 2000) {
        return '';
      }
      $matches = [];
      for ($index = 0; $index < $zip->numFiles; $index++) {
        $entry = $zip->getNameIndex($index);
        if (!is_string($entry) || !$this->safeName($entry) || str_starts_with($entry, '__MACOSX/')) {
          continue;
        }
        // Never choose arbitrarily between duplicate names in archive folders.
        if ($entry === $name || (!str_contains($name, '/') && basename($entry) === $name)) {
          $matches[] = $index;
        }
      }
      if (count($matches) !== 1) {
        return '';
      }
      $stat = $zip->statIndex($matches[0]);
      if (!$stat || $stat['size'] < 1 || $stat['size'] > self::MAX_BYTES || $stat['size'] > max(1, $stat['comp_size']) * 200) {
        return '';
      }
      $bytes = $zip->getFromIndex($matches[0], self::MAX_BYTES + 1);
      return is_string($bytes) && strlen($bytes) === (int) $stat['size'] ? $bytes : '';
    }
    finally {
      $zip->close();
    }
  }

  private function raster(string $bytes): string {
    $size = @getimagesizefromstring($bytes);
    if (!$size || !in_array($size[2], [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_WEBP], TRUE) || $size[0] < 1 || $size[1] < 1 || $size[0] * $size[1] > self::MAX_PIXELS) {
      return '';
    }
    // Reserve memory for both decoding overhead and the thumbnail.
    $limit = ini_get('memory_limit');
    if ($limit !== FALSE && $limit !== '-1' && ini_parse_quantity($limit) - memory_get_usage(TRUE) < $size[0] * $size[1] * 8 + 16777216) {
      return '';
    }
    $source = @imagecreatefromstring($bytes);
    if ($source === FALSE) {
      return '';
    }
    $scale = min(1, 420 / $size[0], 320 / $size[1]);
    $width = max(1, (int) round($size[0] * $scale));
    $height = max(1, (int) round($size[1] * $scale));
    $target = imagecreatetruecolor($width, $height);
    if ($target === FALSE) {
      imagedestroy($source);
      return '';
    }
    try {
      imagefill($target, 0, 0, imagecolorallocate($target, 255, 255, 255));
      imagecopyresampled($target, $source, 0, 0, 0, 0, $width, $height, $size[0], $size[1]);
      ob_start();
      try {
        $ok = imagejpeg($target, NULL, 78);
        $jpeg = (string) ob_get_contents();
      }
      finally {
        ob_end_clean();
      }
      // Re-encoding strips metadata. No SVG, original bytes or external URL.
      return $ok && strlen($jpeg) <= 131072 ? 'data:image/jpeg;base64,' . base64_encode($jpeg) : '';
    }
    finally {
      imagedestroy($source);
      imagedestroy($target);
    }
  }

}
