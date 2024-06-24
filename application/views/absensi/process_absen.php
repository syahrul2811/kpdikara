<?php
// Fungsi untuk mendapatkan lokasi pengguna
function get_user_location() {
    if (isset($_SERVER['HTTP_CF_IPCOUNTRY'])) {
        return $_SERVER['HTTP_CF_IPCOUNTRY']; // Menggunakan Cloudflare untuk mendapatkan lokasi
    } else {
        return 'Unknown'; // Jika tidak ada informasi lokasi yang tersedia
    }
}

// Simpan lokasi pengguna ke dalam variabel
$user_location = get_user_location();

// Koneksi ke database
$host = "localhost";
$username = "root";
$password = "";
$dbname = "nama_database";

$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Ambil data dari permintaan POST
$tipe_absen = $_POST['tipe_absen'];
$foto_wajah = $_POST['foto_wajah'];

// Dekode data gambar
$foto_wajah = str_replace('data:image/jpeg;base64,', '', $foto_wajah);
$foto_wajah = str_replace(' ', '+', $foto_wajah);
$data = base64_decode($foto_wajah);
$filename = uniqid() . '.jpg';
$file = 'assets/absen/png/' . $filename;

// Simpan file foto
file_put_contents($file, $data);

// Simpan data ke database
$tanggal = date('Y-m-d');
$sql = "INSERT INTO absensi (tipe_absen, foto_wajah, tanggal, lokasi) VALUES ('$tipe_absen', '$filename', '$tanggal', '$user_location')";

if ($conn->query($sql) === TRUE) {
    echo json_encode(["status" => "success", "message" => "Absen berhasil"]);
} else {
    echo json_encode(["status" => "error", "message" => "Error: " . $sql . "<br>" . $conn->error]);
}

$conn->close();
?>
