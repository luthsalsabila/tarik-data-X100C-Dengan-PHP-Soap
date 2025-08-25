<?php
function Parse_Data($data, $p1, $p2) {
    $data = " " . $data;
    $hasil = "";
    $awal = strpos($data, $p1);
    if ($awal != "") {
        $akhir = strpos(strstr($data, $p1), $p2);
        if ($akhir != "") {
            $hasil = substr($data, $awal + strlen($p1), $akhir - strlen($p1));
        }
    }
    return $hasil;
}

$IP     = $_GET['finger'] ?? '';
$userid = $_GET['userid'] ?? '';
$bulan  = $_GET['bulan'] ?? '';
$tahun  = $_GET['tahun'] ?? '';
$Key    = "0";

if ($IP != "" && $IP != "0") {
    echo '<div class="table-responsive">
            <table class="table table-bordered table-striped align-middle text-center">
              <thead class="table-dark">
                <tr>
                  <th>UserID</th>
                  <th width="200">Tanggal & Jam</th>
                  <th>Verifikasi</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody>';
    
    $Connect = @fsockopen($IP, "80", $errno, $errstr, 1);
    if ($Connect) {
        $soap_request = "<GetAttLog><ArgComKey xsi:type=\"xsd:integer\">" . $Key . "</ArgComKey><Arg><PIN xsi:type=\"xsd:integer\">All</PIN></Arg></GetAttLog>";
        $newLine = "\r\n";
        fputs($Connect, "POST /iWsService HTTP/1.0" . $newLine);
        fputs($Connect, "Content-Type: text/xml" . $newLine);
        fputs($Connect, "Content-Length:" . strlen($soap_request) . $newLine . $newLine);
        fputs($Connect, $soap_request . $newLine);
        $buffer = "";
        while ($Response = fgets($Connect, 1024)) {
            $buffer .= $Response;
        }
    } else {
        echo "<tr><td colspan='4' class='text-danger'>Koneksi Gagal ke Mesin Fingerprint</td></tr>";
    }

    if (!empty($buffer)) {
        $buffer = Parse_Data($buffer, "<GetAttLogResponse>", "</GetAttLogResponse>");
        $buffer = explode("\r\n", $buffer);
        for ($a = 1; $a < count($buffer) - 1; $a++) {
            $data = Parse_Data($buffer[$a], "<Row>", "</Row>");
            $PIN = Parse_Data($data, "<PIN>", "</PIN>");
            $DateTime = Parse_Data($data, "<DateTime>", "</DateTime>");
            $Verified = Parse_Data($data, "<Verified>", "</Verified>");
            $Status = Parse_Data($data, "<Status>", "</Status>");

            // 🔹 Filter
            $pass = true;
            if ($userid != "" && $PIN != $userid) {
                $pass = false;
            }
            if ($bulan != "" || $tahun != "") {
                $tgl = date_create($DateTime);
                $blnLog = date_format($tgl,"n"); // bulan angka
                $thnLog = date_format($tgl,"Y"); // tahun 4 digit
                if ($bulan != "" && $blnLog != $bulan) $pass = false;
                if ($tahun != "" && $thnLog != $tahun) $pass = false;
            }

            if ($pass) {
                echo "<tr>
                        <td>{$PIN}</td>
                        <td>{$DateTime}</td>
                        <td>{$Verified}</td>
                        <td>{$Status}</td>
                      </tr>";
            }
        }
    }

    echo '</tbody></table></div>';
}
?>
