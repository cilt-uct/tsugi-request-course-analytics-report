<?php class_exists('Template') or exit; ?>

<?php foreach($stylesheets as $style): ?>
    <link href="<?php echo $style ?>" rel="stylesheet" />
<?php endforeach; ?>
