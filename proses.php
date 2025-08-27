<?php
function Parse_Data($data, $p1, $p2){
    $data = " ".$data; $hasil = "";
    $awal = strpos($data,$p1);
    if($awal!=""){
        $akhir = strpos(strstr($data,$p1),$p2);
        if($akhir!="") $hasil = substr($data,$awal+strlen($p1),$akhir-strlen($p1));
    }
    return $hasil;
}

$IP     = $_GET['finger'] ?? '';
$userid = $_GET['userid'] ?? '';
$bulan  = $_GET['bulan'] ?? '';
$tahun  = $_GET['tahun'] ?? '';
$Key    = "0";

if(isset($_GET['mode']) && $_GET['mode']=='users'){
    // Untuk dropdown user per mesin
    $Connect = @fsockopen($IP,80,$errno,$errstr,1);
    $options = "<option value=''>Semua</option>"; // ✅ Tambahin opsi Semua
    if($Connect){
        $soap_request = "<GetUserInfo><ArgComKey xsi:type=\"xsd:integer\">$Key</ArgComKey><Arg><PIN xsi:type=\"xsd:integer\">All</PIN></Arg></GetUserInfo>";
        $newLine="\r\n";
        fputs($Connect,"POST /iWsService HTTP/1.0".$newLine);
        fputs($Connect,"Content-Type: text/xml".$newLine);
        fputs($Connect,"Content-Length:".strlen($soap_request).$newLine.$newLine);
        fputs($Connect,$soap_request.$newLine);
        $buffer=""; while($Response=fgets($Connect,1024)) $buffer.=$Response;
        fclose($Connect);

        $buffer = Parse_Data($buffer,"<GetUserInfoResponse>","</GetUserInfoResponse>");
        $rows = explode("\r\n",$buffer);
        foreach($rows as $row){
            $data = Parse_Data($row,"<Row>","</Row>");
            $PIN  = Parse_Data($data,"<PIN>","</PIN>");
            $Name = Parse_Data($data,"<Name>","</Name>");
            if($PIN) $options .= "<option value='$PIN'>$PIN - $Name</option>";
        }
    }
    echo $options;
    exit;
}

// Ambil data absensi
if($IP!="" && $IP!="0"){
    echo '<div class="table-responsive">
            <table class="table table-bordered table-striped align-middle text-center">
            <thead class="table-dark"><tr>
              <th>UserID</th>
              <th>Nama</th>
              <th>Tanggal & Jam</th>
              <th>Verifikasi</th>
              <th>Status</th>
            </tr></thead><tbody>';

    // 🔹 Ambil data user untuk mapping PIN → Nama
    $userNames = [];
    $ConnectUser = @fsockopen($IP,80,$errno,$errstr,1);
    if($ConnectUser){
        $soap_request = "<GetUserInfo><ArgComKey xsi:type=\"xsd:integer\">$Key</ArgComKey><Arg><PIN xsi:type=\"xsd:integer\">All</PIN></Arg></GetUserInfo>";
        $newLine="\r\n";
        fputs($ConnectUser,"POST /iWsService HTTP/1.0".$newLine);
        fputs($ConnectUser,"Content-Type: text/xml".$newLine);
        fputs($ConnectUser,"Content-Length:".strlen($soap_request).$newLine.$newLine);
        fputs($ConnectUser,$soap_request.$newLine);
        $bufferUser=""; while($Response=fgets($ConnectUser,1024)) $bufferUser.=$Response;
        fclose($ConnectUser);

        $bufferUser = Parse_Data($bufferUser,"<GetUserInfoResponse>","</GetUserInfoResponse>");
        $rowsUser = explode("\r\n",$bufferUser);
        foreach($rowsUser as $row){
            $data = Parse_Data($row,"<Row>","</Row>");
            $PIN  = Parse_Data($data,"<PIN>","</PIN>");
            $Name = Parse_Data($data,"<Name>","</Name>");
            if($PIN) $userNames[$PIN] = $Name;
        }
    }

    // 🔹 Ambil log absensi
    $Connect = @fsockopen($IP,80,$errno,$errstr,1);
    if($Connect){
        $soap_request = "<GetAttLog><ArgComKey xsi:type=\"xsd:integer\">$Key</ArgComKey><Arg><PIN xsi:type=\"xsd:integer\">All</PIN></Arg></GetAttLog>";
        $newLine="\r\n";
        fputs($Connect,"POST /iWsService HTTP/1.0".$newLine);
        fputs($Connect,"Content-Type: text/xml".$newLine);
        fputs($Connect,"Content-Length:".strlen($soap_request).$newLine.$newLine);
        fputs($Connect,$soap_request.$newLine);
        $buffer=""; while($Response=fgets($Connect,1024)) $buffer.=$Response;
    } else {
        echo "<tr><td colspan='5' class='text-danger'>Koneksi Gagal ke Mesin Fingerprint</td></tr>";
    }

    if(!empty($buffer)){
        $buffer = Parse_Data($buffer,"<GetAttLogResponse>","</GetAttLogResponse>");
        $rows = explode("\r\n",$buffer);
        foreach($rows as $row){
            $data = Parse_Data($row,"<Row>","</Row>");
            $PIN = Parse_Data($data,"<PIN>","</PIN>");
            $DateTime = Parse_Data($data,"<DateTime>","</DateTime>");
            $Verified = Parse_Data($data,"<Verified>","</Verified>");
            $Status = Parse_Data($data,"<Status>","</Status>");

            // 🔹 Ambil nama dari mapping
            $Name = $userNames[$PIN] ?? "";

            $pass = true;
            if($userid!="" && $PIN!=$userid) $pass=false;
            if($bulan!="" || $tahun!=""){
                $tgl=date_create($DateTime);
                $blnLog=date_format($tgl,"n");
                $thnLog=date_format($tgl,"Y");
                if($bulan!="" && $blnLog!=$bulan) $pass=false;
                if($tahun!="" && $thnLog!=$tahun) $pass=false;
            }

            if($pass){
                echo "<tr>
                        <td>{$PIN}</td>
                        <td>{$Name}</td>
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
