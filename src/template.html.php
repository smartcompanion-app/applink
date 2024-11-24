<!DOCTYPE html>
<html lang="en" prefix="og: https://ogp.me/ns#">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta property="og:title" content="<?php echo $page['title']; ?>" />
    <meta property="og:type" content="website" />
    <meta property="og:url" content="<?php echo $page['base-url']; ?>?app=<?php echo $app; ?>" />
    <meta property="og:image" content="<?php echo $page['base-url']; ?>/icons/<?php echo $page['app-icon']; ?>" />
    <title><?php echo $page['title']; ?></title>
    <link rel="stylesheet" href="style.css">    
</head>
<body style="background-color: <?php echo $page['background-color']; ?>">
    <main>
        <?php if (isset($page['app-icon'])): ?>
            <img id="app-icon" src="./icons/<?php echo $page['app-icon']; ?>" alt="<?php echo $page['title']; ?> App Icon">
        <?php endif; ?>
        <?php if (isset($links['android'])): ?>
            <a class="badge" href="<?php echo $links['android']; ?>">
                <img src="./assets/google-play-badge.svg" alt="Google Play Download">
            </a>
        <?php endif; ?>
        <?php if (isset($links['ios'])): ?>
            <a class="badge" href="<?php echo $links['ios']; ?>">
                <img src="./assets/app-store-badge.svg" alt="App Store Download">
            </a>
        <?php endif; ?>
    </main>
</body>
</html>