<?php class_exists('Template') or exit; ?>
<div class="flex-container">
    <?php foreach ($reports as $rep) {
        $title = !empty($rep['title']) ? $rep['title'] : '';
        $url = !empty($rep['url']) ? $rep['url'] : '';
        $description = !empty($rep['description']) ? $rep['description'] : '';
        print "<div><a href='". $url ."'><img src='". $rep['img'] ."' class='report-image'><h5>". $rep['title'] ."</h5></a></div>";
    } ?>
</div>
