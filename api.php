<?php
include_once("functions.php");

header('Content-Type: application/json; charset=UTF-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'status' => 'error',
        'message' => 'Not allowed'
    ]);
    exit;
}

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

function killMpv (){
    $jsonResponse = shell_exec("pkill mpv");
    $jsonResponse["success"]=true;
    return $jsonResponse ;
}

$jsonInput = file_get_contents('php://input');
$data = json_decode($jsonInput, true);

if ($data['action']) {
	switch ($data['action']) {
	    case 'stop':
		$jsonResponse =killMpv();
                sleep(1);
		break;
	    case 'play':
                if ($data['stream']){
                    $statusPlayer = getStreamPlaying();
                    if ($statusPlayer['isPlaying']){
                       killMpv();
                    }
                    $jsonResponse = shell_exec("mpv --input-ipc-server=/tmp/mpvsocket --no-video --audio-device=alsa/default " . escapeshellarg($data['stream']) . " > /dev/null 2>&1 &");
                    sleep(4);
                    $jsonResponse["success"]=true;
                }else {
                    $jsonResponse["success"]=false;
                    $jsonResponse["message"]="input error";
                }
		break;
	    case 'volup':
               $currVolume = getVolume ();
               if (isset($currVolume) && isset($currVolume["vol"])) {
                   $increm = $currVolume["vol"] + 20 < 130 ? "20" : 130-$currVolume["vol"];
    		   $volResp = shell_exec('echo \'{"command": ["cycle", "volume", "'.$increm.'"]}\' | socat - /tmp/mpvsocket');                
                   $volObj = json_decode($volResp, true);
                   $jsonResponse["success"]=$volObj['error'] == "success";
                   if (!$jsonResponse["success"]){
                      $jsonResponse["message"]=$volObj['error']; 
                   }
                }else {
                   $jsonResponse["success"]=false;
                   $jsonResponse["message"]=isset($currVolume["error"])? $currVolume["error"] : "Error setting volume";
                }
		break;			
	    case 'voldown':
                $currVolume = getVolume ();
                if (isset($currVolume) && isset($currVolume["vol"])){
                    $decrem = $currVolume["vol"] - 20 > 0 ? "20" : $currVolume["vol"];
                    $volResp = shell_exec('echo \'{"command": ["cycle", "volume", "-'.$decrem.'"]}\' | socat - /tmp/mpvsocket');
                    $volObj = json_decode($volResp, true);
                    $jsonResponse["success"]=$volObj['error'] == "success";
                    if (!$jsonResponse["success"]){
                       $jsonResponse["message"]=$volObj['error']; 
                    }	
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
}else {
    $jsonResponse["success"]=false;
    $jsonResponse["message"]="Input error";
}
echo json_encode($jsonResponse);
