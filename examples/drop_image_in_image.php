<?
include_once('../lib/ImageEditor.php');

$src = new ImageEditor();
$dst = new ImageEditor();
$dst->createCanvas(200, 400);
$src->loadImageFile('kai_schwanger.jpg' );
$dst->fillin($src, 0, 1000);

$drop = new ImageEditor();
$drop->loadImageFile('ruth.jpg');

#$dst->dropin($drop, 20 ,50);
$dst->fillinArea($drop, 10, 20, 100, 100);
$dst->fillinArea($drop, 10, 350, 100, 100);
#$src->rotate(90);
#$dst->fitin($src);
#$dst->applyGrayscale();
#$dst->pseudosepia(20);
#$dst->sepia(60);
#$dst->grayscale();

$dst->writeImageFile('out.jpg', ImageEditor::JPG, 75);
#$src->writeImageFile('out.jpg', ImageEditor::JPG, 75);

#$dst->displayImage('JPG');
?>
