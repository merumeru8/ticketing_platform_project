<!DOCTYPE html>
<html lang="en">


<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tickets Project</title>
    
    <link rel="icon" type="image/png" sizes="32x32" href="/images/icon_32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/images/icon_16.png">

    <!-- languages and fonts-->
    <link rel="stylesheet" href="/styles/fonts.css" media="all">

    <!-- title with dog handling-->
    <link rel="stylesheet" href="/styles/style.css?v=6" media="all">

    <!-- Font awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" integrity="sha512-1ycn6IcaQQ40/MKBW2W4Rhis/DbILU74C1vSrLJxCq57o941Ym01SwNsOMqvEBFlcgUa6xLiPY/NS5R+E6ztJQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <!-- Common javascript for all the pages -->
    <script src="/js/async_calls.js" id="ajax-calls-js"></script>
    <script src="/js/utils.js" id="utils-js"></script>

</head>

<body>

<header>
    <div class="main-header-bar-wrap opposite_sides">
        <div class="site-header-section main-header-left">
            <a href="/" class="custom-logo-link" rel="home" aria-current="page"><img src="/images/ticketing_platform_logo.png"></a>
        </div>

        <div class="site-header-section main-header-right">
            <?php if(getSession("isLoggedIn")): ?>
            <div class="user-badge" aria-label="User profile">
                <img class="user-avatar" src="/images/avatar_placeholder.png" alt="Avatar" loading="lazy">
                <div class="user-text">
                    <span class="user-name"><?= getSession("user_name") ?></span>
                    <span class="user-role"><?= strtoupper(getSession("user_group")) ?></span>
                </div>
            </div>
            <?php endif;?>
            <?= (getSession("isLoggedIn") ? \App\Views\components\Button::generateButton("logout_btn", "", "Logout", []) : \App\Views\components\Button::generateButton("login_btn", "", "Login", [])) ?>
        </div>
    </div>
</header>

<?= \App\Views\components\Popup::generatePopup("popup_id", "", "", true); ?>