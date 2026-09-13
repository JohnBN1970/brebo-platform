<?php

/**
 * @file
 * Standalone presentation regression tests; no Office or Drupal runtime needed.
 */

use Drupal\brebo_europakozijn\Service\ProjectResultPresenter;
use Drupal\brebo_europakozijn\Service\ProjectResultThumbnail;

require_once __DIR__ . '/../src/Service/ProjectResultThumbnail.php';
require_once __DIR__ . '/../src/Service/ProjectResultPresenter.php';

$checks = 0;
$check = static function (bool $condition, string $message) use (&$checks): void {
  if (!$condition) {
    throw new RuntimeException($message);
  }
  $checks++;
};
$thumbnail = new ProjectResultThumbnail();
$presenter = new ProjectResultPresenter($thumbnail);
$documents = [
  ['filename' => 'Voorgevel.jpg', 'status' => 'extracted', 'excerpt' => 'Een aangeleverde foto.'],
  ['filename' => 'Kozijntekeningen begane grond.pdf', 'status' => 'extracted', 'excerpt' => "Bronfragment met maatvoering.\nDit is geen definitieve kozijnstaat."],
  ['filename' => 'Projectomschrijving.pdf', 'status' => 'extracted', 'excerpt' => 'Dit document bevat de aangeleverde projectomschrijving.'],
  ['filename' => 'Foto_01.HEIC', 'status' => 'provider_error'],
  ['filename' => 'Foto_02.HEIC', 'status' => 'provider_error'],
  ['filename' => 'Foto_03.HEIC', 'status' => 'provider_error'],
  ['filename' => 'Foto_04.HEIC', 'status' => 'provider_error'],
];
$result = [
  'intake_id' => '12345678-1234-4234-8234-123456789abc',
  'source_of_truth' => 'BREBO Office',
  'files' => [[
    'name' => 'Projectstukken.zip', 'stored_name' => '01-projectstukken.zip',
    'office' => ['ok' => TRUE, 'recognition' => ['document_count' => 7, 'documents' => $documents]],
  ]],
  'office_summary' => ['documents_indexed' => 7, 'documents_read' => 3],
];
$view = $presenter->present($result, '/does-not-exist');
$check([$view['total'], $view['read'], $view['failed'], $view['pending']] === [7, 3, 4, 0], 'Seven documents: three read, four failed, none guessed.');
$check(count($view['gallery']) === 5 && count($view['documents']) === 7, 'Five cards, seven rows.');
$check($view['gallery'][0]['name'] === 'Voorgevel.jpg' && $view['gallery'][4]['name'] === 'Foto_02.HEIC', 'Use archive children in stable order.');
$check($view['documents'][1]['excerpt'] === $documents[1]['excerpt'], 'Keep source text unchanged.');
$check($view['state'] === 'Deels uitgelezen', 'Do not mark a partial result complete.');
$check(!isset($view['documents'][0]['extractor'], $view['documents'][0]['confidence']), 'No internal diagnostics in visitor model.');
$empty = $presenter->present([], '');
$check($empty['total'] === 0 && $empty['gallery'] === [], 'Handle empty response.');
$check($presenter->present(['files' => 'invalid', 'office_summary' => 'invalid'], '')['total'] === 0, 'Malformed payload is safe.');
$unknown = $result;
$unknown['files'][0]['office']['recognition']['documents'][0]['status'] = 'new_status_not_in_contract';
$unknownView = $presenter->present($unknown, '');
$check($unknownView['pending'] === 1 && $unknownView['read'] === 2, 'Unknown status is not silently successful.');
$missing = $result;
$missing['office_summary']['documents_indexed'] = 10;
$missingView = $presenter->present($missing, '');
$check($missingView['missing_details'] === 3 && $missingView['pending'] === 3, 'Missing details are not called failed or read.');
$handoff = $result;
$handoff['files'][0]['office']['ok'] = FALSE;
$check($presenter->present($handoff, '')['handoff_failed'], 'Separate receipt from handoff.');
$check($thumbnail->create('', ['stored_name' => '../outside.jpg'], ['filename' => 'outside.jpg']) === '', 'Reject traversal.');
$check($thumbnail->create('', ['stored_name' => 'a.pdf'], ['filename' => 'a.pdf']) === '', 'Honest PDF fallback.');
$check($thumbnail->create('', ['stored_name' => 'a.HEIC'], ['filename' => 'a.HEIC']) === '', 'Honest HEIC fallback.');

$rasterAvailable = function_exists('imagecreatefromstring') && class_exists(ZipArchive::class);
if ($rasterAvailable) {
  $dir = sys_get_temp_dir() . '/brebo-result-test-' . bin2hex(random_bytes(6));
  mkdir($dir, 0700);
  try {
    $image = imagecreatetruecolor(24, 16);
    imagefill($image, 0, 0, imagecolorallocate($image, 130, 150, 170));
    imagepng($image, $dir . '/picture.png');
    imagedestroy($image);
    $upload = ['name' => 'picture.png', 'stored_name' => 'picture.png'];
    $doc = ['filename' => 'picture.png'];
    $data = $thumbnail->create($dir, $upload, $doc);
    $check(str_starts_with($data, 'data:image/jpeg;base64,'), 'Real image re-encoded as JPEG.');
    $check(strlen($data) < 180000, 'Bounded inline preview.');
    file_put_contents($dir . '/fake.jpg', '<svg onload="alert(1)"></svg>');
    $check($thumbnail->create($dir, ['name' => 'fake.jpg', 'stored_name' => 'fake.jpg'], ['filename' => 'fake.jpg']) === '', 'Reject active content disguised as a photo.');
    $zip = new ZipArchive();
    $zip->open($dir . '/archive.zip', ZipArchive::CREATE);
    $zip->addFile($dir . '/picture.png', 'photos/picture.png');
    $zip->addFromString('../outside.png', file_get_contents($dir . '/picture.png'));
    $zip->close();
    $archive = ['name' => 'archive.zip', 'stored_name' => 'archive.zip'];
    $check(str_starts_with($thumbnail->create($dir, $archive, $doc), 'data:image/jpeg;base64,'), 'Locate unambiguous image inside ZIP.');
    $check($thumbnail->create($dir, $archive, ['filename' => '../outside.png']) === '', 'Reject unsafe ZIP member.');
    $zip->open($dir . '/archive.zip');
    $zip->addFile($dir . '/picture.png', 'other/picture.png');
    $zip->close();
    $check($thumbnail->create($dir, $archive, $doc) === '', 'Ambiguous basename never selects a random image.');
    $check($thumbnail->create($dir, $archive, ['filename' => 'https://example.com/picture.png']) === '', 'Never fetch external previews.');
    symlink(__FILE__, $dir . '/escape.jpg');
    $check($thumbnail->create($dir, ['name' => 'escape.jpg', 'stored_name' => 'escape.jpg'], ['filename' => 'escape.jpg']) === '', 'Reject symlink escaping intake directory.');
  }
  finally {
    foreach (glob($dir . '/*') as $path) {
      unlink($path);
    }
    rmdir($dir);
  }
}
elseif (getenv('REQUIRE_THUMBNAIL_EXTENSIONS') === '1') {
  throw new RuntimeException('CI must exercise GD and ZIP, not skip them.');
}

$autoload = getenv('RESULT_TEST_AUTOLOAD');
if ($autoload) {
  require_once $autoload;
  $twig = new Twig\Environment(new Twig\Loader\FilesystemLoader(__DIR__ . '/../templates'), ['autoescape' => 'html', 'strict_variables' => TRUE]);
  $rendered = $twig->render('brebo-europakozijn-project-result.html.twig', ['result' => ['presentation' => $view]]);
  $check(substr_count($rendered, 'class="ek-results__document-card"') === 5, 'Render five real template cards.');
  $check(substr_count($rendered, '<tr id="project-document-') === 7, 'Render all seven rows outside details.');
  $check(!str_contains($rendered, '<details open'), 'Only source fragments collapse, not primary result.');
  $attack = $view;
  $attack['documents'][0]['name'] = '<script>alert(1)</script>';
  $attack['documents'][0]['excerpt'] = '<img src=x onerror=alert(2)>';
  $safe = $twig->render('brebo-europakozijn-project-result.html.twig', ['result' => ['presentation' => $attack]]);
  $check(!str_contains($safe, '<script>') && str_contains($safe, '&lt;script&gt;'), 'Escape document names.');
  $check(!str_contains($safe, '<img src=x') && str_contains($safe, '&lt;img'), 'Escape untrusted extracted text.');
  $twig->render('brebo-europakozijn-project-result.html.twig', ['result' => ['presentation' => $empty]]);
  $check(TRUE, 'Empty-state Twig render.');
  if (getenv('RESULT_TEST_HTML')) {
    $css = file_get_contents(__DIR__ . '/../css/europakozijn-project-result.css');
    file_put_contents(getenv('RESULT_TEST_HTML'), '<!doctype html><html lang="nl"><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>BREBO resultaat - testfixture</title><style>body{margin:0;font-family:Arial,sans-serif}' . $css . '</style><body>' . $rendered . '</body></html>');
  }
}
if (in_array('--fixture', $argv, TRUE)) {
  echo json_encode(['result' => ['presentation' => $view]], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}
else {
  echo "PASS: $checks checks. GD/ZIP: " . ($rasterAvailable ? 'tested' : 'unavailable; fallback tested') . '. Twig: ' . ($autoload ? 'rendered' : 'not installed') . "\n";
}
