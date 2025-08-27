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

// Fungsi untuk ambil daftar user per mesin
function getUsers($IP, $Key="0") {
    $Connect = @fsockopen($IP, 80, $errno, $errstr, 1);
    $users = [];
    if($Connect){
        $soap_request = "<GetUserInfo><ArgComKey xsi:type=\"xsd:integer\">$Key</ArgComKey><Arg><PIN xsi:type=\"xsd:integer\">All</PIN></Arg></GetUserInfo>";
        $newLine = "\r\n";
        fputs($Connect, "POST /iWsService HTTP/1.0".$newLine);
        fputs($Connect, "Content-Type: text/xml".$newLine);
        fputs($Connect, "Content-Length:".strlen($soap_request).$newLine.$newLine);
        fputs($Connect, $soap_request.$newLine);
        $buffer = "";
        while($Response = fgets($Connect,1024)) { $buffer .= $Response; }
        fclose($Connect);

        $buffer = Parse_Data($buffer,"<GetUserInfoResponse>","</GetUserInfoResponse>");
        $rows = explode("\r\n",$buffer);
        foreach($rows as $row){
            $data = Parse_Data($row,"<Row>","</Row>");
            $PIN  = Parse_Data($data,"<PIN>","</PIN>");
            $Name = Parse_Data($data,"<Name>","</Name>");
            if($PIN) $users[$PIN] = $Name ?: "Tanpa Nama";
        }
    }
    return $users;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Data Mesin Absensi</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { background: #f2f7fb; font-family: "Segoe UI", sans-serif; }
    .card { border-radius: 1rem; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
    table thead { background: #0d6efd; color: white; }
    table tbody tr:hover { background: #f1f5ff; }
    .container { max-width: 1200px; }
    .section-title { font-weight: bold; margin-bottom: 1rem; color: #0d6efd; }
  </style>
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
</head>
<body>
<div class="container py-5">
  <div class="card p-4">
    <h3 class="judul">Data Mesin Absensi</h3>

<style>
.judul {
  display: inline-block;
  padding: 15px 25px;
  background: #0d6efd;
  color: white;
  font-weight: bold;
  border-radius: 50px; /* bikin oval/bola */
  text-align: center;
}
</style>

    
    <!-- Filter -->
    <form id="filterForm" class="row g-3 mb-4">
      <div class="col-md-3">
        <label class="form-label fw-semibold">Pilih Mesin</label>
        <select name="finger" id="finger" class="form-select">
          <option value="192.168.1.100">Mesin Finger 1</option>
          <option value="192.168.1.101">Mesin Finger 2</option>
          <option value="192.168.1.102">Mesin Finger 3</option>
          <option value="192.168.1.201">Mesin Finger 4</option>
        </select>
      </div>
      <div class="col-md-3">
        <label class="form-label fw-semibold">User ID</label>
        <select name="userid" id="userid" class="form-select">
          <option value="">Semua</option>
        </select>
      </div>
      <div class="col-md-2">
        <label class="form-label fw-semibold">Bulan</label>
        <select name="bulan" class="form-select">
          <option value="">Semua</option>
          <?php for($m=1;$m<=12;$m++): ?>
            <option value="<?= $m ?>"><?= date("F", mktime(0,0,0,$m,1)) ?></option>
          <?php endfor; ?>
        </select>
      </div>
      <div class="col-md-2">
        <label class="form-label fw-semibold">Tahun</label>
        <select name="tahun" class="form-select">
          <option value="">Semua</option>
          <?php for($y=date("Y"); $y>=2020; $y--): ?>
            <option value="<?= $y ?>"><?= $y ?></option>
          <?php endfor; ?>
        </select>
      </div>
      <div class="col-md-2 d-flex align-items-end">
        <button type="submit" class="btn btn-success w-100">Tampilkan</button>
      </div>
    </form>

    <!-- Tabel hasil -->
    <div id="tabelHasil" class="mt-3">
      <p class="text-muted text-center">Silakan pilih filter lalu klik <b>Tampilkan</b> untuk melihat data.</p>
    </div>
  </div>
</div>

<script>
$(document).ready(function(){
  function loadUsers(ip){
    $("#userid").html('<option value="">Loading...</option>');
    $.get('proses.php',{finger: ip, mode:'users'}, function(data){
      $("#userid").html(data);
    });
  }

  // Load user list saat pilih mesin
  $("#finger").change(function(){
    loadUsers($(this).val());
  });

  // Submit form dengan AJAX
  $("#filterForm").submit(function(e){
    e.preventDefault();
    $.get('proses.php', $(this).serialize(), function(data){
      $("#tabelHasil").html(data);
    });
  });

  // Load initial user list
  loadUsers($("#finger").val());
});
</script>
</body>
</html>
