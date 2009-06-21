<?
include_once('../lib/ImageEditor.php');

// create destination image
$dst = new ImageEditor();
// assing canvas size
$dst->createCanvas(250, 200);

// filenames
$src1FileName = 'all4.jpg';
$src2FileName = 'bart.jpg';
$src3FileName = 'splash.jpg';
$src4FileName = 'water.jpg';

/**
 * drop the sourcefiles resized into destination image
 */
// src1
$src1 = new ImageEditor();
$src1->loadImageFile($src1FileName);
$dst->fillinArea($src1, 0, 0, 150, 100);

// src2
$src2 = new ImageEditor();
$src2->loadImageFile($src2FileName);
$dst->fillinArea($src2, 0, 100, 150, 100);

// src3
$src3 = new ImageEditor();
$src3->loadImageFile($src3FileName);
// apply drift of cropping mask to the left(x-direction)
$dst->fillinArea($src3, 150, 0, 100, 100, 50, 0);

// src4
$src4 = new ImageEditor();
$src4->loadImageFile($src4FileName);
$dst->fillinArea($src4, 150, 100, 100, 100);

// write the image
$dst->writeImageFile('out.jpg', ImageEditor::JPG, 75);

?><img src="out.jpg">
