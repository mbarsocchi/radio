<?php
include_once("functions.php");
?>
<!DOCTYPE html>
<html lang="it">
    <head>
        <title>Radio Player Web</title>
        <meta name="viewport" content="width=device-width,initial-scale=1.0,maximum-scale=1.0,user-scalable=no">
    </head>
    <body>
    <link rel="stylesheet" href="style.css">
    <div id="loaderOverlay" class="loading-overlay">
        <div class="spinner"></div>
        <div class="loading-text">Caricamento in corso...</div>
    </div>
    <?php if(clientInSameSubnet()) {
        $statusPlayer = getStreamPlaying();?>
        <?php if($statusPlayer['isPlaying']) {?>
            <a class="stream-card stream-item" data-action="voldown">
                <h3>Vol-</h3>
            </a>
            <a class="stream-card stream-item" data-action="stop">
                <h3>Quit</h3>
            </a>
            <a class="stream-card stream-item" data-action="volup">
                <h3>Vol+</h3>
            </a>
        <?php }?>

        <?php foreach (getAllRadios() as $stationName => $streamUrl) {?>
        <a class="stream-card stream-item" data-stream-url="<?php echo $streamUrl;?>" data-action="play">
            <h3><?php echo $stationName;?></h3>
        </a>
        <?php }?>
    <?php } else { ?>
        Forbidden
    <?php } ?>
    <script src="script.js"></script>
</body>
</html>