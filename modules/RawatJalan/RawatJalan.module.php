<?php
if(!defined('IS_IN_MODULE')) { die("NO DIRECT FILE ACCESS!"); }
?>

<?php
class RawatJalan {
    function index() {
        global $dataSettings, $date;
       include('modules/RawatJalan/pasien.php');
    }
    function rujuk_internal() {
       include('modules/RawatJalan/rujukan_internal.php');
    }
    function pasien_lanjutan() {
       include('modules/RawatJalan/pasien_lanjutan.php');
    }
}
?>
