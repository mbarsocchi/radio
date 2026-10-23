<?php
function clientInSameSubnet() {
    if ((substr($_SERVER['REMOTE_ADDR'],0,8) == "192.168.") || ($_SERVER['REMOTE_ADDR'] == "127.0.0.1")) {
		$result =true;
	} else{
		$result =false;
	} 
	return $result;
}

function getStreamPlaying(){
    $isProcessRunning = shell_exec("pgrep mpv") !== null;
    if ($isProcessRunning) {
        $jsonResponse = shell_exec("echo '{\"command\": [\"get_property\", \"path\"]}' | socat - /tmp/mpvsocket");
        if ($jsonResponse !== null) {
            $data = json_decode($jsonResponse, true);
            if (isset($data['error']) && $data['error'] === 'success' && !empty($data['data'])) {
                $res['isPlaying']=true;
                $fullUrl = $data['data'];
                $parsedUrl = parse_url($fullUrl);
                if (isset($parsedUrl['host'])) {
                    $host = $parsedUrl['host'];
                    $res['streamUrl'] = $fullUrl;
                } else {
                    $jsonResponse["success"]=true;
                    $res['message'] = "Il file in riproduzione non sembra essere uno streaming di rete (Path: " . $fullUrl . ")";
                }
            } else {
                $res['isPlaying']=false; 
                $jsonResponse["success"]=false;
                $res['message'] =  "Nessun file o streaming in riproduzione al momento.";
            }
        } else {
            $jsonResponse["success"]=false;
            $res['message'] =  "Impossibile connettersi a mpv. Assicurati che il socket sia attivo.";
        }
    }else {
       $res['isPlaying']=false; 
    }    
    return $res;
}

function getAllRadios(){
    $radioStations = glob(__DIR__.DIRECTORY_SEPARATOR.'stations'.DIRECTORY_SEPARATOR.'*');
    foreach ($radioStations as $station) {
        if ($station[0] == '.'){
            continue;
        }            
        $txtFile = fopen($station,"r");
        if (!$txtFile) {
            $r["success"]=false;
            $r["message"]='fopen failed. reason: '. $php_errormsg;
        }else {
            $r[basename($station)] = fgets($txtFile);
            fclose($txtFile);
        }           
    }
    uksort($r, 'strcasecmp');
    return $r;
}