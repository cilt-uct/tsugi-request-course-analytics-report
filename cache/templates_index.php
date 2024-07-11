<?php class_exists('Template') or exit; ?>
<div class="flex-container">
    <?php foreach($reports as $rep): ?>
        <?php if($rep["active"]) { ?>
            <div>
                <a href="<?php print($rep['url']) ?>">
                    <img src="<?php print($rep['img']) ?>" class='report-image'>
                    <h5>"<?php print($rep['title']) ?>"</h5>
                </a>
            </div>
        <?php } ?>
    <?php endforeach; ?>
</div>
