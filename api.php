<?php

function getVolume (){
    $jsonResponse = shell_exec("echo '{\"command\": [\"get_property\", \"volume\"]}' | socat - /tmp/mpvsocket");
    if ($jsonResponse !== null) {
        $data = json_decode($jsonResponse, true);
        if (isset($data['error']) && $data['error'] === 'success') {
            $res["vol"] = $data['data'];
        } else {
           $res["error"] = $data['error'];
        }
    } else {
        $res["error"] =  "Impossibile comunicare con il socket di mpv. Verifica che mpv sia attivo e che 'socat' sia installato.\n";
    }
    return $res;
}

if ($_GET['action']) {
	switch ($_GET['action']) {
	    case 'stop':
			$jsonResponse = shell_exec("pkill mpv");
			break;
		case 'play':
            if ($_GET['stream']){
                $jsonResponse = shell_exec("mpv --input-ipc-server=/tmp/mpvsocket --no-video --audio-device=alsa/default " . escapeshellarg($_GET['stream']) . " > /dev/null 2>&1 &");
            }else {
                $jsonResponse["success"]=false;
                $jsonResponse["message"]="input error";
            }
			break;
		case 'volup':
            $currVolume = getVolume ();
            if (isset($currVolume) && isset($currVolume["vol"])) {
                $decrem = $currVolume["vol"] + 20 < 130 ? "20" : 130-$currVolume["vol"];
    			$jsonResponse = shell_exec('echo \'{"command": ["cycle", "volume", "+'.$increm.'"]}\' | socat - /tmp/mpvsocket');
            }else {
                $jsonResponse["success"]=false;
                $jsonResponse["message"]=isset($currVolume["error"])? $currVolume["error"] : "Error setting volume";
            }
			break;			
		case 'voldown':
            $currVolume = getVolume ();
            if (isset($currVolume) && isset($currVolume["vol"])){
                $decrem = $currVolume["vol"] - 20 > 0 ? "20" : $currVolume["vol"];
                $jsonResponse = shell_exec('echo \'{"command": ["cycle", "volume", "-'.$decrem.'"]}\' | socat - /tmp/mpvsocket');		
            }else {
                $jsonResponse["success"]=false;
                $jsonResponse["message"]=isset($currVolume["error"])? $currVolume["error"] : "Error setting volume";
            }
        break;
        default:
            $jsonResponse["success"]=false;
            $jsonResponse["message"]="Action unrecognized";
        break;
	}
	
	echo json_encode($jsonResponse);
}