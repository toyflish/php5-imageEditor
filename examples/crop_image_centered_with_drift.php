<?
include_once('../lib/ImageEditor.php');

// create destination image
$dst = new ImageEditor();
// assing canvas size
$dst->createCanvas(100, 200);

/**
 * cut all4 centered to canvas size
 */
$src = new ImageEditor();
$src->loadImageFile('all4.jpg');
// fill destination image with source image
// keeping destination canvas-size(100,200)
$dst->fillin($src);

// write the image
$dst->writeImageFile('out1.jpg', ImageEditor::JPG, 75);

/**
 * apply drift to kai(right 120)
 */
$src = new ImageEditor();
$src->loadImageFile('all4.jpg');
$dst->fillin($src, 120, 0);

// write the image
$dst->writeImageFile('out2.jpg', ImageEditor::JPG, 75);

/**
 * apply drift to Anne (left -100)
 */
$src = new ImageEditor();
$src->loadImageFile('all4.jpg');
$dst->fillin($src, -100, 0);

// write the image
$dst->writeImageFile('out3.jpg', ImageEditor::JPG, 75);

?>
<img src="out1.jpg">
<img src="out2.jpg">
<img src="out3.jpg">

<div style="font-size:11px;">
<?
highlight_file('fillinDrift.php');
?>
</div>
