<?php

require_once __DIR__ . '/inc/core.php';

if (!isset($_GET['id']))
    exit("500");

/** @var $recipes Objects\Recipe[] */
$recipes = [];

$json = @file_get_contents('http://recommender-service/content?recipe=' . $_GET['id'] . '&amount=3');

if ($json !== false)
{
    foreach (json_decode($json)->recipes as $recommendation)
    {
        $result = Recipe::Fetch((int) $recommendation);
        if ($result == null) continue;
        $recipes[] = $result;
    }
}
else
{
    // Emergency-Backup:
    $recipes[] = Favorite::RecommendRandom();
    $recipes[] = Favorite::RecommendRandom();
    $recipes[] = Favorite::RecommendRandom();
}

?>


<?php foreach ($recipes as $recipe) { ?>
<div class="col-4 portfolio-item">
    <div class="card h-100">
        <a href="details.php?id=<?=$recipe->id;?>"><img class="card-img-top" src="<?=$recipe->thumbnail;?>" alt=""></a>
        <div class="card-body">
            <h5 style="margin-bottom: 5px;">
                <a href="details.php?id=<?=$recipe->id;?>"><?=$recipe->title;?></a>
            </h5>
            <p style="margin: 0;">
                <?php
                echo !empty($recipe->cuisine) ? implode(', ', $recipe->cuisine) : '';
                if (!empty($recipe->cuisine) && !empty($recipe->course)) echo ' - ';
                echo !empty($recipe->course) ? implode(', ', $recipe->course) : '';
                ?>
            </p>
        </div>
    </div>
</div>
<?php } ?>