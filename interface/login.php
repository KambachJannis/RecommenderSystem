<?php

require_once __DIR__ . '/inc/core.php';

$error_message = '';
if (isset($_POST) && isset($_POST['type']))
{
    if ($_POST['type'] == 'register')
    {
        $result = User::Register($_POST['mail'], $_POST['pass1'], $_POST['pass2']);
        if ($result === true)
            $error_message = 'You can now login!';
        else
            $error_message = $result;
    }
    else if ($_POST['type'] == 'login')
    {
        $result = User::Login($_POST['mail'], $_POST['pass']);
        if ($result !== null)
        {
            $_SESSION['user_id'] = $result;
            redirect('home.php');
            exit;
        }

        $error_message = 'Login failed!';
    }
}

?>
<?php require_once __DIR__ . '/inc/_template/head_start.php'; ?>
<link href="css/base.css" rel="stylesheet">
<link href="css/login.css" rel="stylesheet">
<?php require_once __DIR__ . '/inc/_template/head_end.php'; ?>
<?php require_once __DIR__ . '/inc/_template/navigation.php'; ?>

<div class="container">
    <div class="row no-gutter">
        <div class="d-none d-md-flex col-md-4 col-lg-6 bg-image"></div>
        <div class="col-md-8 col-lg-6">
            <div class="login d-flex align-items-center py-5">
                <div class="container">
                    <div class="row">
                        <div class="col-md-9 col-lg-8 mx-auto">
                            <h3 class="login-heading text-danger mb-4" id="error_message" style="display: <?=$error_message != '' ? 'block' : 'none';?>;"><?=$error_message;?></h3>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-9 col-lg-8 mx-auto">
                            <h3 class="login-heading mb-4">Login!</h3>
                            <form method="post">
                                <div class="form-label-group">
                                    <input type="email" id="inputEmail" name="mail" class="form-control" placeholder="Email address" required autofocus>
                                    <label for="inputEmail">Email address</label>
                                </div>

                                <div class="form-label-group">
                                    <input type="password" id="inputPassword" name="pass" class="form-control" placeholder="Password" required>
                                    <label for="inputPassword">Password</label>
                                </div>

                                <input type="hidden" name="type" value="login">
                                <button class="btn btn-lg btn-primary btn-block btn-login text-uppercase font-weight-bold mb-2" type="submit">Sign in</button>
                            </form>
                        </div>
                    </div>
                    <div class="row" style="margin-top: 50px;">
                        <div class="col-md-9 col-lg-8 mx-auto">
                            <h3 class="login-heading mb-4">Register!</h3>
                            <form method="post">
                                <div class="form-label-group">
                                    <input type="email" id="inputEmail2" name="mail" class="form-control" placeholder="Email address" required autofocus>
                                    <label for="inputEmail2">Email address</label>
                                </div>

                                <div class="form-label-group">
                                    <input type="password" id="inputPassword3" name="pass1" class="form-control" placeholder="Password" required>
                                    <label for="inputPassword3">Password</label>
                                </div>

                                <div class="form-label-group">
                                    <input type="password" id="inputPassword4" name="pass2" class="form-control" placeholder="Password" required>
                                    <label for="inputPassword4">Password</label>
                                </div>

                                <input type="hidden" name="type" value="register">
                                <button class="btn btn-lg btn-primary btn-block btn-login text-uppercase font-weight-bold mb-2" type="submit">Register</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/inc/_template/footer_start.php'; ?>
<?php require_once __DIR__ . '/inc/_template/footer_end.php'; ?>
