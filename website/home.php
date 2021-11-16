<?php require_once __DIR__ . '/inc/core.php'; ?>
<?php require_once __DIR__ . '/inc/_template/head_start.php'; ?>
<link href="css/base.css" rel="stylesheet">
<style>
    body
    {
        height: 100vh;
    }

    .bg-image-container
    {
        height: 100%;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        background-image: url('img/user_bg-min.jpg');
        background-repeat: no-repeat;
        background-attachment: fixed;
        background-size: 100%;
        opacity: 0.5;
        filter:alpha(opacity=50);
    }
</style>
<?php require_once __DIR__ . '/inc/_template/head_end.php'; ?>
<?php require_once __DIR__ . '/inc/_template/navigation.php'; ?>

<div class="bg-image-container"></div>
<div class="container text-center" style="margin-top: 20vh;">
    <div class="card" style="margin-left: 20vh; margin-right: 20vh;">
        <div class="card-body bg-gray">

            <h1 class="cover-heading">Recipe Recommender</h1>
            <p class="lead">Finding recipes you will love.</p>

            <div class="row mt-5">
                <div class="offset-2 col-4">
                    <p class="lead text-center">
                        <a href="details.php?id=1000" class="btn btn-lg btn-secondary text-center" style="width: 120px;">Explore</a>
                    </p>
                </div>
                <div class="col-4">
                    <p class="lead text-center">
                        <a href="<?=isset($_SESSION['user_id']) ? 'logout' : 'login';?>.php" class="btn btn-lg btn-secondary text-center" style="width: 120px;"><?=isset($_SESSION['user_id']) ? 'Logout' : 'Login';?></a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/inc/_template/footer_start.php'; ?>
<?php require_once __DIR__ . '/inc/_template/footer_end.php'; ?>
