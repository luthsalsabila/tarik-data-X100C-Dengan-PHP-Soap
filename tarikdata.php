<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Tes Tarik Data Mesin Absensi</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background: #f8f9fa;
      font-family: "Segoe UI", sans-serif;
    }
    .card {
      border-radius: 1rem;
      box-shadow: 0 4px 10px rgba(0,0,0,0.08);
    }
    table thead {
      background: #0d6efd;
      color: white;
    }
    table tbody tr:hover {
      background: #f1f5ff;
    }
  </style>
</head>
<body>
  <div class="container py-5">
    <div class="card">
      <div class="card-header bg-primary text-white text-center">
        <h4 class="mb-0">Data Mesin Absensi</h4>
      </div>
      <div class="card-body">
        <!-- Form Pilih Mesin -->
        <form action="" method="GET" class="row g-3 mb-3">
          <div class="col-md-3">
            <label for="finger" class="form-label fw-semibold">Pilih Mesin Fingerprint</label>
            <select name="finger" id="finger" class="form-select" onchange="this.form.submit()">
              <option value="0">-- Pilih Mesin --</option>
              <option value="192.168.1.100" <?= (($_GET['finger'] ?? '')=="192.168.1.100")?"selected":""; ?>>Mesin Finger 1</option>
              <option value="192.168.1.101" <?= (($_GET['finger'] ?? '')=="192.168.1.101")?"selected":""; ?>>Mesin Finger 2</option>
              <option value="192.168.1.102" <?= (($_GET['finger'] ?? '')=="192.168.1.102")?"selected":""; ?>>Mesin Finger 3</option>
              <option value="192.168.1.201" <?= (($_GET['finger'] ?? '')=="192.168.1.201")?"selected":""; ?>>Mesin Finger 4</option>
            </select>
          </div>
          
          <?php
          // form filter hanya muncul kalau sudah pilih mesin
          if (!empty($_GET['finger']) && $_GET['finger'] != "0") {
              $bulanDipilih = $_GET['bulan'] ?? '';
              $tahunDipilih = $_GET['tahun'] ?? '';
              echo '<div class="col-md-3">
                      <label for="filter_id" class="form-label fw-semibold">Filter UserID</label>
                      <select name="filter_id" id="filter_id" class="form-select" onchange="this.form.submit()">
                        <option value="">-- Semua UserID --</option>
                      </select>
                    </div>';

              // Filter bulan
              echo '<div class="col-md-3">
                      <label for="bulan" class="form-label fw-semibold">Filter Bulan</label>
                      <select name="bulan" id="bulan" class="form-select" onchange="this.form.submit()">
                        <option value="">-- Semua Bulan --</option>';
                        for($m=1;$m<=12;$m++){
                          $val = str_pad($m,2,"0",STR_PAD_LEFT);
                          $selected = ($bulanDipilih==$val)?"selected":"";
                          echo "<option value='$val' $selected>".date("F", mktime(0,0,0,$m,10))."</option>";
                        }
              echo '  </select>
                    </div>';

              // Filter tahun
              echo '<div class="col-md-3">
                      <label for="tahun" class="form-label fw-semibold">Filter Tahun</label>
                      <select name="tahun" id="tahun" class="form-select" onchange="this.form.submit()">
                        <option value="">-- Semua Tahun --</option>';
                        $tahunSekarang = date("Y");
                        for($t=$tahunSekarang;$t>=2020;$t--){
                          $selected = ($tahunDipilih==$t)?"selected":"";
                          echo "<option value='$t' $selected>$t</option>";
                        }
              echo '  </select>
                    </div>';
          }
          ?>
        </form>
        <hr>

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

        $IP = $_GET['finger'] ?? '';
        $filter_id = $_GET['filter_id'] ?? '';
        $filter_bulan = $_GET['bulan'] ?? '';
        $filter_tahun = $_GET['tahun'] ?? '';
        $Key = "0";

        if ($IP != "" && $IP != "0") {
            $Connect = @fsockopen($IP, "80", $errno, $errstr, 1);
            $userList = []; // simpan UserID unik
            $rows = [];     // simpan data absensi
            $userNames = []; // simpan data nama
            
            if ($Connect) {
                // --- Ambil Data Absensi ---
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

                // --- Ambil Data User (untuk Nama) ---
                $soap_request = "<GetAllUserInfo><ArgComKey xsi:type=\"xsd:integer\">$Key</ArgComKey><Arg><PIN xsi:type=\"xsd:integer\">All</PIN></Arg></GetUserInfo>";
                fputs($Connect, "POST /iWsService HTTP/1.0" . $newLine);
                fputs($Connect, "Content-Type: text/xml" . $newLine);
                fputs($Connect, "Content-Length:" . strlen($soap_request) . $newLine . $newLine);
                fputs($Connect, $soap_request . $newLine);
                $bufferUser = "";
                while ($Response = fgets($Connect, 1024)) {
                    $bufferUser .= $Response;
                }
                $bufferUser = Parse_Data($bufferUser, "<GetUserInfoResponse>", "</GetUserInfoResponse>");
                $bufferUser = explode("\r\n", $bufferUser);
                for ($a = 1; $a < count($bufferUser) - 1; $a++) {
                    $data = Parse_Data($bufferUser[$a], "<Row>", "</Row>");
                    $PIN  = Parse_Data($data, "<PIN>", "</PIN>");
                    $Name = Parse_Data($data, "<Name>", "</Name>");
                    if ($PIN != "") {
                        $userNames[$PIN] = $Name;
                    }
                }

            } else {
                echo "<p class='text-danger'>Koneksi Gagal ke Mesin Fingerprint</p>";
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

                    if (!in_array($PIN, $userList)) {
                        $userList[] = $PIN; // simpan unique user
                    }

                    // simpan row
                    $rows[] = [
                        "PIN" => $PIN,
                        "DateTime" => $DateTime,
                        "Verified" => $Verified,
                        "Status" => $Status
                    ];
                }

                // render ulang filter dropdown userID
                echo "<script>
                  let filter = document.getElementById('filter_id');
                  if(filter){
                    let userList = " . json_encode($userList) . ";
                    let selected = '" . $filter_id . "';
                    userList.forEach(uid=>{
                      let opt = document.createElement('option');
                      opt.value = uid;
                      opt.text = uid;
                      if(uid === selected) opt.selected = true;
                      filter.appendChild(opt);
                    });
                  }
                </script>";

                // tampilkan tabel
                echo '<div class="table-responsive">
                        <table class="table table-bordered table-striped align-middle text-center">
                          <thead>
                            <tr>
                              <th>UserID</th>
                              <th>Tanggal & Jam</th>
                              <th>Verifikasi</th>
                              <th>Status</th>
                            </tr>
                          </thead>
                          <tbody>';
                foreach ($rows as $r) {
                    $tgl = $r['DateTime']; // format: YYYY-MM-DD hh:mm:ss
                    $tahun = substr($tgl,0,4);
                    $bulan = substr($tgl,5,2);

                    if (
                        ($filter_id == "" || $r['PIN'] == $filter_id) &&
                        ($filter_bulan == "" || $bulan == $filter_bulan) &&
                        ($filter_tahun == "" || $tahun == $filter_tahun)
                    ) {
                        $nama = $userNames[$r['PIN']] ?? "-";
                        echo "<tr>
                                <td>{$r['PIN']}</td>
                                <td>{$r['DateTime']}</td>
                                <td>{$r['Verified']}</td>
                                <td>{$r['Status']}</td>
                              </tr>";
                    }
                }
                echo '</tbody></table></div>';
            }
        }
        ?>
      </div>
    </div>
  </div>
</body>
</html>
