<?php
include_once("functions.php");
?>
<!DOCTYPE html>
<title>Radio Player Web</title>
<meta name="viewport" content="width=device-width,initial-scale=1.0,maximum-scale=1.0,user-scalable=no">
<link rel="stylesheet" href="style.css">
<div id="loaderOverlay" class="loading-overlay">
    <div class="spinner"></div>
    <div class="loading-text">Caricamento in corso...</div>
</div>
<?php if(clientInSameSubnet()) {
    $statusPlayer = getStreamPlaying(); print_r($statusPlayer);?>
    <?php if($statusPlayer['isPlaying']) {?>
        <a href="api.php?action=volup">Vol -</a>
        <a href="api.php?action=stop">Quit</a>
        <a href="api.php?action=voldown">Vol +</a>
    <?php }?>

    <?php foreach (getAllRadios() as $stationName => $streamUrl) {?>
        <a href="api.php?action=play=<?php echo $streamUrl;?>"><?php echo $stationName;?></a>
    <?php }?>
<?php } else { ?>
    Forbidden
<?php } ?>
