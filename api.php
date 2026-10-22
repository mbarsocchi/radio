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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'status' => 'error',
        'message' => 'Not Allowed'
    ]);
    exit;
}

$jsonInput = file_get_contents('php://input');
$data = json_decode($jsonInput, true);


switch ($data['action']) {
    case 'stop':
        exec("pkill mpv", $output, $result_code);
        $jsonResponse["success"]= $result_code == 0;
        if (!$jsonResponse["success"]){
            $jsonResponse["message"]=implode("\n",$output);
        }
        break;
    case 'play':
        if ($data['stream']){
            exec("mpv --input-ipc-server=/tmp/mpvsocket --no-video --audio-device=alsa/default " . escapeshellarg($data['stream']) . " > /dev/null 2>&1 &");
            $jsonResponse["success"]= $result_code == 0;
            if (!$jsonResponse["success"]){
                $jsonResponse["message"]=implode("\n",$output);
            }
        }else {
            $jsonResponse["success"]=false;
            $jsonResponse["message"]="input error";
        }
        break;
    case 'volup':
        $currVolume = getVolume ();
        if (isset($currVolume) && isset($currVolume["vol"])) {
            $increm = $currVolume["vol"] + 20 < 130 ? "20" : 130-$currVolume["vol"];
            $jsonResponse = json_decode(shell_exec('echo \'{"command": ["cycle", "volume", "+'.$increm.'"]}\' | socat - /tmp/mpvsocket'));
        }else {
            $jsonResponse["success"]=false;
            $jsonResponse["message"]=isset($currVolume["error"])? $currVolume["error"] : "Error setting volume";
        }
        break;			
    case 'voldown':
        $currVolume = getVolume ();
        if (isset($currVolume) && isset($currVolume["vol"])){
            $decrem = $currVolume["vol"] - 20 > 0 ? "20" : $currVolume["vol"];
            $jsonResponse = json_decode(shell_exec('echo \'{"command": ["cycle", "volume", "-'.$decrem.'"]}\' | socat - /tmp/mpvsocket'));		
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
