<?php

namespace Plugins\Api;

use Systems\SiteModule;

class Site extends SiteModule
{
    public function routes()
    {
        $this->route('api', 'getIndex');
        $this->route('api/apam', 'getApam');
    }

    public function getIndex()
    {
        echo $this->draw('index.html');
        exit();
    }

    public function getApam()
    {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST');
        header("Access-Control-Allow-Headers: X-Requested-With");

        $key = $this->settings->get('api.apam_key');
        $token = trim(isset($_REQUEST['token'])?$_REQUEST['token']:null);
        if($token == $key) {
          $action = isset($_REQUEST['action']) ? $_REQUEST['action'] : "";
          switch($action){
            case "signin":
              $no_rkm_medis = trim($_REQUEST['no_rkm_medis']);
              $no_ktp = trim($_REQUEST['no_ktp']);
              $pasien = $this->db('pasien')->where('no_rkm_medis', $no_rkm_medis)->where('no_ktp', $no_ktp)->oneArray();
              if($pasien) {
                $data['state'] = 'valid';
                $data['no_rkm_medis'] = $pasien['no_rkm_medis'];
              } else {
                $data['state'] = 'invalid';
              }
              echo json_encode($data);
            break;
            case "notifikasi":
              $results = array();
              //$_REQUEST['no_rkm_medis'] = '000009';
              $no_rkm_medis = trim($_REQUEST['no_rkm_medis']);
              $sql = "SELECT * FROM mlite_notifications WHERE no_rkm_medis = '$no_rkm_medis' AND status = 'unread'";
              $query = $this->db()->pdo()->prepare($sql);
              $query->execute();
              $result = $query->fetchAll(\PDO::FETCH_ASSOC);
              foreach ($result as $row) {
                $row['state'] = 'valid';
                $results[] = $row;
              }
              echo json_encode($results);
            break;
            case "notifikasilist":
              $results = array();
              //$_REQUEST['no_rkm_medis'] = '000009';
              $no_rkm_medis = trim($_REQUEST['no_rkm_medis']);
              $sql = "SELECT * FROM mlite_notifications WHERE no_rkm_medis = '$no_rkm_medis'";
              $query = $this->db()->pdo()->prepare($sql);
              $query->execute();
              $result = $query->fetchAll(\PDO::FETCH_ASSOC);
              foreach ($result as $row) {
                $results[] = $row;
              }
              echo json_encode($results);
            break;
            case "tandaisudahdibaca":
              $id = trim($_REQUEST['id']);
              $this->db('mlite_notifications')->where('id', $id)->update('status', 'read');
            break;
            case "tandaisudahdibacasemua":
              $no_rkm_medis = trim($_REQUEST['no_rkm_medis']);
              $this->db('mlite_notifications')->where('no_rkm_medis', $no_rkm_medis)->update('status', 'read');
            break;
            case "notifbooking":
              $data = array();
              //$_REQUEST['no_rkm_medis'] = '000009';
              $date = date('Y-m-d');
              $no_rkm_medis = trim($_REQUEST['no_rkm_medis']);
              $sql = "SELECT stts FROM reg_periksa WHERE tgl_registrasi = '$date' AND no_rkm_medis = '$no_rkm_medis' AND (stts = 'Belum' OR stts = 'Berkas Diterima')";
              $query = $this->db()->pdo()->prepare($sql);
              $query->execute();
              $result = $query->fetchAll(\PDO::FETCH_ASSOC);
              foreach ($result as $row) {
                $results[] = $row;
              }

              if(!$result) {
                $data['state'] = 'invalid';
                echo json_encode($data);
              } else {
                if($results[0]["stts"] == 'Belum') {
                  $data['state'] = 'notifbooking';
                  $data['stts'] = $this->settings->get('api.apam_status_daftar');
                  echo json_encode($data);
                } else if($results[0]["stts"] == 'Berkas Diterima') {
                    $data['state'] = 'notifberkas';
                    $data['stts'] = $this->settings->get('api.apam_status_dilayani');
                    echo json_encode($data);
                } else {
                  $data['state'] = 'invalid';
                  echo json_encode($data);
                }
              }
            break;
            case "antrian":
              $data['state'] = 'valid';
              echo json_encode($data);
            break;
            case "booking":
              $results = array();
              $no_rkm_medis = trim($_REQUEST['no_rkm_medis']);
              $sql = "SELECT a.tanggal_booking, a.tanggal_periksa, a.no_reg, a.status, b.nm_poli, c.nm_dokter, d.png_jawab FROM booking_registrasi a LEFT JOIN poliklinik b ON a.kd_poli = b.kd_poli LEFT JOIN dokter c ON a.kd_dokter = c.kd_dokter LEFT JOIN penjab d ON a.kd_pj = d.kd_pj WHERE a.no_rkm_medis = '$no_rkm_medis' ORDER BY a.tanggal_periksa DESC";
              $query = $this->db()->pdo()->prepare($sql);
              $query->execute();
              $rows = $query->fetchAll(\PDO::FETCH_ASSOC);
              foreach ($rows as $row) {
                $results[] = $row;
              }
              echo json_encode($results);
            break;
            case "lastbooking":
              $data['state'] = 'valid';
              echo json_encode($data);
            break;
            case "bookingdetail":
              $results = array();
              $no_rkm_medis = trim($_REQUEST['no_rkm_medis']);
              $tanggal_periksa = trim($_REQUEST['tanggal_periksa']);
              $no_reg = trim($_REQUEST['no_reg']);
              $sql = "SELECT a.tanggal_booking, a.tanggal_periksa, a.no_reg, a.status, b.nm_poli, c.nm_dokter, d.png_jawab FROM booking_registrasi a LEFT JOIN poliklinik b ON a.kd_poli = b.kd_poli LEFT JOIN dokter c ON a.kd_dokter = c.kd_dokter LEFT JOIN penjab d ON a.kd_pj = d.kd_pj WHERE a.no_rkm_medis = '$no_rkm_medis' AND a.tanggal_periksa = '$tanggal_periksa' AND a.no_reg = '$no_reg'";
              $query = $this->db()->pdo()->prepare($sql);
              $query->execute();
              $rows = $query->fetchAll(\PDO::FETCH_ASSOC);
              foreach ($rows as $row) {
                $results[] = $row;
              }
              echo json_encode($results);
            break;
            case "kamar":
              $results = array();
              $query = $this->db()->pdo()->prepare("SELECT nama.kelas, (SELECT COUNT(*) FROM kamar WHERE kelas=nama.kelas AND statusdata='1') AS total, (SELECT COUNT(*) FROM kamar WHERE  kelas=nama.kelas AND statusdata='1' AND status='ISI') AS isi, (SELECT COUNT(*) FROM kamar WHERE  kelas=nama.kelas AND statusdata='1' AND status='KOSONG') AS kosong FROM (SELECT DISTINCT kelas FROM kamar WHERE statusdata='1') AS nama ORDER BY nama.kelas ASC");
              $query->execute();
              $rows = $query->fetchAll(\PDO::FETCH_ASSOC);
              foreach ($rows as $row) {
                $results[] = $row;
              }
              echo json_encode($results);
            break;
            case "dokter":
              $tanggal = @$_REQUEST['tanggal'];

              if($tanggal) {
                $getTanggal = $tanggal;
              } else {
                $getTanggal = date('Y-m-d');
              }
              $results = array();

              $hari = $this->db()->pdo()->prepare("SELECT DAYNAME('$getTanggal') AS dt");
              $hari->execute();
              $hari = $hari->fetch(\PDO::FETCH_OBJ);

              $namahari = "";
              if($hari->dt == "Sunday"){
                  $namahari = "AKHAD";
              }else if($hari->dt == "Monday"){
                  $namahari = "SENIN";
              }else if($hari->dt == "Tuesday"){
                 	$namahari = "SELASA";
              }else if($hari->dt == "Wednesday"){
                  $namahari = "RABU";
              }else if($hari->dt == "Thursday"){
                  $namahari = "KAMIS";
              }else if($hari->dt == "Friday"){
                  $namahari = "JUMAT";
              }else if($hari->dt == "Saturday"){
                  $namahari = "SABTU";
              }

              $sql = $this->db()->pdo()->prepare("SELECT dokter.nm_dokter, dokter.jk, poliklinik.nm_poli, DATE_FORMAT(jadwal.jam_mulai, '%H:%i') AS jam_mulai, DATE_FORMAT(jadwal.jam_selesai, '%H:%i') AS jam_selesai, dokter.kd_dokter FROM jadwal INNER JOIN dokter INNER JOIN poliklinik on dokter.kd_dokter=jadwal.kd_dokter AND jadwal.kd_poli=poliklinik.kd_poli WHERE jadwal.hari_kerja='$namahari'");
              $sql->execute();
              $result = $sql->fetchAll(\PDO::FETCH_ASSOC);

              if(!$result){
                $send_data['state'] = 'notfound';
                echo json_encode($send_data);
              } else {
                foreach ($result as $row) {
                  $results[] = $row;
                }
                echo json_encode($results);
              }
            break;
            case "riwayat":
              $results = array();
              $no_rkm_medis = trim($_REQUEST['no_rkm_medis']);
              $query = $this->db()->pdo()->prepare("SELECT a.tgl_registrasi, a.no_rawat, a.no_reg, b.nm_poli, c.nm_dokter, d.png_jawab FROM reg_periksa a LEFT JOIN poliklinik b ON a.kd_poli = b.kd_poli LEFT JOIN dokter c ON a.kd_dokter = c.kd_dokter LEFT JOIN penjab d ON a.kd_pj = d.kd_pj WHERE a.no_rkm_medis = '$no_rkm_medis' AND a.stts = 'Sudah' ORDER BY a.tgl_registrasi DESC");
              $query->execute();
              $rows = $query->fetchAll(\PDO::FETCH_ASSOC);
              foreach ($rows as $row) {
                $results[] = $row;
              }
              echo json_encode($results);
            break;
            case "riwayatdetail":
              $results = array();
              $no_rkm_medis = trim($_REQUEST['no_rkm_medis']);
              $tgl_registrasi = trim($_REQUEST['tgl_registrasi']);
              $no_reg = trim($_REQUEST['no_reg']);
              $query = $this->db()->pdo()->prepare("SELECT a.tgl_registrasi, a.no_rawat, a.no_reg, b.nm_poli, c.nm_dokter, d.png_jawab, e.keluhan, e.pemeriksaan, GROUP_CONCAT(DISTINCT g.nm_penyakit SEPARATOR '<br>') AS nm_penyakit, GROUP_CONCAT(DISTINCT i.nama_brng SEPARATOR '<br>') AS nama_brng FROM reg_periksa a LEFT JOIN poliklinik b ON a.kd_poli = b.kd_poli LEFT JOIN dokter c ON a.kd_dokter = c.kd_dokter LEFT JOIN penjab d ON a.kd_pj = d.kd_pj LEFT JOIN pemeriksaan_ralan e ON a.no_rawat = e.no_rawat LEFT JOIN diagnosa_pasien f ON a.no_rawat = f.no_rawat LEFT JOIN penyakit g ON f.kd_penyakit = g.kd_penyakit LEFT JOIN detail_pemberian_obat h ON a.no_rawat = h.no_rawat LEFT JOIN databarang i ON h.kode_brng = i.kode_brng WHERE a.no_rkm_medis = '$no_rkm_medis' AND a.tgl_registrasi = '$tgl_registrasi' AND a.no_reg = '$no_reg' GROUP BY a.no_rawat");
              $query->execute();
              $rows = $query->fetchAll(\PDO::FETCH_ASSOC);
              foreach ($rows as $row) {
                $results[] = $row;
              }
              echo json_encode($results);
            break;
            case "riwayatranap":
              $results = array();
              $no_rkm_medis = trim($_REQUEST['no_rkm_medis']);
              $query = $this->db()->pdo()->prepare("SELECT reg_periksa.tgl_registrasi, reg_periksa.no_reg, dokter.nm_dokter, bangsal.nm_bangsal, penjab.png_jawab, reg_periksa.no_rawat FROM kamar_inap, reg_periksa, pasien, bangsal, kamar, penjab, dokter, dpjp_ranap WHERE kamar_inap.no_rawat = reg_periksa.no_rawat AND reg_periksa.no_rkm_medis = pasien.no_rkm_medis AND kamar_inap.no_rawat = reg_periksa.no_rawat AND kamar_inap.kd_kamar = kamar.kd_kamar AND kamar.kd_bangsal = bangsal.kd_bangsal AND reg_periksa.kd_pj = penjab.kd_pj AND dpjp_ranap.no_rawat = reg_periksa.no_rawat AND dpjp_ranap.kd_dokter = dokter.kd_dokter AND pasien.no_rkm_medis = '$no_rkm_medis' ORDER BY reg_periksa.tgl_registrasi DESC");
              $query->execute();
              $rows = $query->fetchAll(\PDO::FETCH_ASSOC);
              foreach ($rows as $row) {
                $results[] = $row;
              }
              echo json_encode($results);
            break;
            case "riwayatranapdetail":
              $results = array();
              $no_rkm_medis = trim($_REQUEST['no_rkm_medis']);
              $tgl_registrasi = trim($_REQUEST['tgl_registrasi']);
              $no_reg = trim($_REQUEST['no_reg']);
              $sql = "SELECT
                  a.tgl_registrasi,
                  a.no_rawat,
                  a.no_reg,
                  b.nm_bangsal,
                  c.nm_dokter,
                  d.png_jawab,
                  GROUP_CONCAT(DISTINCT e.keluhan SEPARATOR '<br>') AS keluhan,
                  GROUP_CONCAT(DISTINCT e.pemeriksaan SEPARATOR '<br>') AS pemeriksaan,
                  GROUP_CONCAT(DISTINCT g.nm_penyakit SEPARATOR '<br>') AS nm_penyakit,
                  GROUP_CONCAT(DISTINCT i.nama_brng SEPARATOR '<br>') AS nama_brng
                FROM reg_periksa a
                LEFT JOIN kamar_inap j ON a.no_rawat = j.no_rawat
                LEFT JOIN kamar k ON j.kd_kamar = k.kd_kamar
                LEFT JOIN bangsal b ON k.kd_bangsal = b.kd_bangsal
                LEFT JOIN dokter c ON a.kd_dokter = c.kd_dokter
                LEFT JOIN penjab d ON a.kd_pj = d.kd_pj
                LEFT JOIN pemeriksaan_ranap e ON a.no_rawat = e.no_rawat
                LEFT JOIN diagnosa_pasien f ON a.no_rawat = f.no_rawat
                LEFT JOIN penyakit g ON f.kd_penyakit = g.kd_penyakit
                LEFT JOIN detail_pemberian_obat h ON a.no_rawat = h.no_rawat
                LEFT JOIN databarang i ON h.kode_brng = i.kode_brng
                WHERE a.no_rkm_medis = '$no_rkm_medis'
                AND a.tgl_registrasi = '$tgl_registrasi'
                AND a.no_reg = '$no_reg'
                GROUP BY a.no_rawat";
              $query = $this->db()->pdo()->prepare($sql);
              $query->execute();
              $rows = $query->fetchAll(\PDO::FETCH_ASSOC);
              foreach ($rows as $row) {
                $results[] = $row;
              }
              echo json_encode($results);
            break;
            case "billing":
              $results = array();
              //$_REQUEST['no_rkm_medis'] = '000009';
              $no_rkm_medis = trim($_REQUEST['no_rkm_medis']);
              $query = $this->db()->pdo()->prepare("SELECT a.tgl_registrasi, a.no_rawat, a.no_reg, b.nm_poli, c.nm_dokter, d.png_jawab, e.kd_billing, e.jumlah_harus_bayar FROM reg_periksa a LEFT JOIN poliklinik b ON a.kd_poli = b.kd_poli LEFT JOIN dokter c ON a.kd_dokter = c.kd_dokter LEFT JOIN penjab d ON a.kd_pj = d.kd_pj INNER JOIN mlite_billing e ON a.no_rawat = e.no_rawat WHERE a.no_rkm_medis = '$no_rkm_medis' AND a.stts = 'Sudah' ORDER BY e.tgl_billing, e.jam_billing DESC");
              $query->execute();
              $rows = $query->fetchAll(\PDO::FETCH_ASSOC);
              foreach ($rows as $row) {
                $row['total_bayar'] = number_format($row['jumlah_harus_bayar'],2,',','.');
                $results[] = $row;
              }
              echo json_encode($results);
            break;
            case "profil":
              $results = array();
              //$_REQUEST['no_rkm_medis'] = '000009';
              $no_rkm_medis = trim($_REQUEST['no_rkm_medis']);
              $sql = "SELECT * FROM pasien WHERE no_rkm_medis = '$no_rkm_medis'";
              $query = $this->db()->pdo()->prepare($sql);
              $query->execute();
              $rows = $query->fetchAll(\PDO::FETCH_ASSOC);
              foreach ($rows as $row) {
                $personal_pasien = $this->db('personal_pasien')->where('no_rkm_medis', $row['no_rkm_medis'])->oneArray();
                $row['foto'] = 'img/'.$row['jk'].'.png';
                if($personal_pasien) {
                  $row['foto'] = $this->settings->get('api.apam_webappsurl').'/photopasien/'.$personal_pasien['gambar'];
                }
                $results[] = $row;
              }
              echo json_encode($results[0]);
            break;
            case "jadwalklinik":
              $results = array();
              $tanggal = trim($_REQUEST['tanggal']);

              $tentukan_hari=date('D',strtotime($tanggal));
              $day = array(
                'Sun' => 'AKHAD',
                'Mon' => 'SENIN',
                'Tue' => 'SELASA',
                'Wed' => 'RABU',
                'Thu' => 'KAMIS',
                'Fri' => 'JUMAT',
                'Sat' => 'SABTU'
              );
              $hari=$day[$tentukan_hari];

              $sql = "SELECT a.kd_poli, b.nm_poli, DATE_FORMAT(a.jam_mulai, '%H:%i') AS jam_mulai, DATE_FORMAT(a.jam_selesai, '%H:%i') AS jam_selesai FROM jadwal a, poliklinik b, dokter c WHERE a.kd_poli = b.kd_poli AND a.kd_dokter = c.kd_dokter AND a.hari_kerja LIKE '%$hari%' GROUP BY b.kd_poli";

              $query = $this->db()->pdo()->prepare($sql);
              $query->execute();
              $rows = $query->fetchAll(\PDO::FETCH_ASSOC);
              foreach ($rows as $row) {
                $results[] = $row;
              }
              echo json_encode($results);
            break;
            case "jadwaldokter":
              $results = array();
              $tanggal = trim($_REQUEST['tanggal']);
              $kd_poli = trim($_REQUEST['kd_poli']);

              $tentukan_hari=date('D',strtotime($tanggal));
              $day = array(
                'Sun' => 'AKHAD',
                'Mon' => 'SENIN',
                'Tue' => 'SELASA',
                'Wed' => 'RABU',
                'Thu' => 'KAMIS',
                'Fri' => 'JUMAT',
                'Sat' => 'SABTU'
              );
              $hari=$day[$tentukan_hari];

              $sql = "SELECT a.kd_dokter, c.nm_dokter FROM jadwal a, poliklinik b, dokter c WHERE a.kd_poli = b.kd_poli AND a.kd_dokter = c.kd_dokter AND a.kd_poli = '$kd_poli' AND a.hari_kerja LIKE '%$hari%'";

              $query = $this->db()->pdo()->prepare($sql);
              $query->execute();
              $rows = $query->fetchAll(\PDO::FETCH_ASSOC);
              foreach ($rows as $row) {
                $results[] = $row;
              }
              echo json_encode($results);
            break;
            case "carabayar":
              $results = array();
              $sql = "SELECT * FROM penjab";
              $query = $this->db()->pdo()->prepare($sql);
              $query->execute();
              $rows = $query->fetchAll(\PDO::FETCH_ASSOC);
              foreach ($rows as $row) {
                $results[] = $row;
              }
              echo json_encode($results);
            break;
            case "daftar":
              $send_data = array();

              $no_rkm_medis = trim($_REQUEST['no_rkm_medis']);
              $tanggal = trim($_REQUEST['tanggal']);
              $kd_poli = trim($_REQUEST['kd_poli']);
              $kd_dokter = trim($_REQUEST['kd_dokter']);
              $kd_pj = trim($_REQUEST['kd_pj']);

              $tentukan_hari=date('D',strtotime($tanggal));
              $day = array(
                'Sun' => 'AKHAD',
                'Mon' => 'SENIN',
                'Tue' => 'SELASA',
                'Wed' => 'RABU',
                'Thu' => 'KAMIS',
                'Fri' => 'JUMAT',
                'Sat' => 'SABTU'
              );
              $hari=$day[$tentukan_hari];

              $jadwal = $this->db('jadwal')->where('kd_poli', $kd_poli)->where('hari_kerja', $hari)->oneArray();

              $check_kuota = $this->db('booking_registrasi')->select(['count' => 'COUNT(DISTINCT no_reg)'])->where('tanggal_periksa', $tanggal)->oneArray();

              $curr_count = $check_kuota['count'];
              $curr_kuota = $jadwal['kuota'];
              $online = $curr_kuota / $this->settings->get('api.apam_limit');

              $check = $this->db('booking_registrasi')->where('no_rkm_medis', $no_rkm_medis)->where('tanggal_periksa', $tanggal)->oneArray();

              if($curr_count > $online) {
                $send_data['state'] = 'limit';
                echo json_encode($send_data);
              }
              else if(!$check) {
                  $mysql_date = date( 'Y-m-d' );
                  $mysql_time = date( 'H:m:s' );
                  $waktu_kunjungan = $tanggal . ' ' . $mysql_time;

                  $max_id = $this->db('booking_registrasi')->select(['no_reg' => 'ifnull(MAX(CONVERT(RIGHT(no_reg,3),signed)),0)'])->where('kd_poli', $kd_poli)->where('tanggal_periksa', $tanggal)->desc('no_reg')->limit(1)->oneArray();
                  if($this->settings->get('settings.dokter_ralan_per_dokter') == 'true') {
                    $max_id = $this->db('booking_registrasi')->select(['no_reg' => 'ifnull(MAX(CONVERT(RIGHT(no_reg,3),signed)),0)'])->where('kd_poli', $kd_poli)->where('kd_dokter', $kd_dokter)->where('tanggal_periksa', $tanggal)->desc('no_reg')->limit(1)->oneArray();
                  }
                  if(empty($max_id['no_reg'])) {
                    $max_id['no_reg'] = '000';
                  }
                  $no_reg = sprintf('%03s', ($max_id['no_reg'] + 1));

                  unset($_POST);
                  $_POST['no_rkm_medis'] = $no_rkm_medis;
                  $_POST['tanggal_periksa'] = $tanggal;
                  $_POST['kd_poli'] = $kd_poli;
                  $_POST['kd_dokter'] = $kd_dokter;
                  $_POST['kd_pj'] = $kd_pj;
                  $_POST['no_reg'] = $no_reg;
                  $_POST['tanggal_booking'] = $mysql_date;
                  $_POST['jam_booking'] = $mysql_time;
                  $_POST['waktu_kunjungan'] = $waktu_kunjungan;
                  $_POST['limit_reg'] = '1';
                  $_POST['status'] = 'Belum';

                  $this->db('booking_registrasi')->save($_POST);

                  $send_data['state'] = 'success';
                  echo json_encode($send_data);
              }
              else{
                  $send_data['state'] = 'duplication';
                  echo json_encode($send_data);
              }
            break;
            case "sukses":
              $results = array();
              $no_rkm_medis = trim($_REQUEST['no_rkm_medis']);
              $date = date('Y-m-d');
              $sql = "SELECT a.tanggal_booking, a.tanggal_periksa, a.no_reg, a.status, b.nm_poli, c.nm_dokter, d.png_jawab FROM booking_registrasi a LEFT JOIN poliklinik b ON a.kd_poli = b.kd_poli LEFT JOIN dokter c ON a.kd_dokter = c.kd_dokter LEFT JOIN penjab d ON a.kd_pj = d.kd_pj WHERE a.no_rkm_medis = '$no_rkm_medis' AND a.tanggal_booking = '$date' AND a.jam_booking = (SELECT MAX(ax.jam_booking) FROM booking_registrasi ax WHERE ax.tanggal_booking = a.tanggal_booking) ORDER BY a.tanggal_booking ASC LIMIT 1";
              $query = $this->db()->pdo()->prepare($sql);
              $query->execute();
              $rows = $query->fetchAll(\PDO::FETCH_ASSOC);
              foreach ($rows as $row) {
                $results[] = $row;
              }
              echo json_encode($results, JSON_PRETTY_PRINT);
            break;
            case "pengaduan":
              $results = array();
              $petugas_array = explode(',', $this->settings->get('api.apam_normpetugas'));
              $no_rkm_medis = trim($_REQUEST['no_rkm_medis']);
              $sql = "SELECT a.*, b.nm_pasien, b.jk FROM mlite_pengaduan a, pasien b WHERE a.no_rkm_medis = b.no_rkm_medis";
              if(in_array($no_rkm_medis, $petugas_array)) {
                $sql .= "";
              } else {
               $sql .= " AND a.no_rkm_medis = '$no_rkm_medis'";
              }
              $sql .= " ORDER BY a.tanggal";
              $query = $this->db()->pdo()->prepare($sql);
              $query->execute();
              $rows = $query->fetchAll(\PDO::FETCH_ASSOC);
              foreach ($rows as $row) {
                $results[] = $row;
              }
              echo json_encode($results);
            break;
            case "pengaduandetail":
              $results = array();
              $no_rkm_medis = trim($_REQUEST['no_rkm_medis']);
              $pengaduan_id = trim($_REQUEST['pengaduan_id']);
              $sql = $this->db()->pdo()->prepare("SELECT * FROM mlite_pengaduan_detail WHERE pengaduan_id = '$pengaduan_id'");
              $sql->execute();
              $result = $sql->fetchAll(\PDO::FETCH_ASSOC);

              if(!$result) {
                $data['state'] = 'invalid';
                echo json_encode($data);
              } else {
                foreach ($result as $row) {
                  $pasien = $this->db('pasien')->where('no_rkm_medis', $row['no_rkm_medis'])->oneArray();
                  $row['nama'] = $pasien['nm_pasien'];
                  $results[] = $row;
                }
                echo json_encode($results);
              }
            break;
            case "simpanpengaduan":
              $send_data = array();
              $max_id = $this->db('mlite_pengaduan')->select(['id' => 'ifnull(MAX(CONVERT(RIGHT(id,6),signed)),0)'])->like('tanggal', ''.date('Y-m-d').'%')->oneArray();
              if(empty($max_id['id'])) {
                $max_id['id'] = '000000';
              }
              $_next_id = sprintf('%06s', ($max_id['id'] + 1));
              $no_rkm_medis = trim($_REQUEST['no_rkm_medis']);
              $message = trim($_REQUEST['message']);
              unset($_POST);
              $_POST['id'] = date('Ymd').''.$_next_id;
              $_POST['no_rkm_medis'] = $no_rkm_medis;
              $_POST['pesan'] = $message;
              $_POST['tanggal'] = date('Y-m-d H:i:s');

              $this->db('mlite_pengaduan')->save($_POST);

              $send_data['state'] = 'success';
              echo json_encode($send_data);
            break;
            case "simpanpengaduandetail":
              $send_data = array();
              $no_rkm_medis = trim($_REQUEST['no_rkm_medis']);
              $message = trim($_REQUEST['message']);
              $pengaduan_id = trim($_REQUEST['pengaduan_id']);

              unset($_POST);
              $_POST['pengaduan_id'] = $pengaduan_id;
              $_POST['no_rkm_medis'] = $no_rkm_medis;
              $_POST['pesan'] = $message;
              $_POST['tanggal'] = date('Y-m-d H:i:s');
              $this->db('mlite_pengaduan_detail')->save($_POST);

              $send_data['state'] = 'success';
              echo json_encode($send_data);
            break;
            case "cekrujukan":
              $data['state'] = 'valid';
              echo json_encode($data);
            break;
            case "rawatjalan":
              $results = array();
              $sql = "SELECT * FROM poliklinik WHERE status = '1'";
              $query = $this->db()->pdo()->prepare($sql);
              $query->execute();
              $rows = $query->fetchAll(\PDO::FETCH_ASSOC);
              foreach ($rows as $row) {
                $results[] = $row;
              }
              echo json_encode($results);
            break;
            case "rawatinap":
              $results = array();
              $sql = "SELECT bangsal.*, kamar.* FROM bangsal, kamar WHERE kamar.statusdata = '1' AND bangsal.kd_bangsal = kamar.kd_bangsal";
              $query = $this->db()->pdo()->prepare($sql);
              $query->execute();
              $rows = $query->fetchAll(\PDO::FETCH_ASSOC);
              foreach ($rows as $row) {
                $results[] = $row;
              }
              echo json_encode($results);
            break;
            case "laboratorium":
              $results = array();
              $sql = "SELECT * FROM jns_perawatan_lab WHERE status = '1'";
              $query = $this->db()->pdo()->prepare($sql);
              $query->execute();
              $rows = $query->fetchAll(\PDO::FETCH_ASSOC);
              foreach ($rows as $row) {
                $results[] = $row;
              }
              echo json_encode($results);
            break;
            case "radiologi":
              $results = array();
              $sql = "SELECT * FROM jns_perawatan_radiologi WHERE status = '1'";
              $query = $this->db()->pdo()->prepare($sql);
              $query->execute();
              $rows = $query->fetchAll(\PDO::FETCH_ASSOC);
              foreach ($rows as $row) {
                $results[] = $row;
              }
              echo json_encode($results);
            break;
            case "hitungralan":
              //$_REQUEST['no_rkm_medis'] = '000009';
              $no_rkm_medis = trim($_REQUEST['no_rkm_medis']);
              $hitung = $this->db('reg_periksa')->select(['count' => 'COUNT(DISTINCT no_rawat)'])->where('no_rkm_medis', $no_rkm_medis)->oneArray();
              echo $hitung['count'];
            break;
            case "hitungranap":
              //$_REQUEST['no_rkm_medis'] = '000009';
              $no_rkm_medis = trim($_REQUEST['no_rkm_medis']);
              $hitung = $this->db('kamar_inap')->select(['count' => 'COUNT(DISTINCT kamar_inap.no_rawat)'])->join('reg_periksa', 'reg_periksa.no_rawat=kamar_inap.no_rawat')->where('no_rkm_medis', $no_rkm_medis)->oneArray();
              echo $hitung['count'];
            break;
            case "layananunggulan":
              $data[] = array_column($this->db('mlite_settings')->where('module', 'website')->toArray(), 'value', 'field');
              echo json_encode($data);
            break;
            case "lastblog":
              $limit = $this->settings->get('blog.latestPostsCount');
              $results = [];
              $rows = $this->db('mlite_blog')
                      ->leftJoin('mlite_users', 'mlite_users.id = mlite_blog.user_id')
                      ->where('status', 2)
                      ->where('published_at', '<=', time())
                      ->desc('published_at')
                      ->limit($limit)
                      ->select(['mlite_blog.id', 'mlite_blog.title', 'mlite_blog.cover_photo', 'mlite_blog.published_at', 'mlite_blog.slug', 'mlite_blog.intro', 'mlite_blog.content', 'mlite_users.username', 'mlite_users.fullname'])
                      ->toArray();

              foreach ($rows as &$row) {
                  //$this->filterRecord($row);
                  $tags = $this->db('mlite_blog_tags')
                      ->leftJoin('mlite_blog_tags_relationship', 'mlite_blog_tags.id = mlite_blog_tags_relationship.tag_id')
                      ->where('mlite_blog_tags_relationship.blog_id', $row['id'])
                      ->select('name')
                      ->oneArray();
                  $row['tag'] = $tags['name'];
                  $row['tanggal'] = getDayIndonesia(date('Y-m-d', date($row['published_at']))).', '.dateIndonesia(date('Y-m-d', date($row['published_at'])));
                  $results[] = $row;
              }
              echo json_encode($results);
            break;
            case "blog":
              $results = [];
              $rows = $this->db('mlite_blog')
                      ->leftJoin('mlite_users', 'mlite_users.id = mlite_blog.user_id')
                      ->where('status', 2)
                      ->where('published_at', '<=', time())
                      ->desc('published_at')
                      ->select(['mlite_blog.id', 'mlite_blog.title', 'mlite_blog.cover_photo', 'mlite_blog.published_at', 'mlite_blog.slug', 'mlite_blog.intro', 'mlite_blog.content', 'mlite_users.username', 'mlite_users.fullname'])
                      ->toArray();

              foreach ($rows as &$row) {
                  //$this->filterRecord($row);
                  $tags = $this->db('mlite_blog_tags')
                      ->leftJoin('mlite_blog_tags_relationship', 'mlite_blog_tags.id = mlite_blog_tags_relationship.tag_id')
                      ->where('mlite_blog_tags_relationship.blog_id', $row['id'])
                      ->select('name')
                      ->oneArray();
                  $row['tag'] = $tags['name'];
                  $row['tanggal'] = getDayIndonesia(date('Y-m-d', date($row['published_at']))).', '.dateIndonesia(date('Y-m-d', date($row['published_at'])));
                  $results[] = $row;
              }
              echo json_encode($results);
            break;
            case "blogdetail":
              $id = trim($_REQUEST['id']);
              $results = [];
              $rows = $this->db('mlite_blog')
                      ->where('id', $id)
                      ->select(['id','title','cover_photo', 'content', 'published_at'])
                      ->oneArray();
              $rows['tanggal'] = getDayIndonesia(date('Y-m-d', date($rows['published_at']))).', '.dateIndonesia(date('Y-m-d', date($rows['published_at'])));
              $results[] = $rows;
              echo json_encode($results);
            break;
            default:
              echo 'Default';
            break;
          }
        } else {
        	echo 'Error';
        }
        exit();
    }

}
