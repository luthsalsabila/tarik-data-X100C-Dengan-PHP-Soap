<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Tes Tarik Data Mesin Absensi</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container py-4">
  <h2 class="mb-4 text-center">Tarik Data Mesin Absensi</h2>

  <!-- 🔹 Form Filter -->
  <form id="filterForm" class="row g-3 mb-4">
    <div class="col-md-4">
      <label class="form-label fw-semibold">Pilih Mesin Fingerprint</label>
      <select name="finger" id="finger" class="form-select">
        <option value="0">-- Pilih Mesin --</option>
        <option value="192.168.1.100">Mesin Finger 1</option>
        <option value="192.168.1.101">Mesin Finger 2</option>
        <option value="192.168.1.102">Mesin Finger 3</option>
        <option value="192.168.1.201">Mesin Finger 4</option>
      </select>
    </div>
    <div class="col-md-3">
      <label class="form-label fw-semibold">User ID</label>
      <input type="text" id="userid" class="form-control" placeholder="Masukan User ID">
    </div>
    <div class="col-md-2">
      <label class="form-label fw-semibold">Bulan</label>
      <select id="bulan" class="form-select">
        <option value="">Semua</option>
        <?php for($i=1;$i<=12;$i++): ?>
          <option value="<?= $i ?>"><?= date("F", mktime(0,0,0,$i,1)) ?></option>
        <?php endfor; ?>
      </select>
    </div>
    <div class="col-md-2">
      <label class="form-label fw-semibold">Tahun</label>
      <select id="tahun" class="form-select">
        <option value="">Semua</option>
        <?php for($y=date("Y"); $y>=2020; $y--): ?>
          <option value="<?= $y ?>"><?= $y ?></option>
        <?php endfor; ?>
      </select>
    </div>
    <div class="col-md-1 d-flex align-items-end">
      <button type="button" id="btnFilter" class="btn btn-primary w-100">Tampilkan</button>
    </div>
  </form>

  <!-- 🔹 Loader -->
  <div id="loader" class="text-center my-3" style="display:none;">
    <div class="spinner-border text-primary" role="status"></div>
    <p class="mt-2">Mengambil data...</p>
  </div>

  <!-- 🔹 Hasil -->
  <div id="result"></div>
</div>

<script>
const loader = document.getElementById('loader');
const result = document.getElementById('result');

document.getElementById('btnFilter').addEventListener('click', function(){
  let ip = document.getElementById('finger').value;
  let uid = document.getElementById('userid').value;
  let bulan = document.getElementById('bulan').value;
  let tahun = document.getElementById('tahun').value;

  if(ip !== "0"){
    loader.style.display = "block";
    result.innerHTML = "";
    fetch(`proses.php?finger=${ip}&userid=${uid}&bulan=${bulan}&tahun=${tahun}`)
      .then(res => res.text())
      .then(data => {
        loader.style.display = "none";
        result.innerHTML = data;
      })
      .catch(err=>{
        loader.style.display = "none";
        result.innerHTML = "<p class='text-danger'>Gagal ambil data.</p>";
      });
  } else {
    result.innerHTML = "<p class='text-warning'>⚠️ Silakan pilih mesin terlebih dahulu.</p>";
  }
});
</script>

</body>
</html>
