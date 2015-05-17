<?php
/****
 * A template for editing the custom stylesheet through the web.
 */
if ($auth<3) {
    setMessage("Access denied.");
    header("Location: {$installroot}");
    exit(0);
}
if (getPOST('stylesheet')) {
    file_put_contents("./css/custom.css", $_POST['stylesheet']);
    $customstylesheet = $_POST['stylesheet'];
} else {
    $customstylesheet = file_get_contents("./css/custom.css");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title><?=$configuration['site_title']?></title>
    <meta http-equiv="Content-Type" content="text/html;charset=utf-8">
    <?php
    jqueryCDN();
    ?>
    <link rel="stylesheet" type="text/css" href="css/styles.css">
    <link rel="stylesheet" type="text/css" href="templates/customstyles/css/styles.css">
</head>
<body>
<div id="page">
    <a href="<?=$installroot?>" class="returnlink"><?=__("return")?></a>
    <h1><?=__("custom stylesheet editor")?></h1>
    <form method="post" action="<?=$installroot?>/?action=customstyles">
    <div id="stylesheet">
    <textarea name="stylesheet"><?=$customstylesheet?></textarea><br>
    <button class="submit"><?=__("submitbutton")?></button>
    </div>
    </form>
</div>
</body>
</html>
