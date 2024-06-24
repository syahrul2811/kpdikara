<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Absen Harian</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        #cameraContainer {
            position: relative;
            width: 100%;
            max-width: 600px;
            margin: auto;
            border: 2px solid #ddd;
            border-radius: 10px;
            overflow: hidden;
        }
        #video {
            width: 100%;
            border-radius: 10px;
        }
        .info {
            text-align: center;
            margin: 10px 0;
        }
        .btn-container {
            display: flex;
            justify-content: space-around;
            margin-top: 20px;
        }
        #snapshotContainer {
            display: none;
            text-align: center;
            margin-top: 20px;
        }
        #snapshot {
            max-width: 100%;
            border: 2px solid #ddd;
            border-radius: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="card mt-4">
                    <div class="card-header">
                        <h4 class="card-title">Absen Harian</h4>
                    </div>
                    <div class="card-body">
                        <div id="cameraContainer" class="mb-3">
                            <!-- Video akan ditampilkan di sini -->
                            <video id="video" autoplay></video>
                        </div>
                        <div class="info">
                            <p><strong>Lokasi:</strong> <span id="lokasi">Mengambil lokasi...</span></p>
                        </div>
                        <div class="btn-container">
                            <button id="absenMasukBtn" class="btn btn-primary">absen wajah</button>
                        
                        </div>
                        <div id="snapshotContainer">
                            <h5>Hasil Tangkapan:</h5>
                            <img id="snapshot" alt="Snapshot">
                        </div>
                        <table class="table w-100 mt-4">
                            <thead>
                                <tr>
                                    <th>Status</th>
                                    <th>Tanggal</th>
                                    <th>Absen Masuk</th>
                                    <th>Absen Pulang</th>
                                </tr>
                            </thead>
                            <tbody>
                            <tr>
                            <?php if(is_weekend()): ?>
                                <td class="bg-light text-danger" colspan="4">Hari ini libur. Tidak Perlu absen</td>
                            <?php else: ?>
                                <td><i class="fa fa-3x fa-<?= ($absen < 2) ? "warning text-warning" : "check-circle-o text-success" ?>"></i></td>
                                <td><?= tgl_hari(date('d-m-Y')) ?></td>
                                <td>
                                    <a href="<?= base_url('absensi/absen/masuk') ?>" class="btn btn-primary btn-sm btn-fill"<?= ($absen == 1) ? 'disabled style="cursor:not-allowed"' : '' ?>>Absen Masuk</a>
                                </td>
                                <td>
                                    <a href="<?= base_url('absensi/absen/pulang') ?>" class="btn btn-success btn-sm btn-fill"<?= ($absen !== 1 || $absen == 2) ? 'disabled style="cursor:not-allowed"' : '' ?>>Absen Pulang</a>
                                </td>
                            <?php endif; ?>
                        </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- jQuery (diperlukan untuk plugin JavaScript Bootstrap) -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <!-- Bootstrap JS -->
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <!-- Font Awesome -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/js/all.min.js"></script>

    <script>
        $(document).ready(function() {
            // Fungsi untuk mengakses dan menampilkan kamera
            function aksesKamera() {
                navigator.mediaDevices.getUserMedia({ video: true })
                .then(function(stream) {
                    const videoElem = document.getElementById('video');
                    videoElem.srcObject = stream;
                })
                .catch(function(err) {
                    console.error('Tidak dapat mengakses kamera:', err);
                });
            }

            // Fungsi untuk mengambil lokasi pengguna
            function ambilLokasi() {
                if (navigator.geolocation) {
                    navigator.geolocation.getCurrentPosition(function(position) {
                        const latitude = position.coords.latitude;
                        const longitude = position.coords.longitude;
                        $('#lokasi').text(`Latitude: ${latitude}, Longitude: ${longitude}`);
                    }, function(error) {
                        console.error('Error mendapatkan lokasi:', error);
                        $('#lokasi').text('Gagal mengambil lokasi');
                    });
                } else {
                    $('#lokasi').text('Geolokasi tidak didukung oleh browser ini');
                }
            }

            // Fungsi untuk mengambil dan mengirim foto wajah ke server
function kirimFotoWajah(tipeAbsen) {
    const videoElem = document.getElementById('video');
    const canvas = document.createElement('canvas');
    const context = canvas.getContext('2d');
    canvas.width = videoElem.videoWidth;
    canvas.height = videoElem.videoHeight;
    context.drawImage(videoElem, 0, 0, canvas.width, canvas.height);
    const imageData = canvas.toDataURL('image/jpeg');

    // Menampilkan hasil tangkapan
    const snapshot = document.getElementById('snapshot');
    snapshot.src = imageData;
    $('#snapshotContainer').show();

    // Mengirim gambar yang ditangkap ke server
    $.ajax({
        url: '/path/assets/img/absen', // Ganti dengan URL server Anda
        method: 'POST',
        data: {
            tipe_absen: tipeAbsen,
            foto_wajah: imageData
        },
        success: function(response) {
            // Tampilkan gambar setelah data berhasil dikirim ke server
            snapshot.src = imageData;
            $('#snapshotContainer').show();
            console.log('Foto wajah berhasil dikirim:', response);
        },
        error: function(xhr, status, error) {
            console.error('Terjadi kesalahan:', error);
        }
    });
}

            // Handler untuk klik tombol "Absen Masuk"
            $('#absenMasukBtn').click(function() {
                kirimFotoWajah('masuk');
            });

            // Handler untuk klik tombol "Absen Pulang"
            $('#absenPulangBtn').click(function() {
                kirimFotoWajah('pulang');
            });

            // Akses kamera dan lokasi saat halaman siap
            aksesKamera();
            ambilLokasi();
        });

        // Fungsi untuk menampilkan notifikasi
        function showNotification(message, type) {
            // Hapus notifikasi sebelumnya jika ada
            $('#notification').remove();

            // Buat elemen notifikasi baru
            var notification = $('<div id="notification" class="alert alert-' + type + ' mt-4" role="alert">' + message + '</div>');

            // Tambahkan notifikasi ke dalam dokumen
            $('.card').append(notification);

            // Sembunyikan notifikasi setelah beberapa detik
            setTimeout(function(){
                notification.fadeOut('slow');
            }, 3000);
        }

        $(document).ready(function() {
            // Handler untuk klik tombol "Absen Masuk"
            $('#absenMasukBtn').click(function() {
                // Tampilkan notifikasi ketika tombol diklik
                showNotification('Absen Masuk berhasil!', 'success');
            });

            // Handler untuk klik tombol "Absen Pulang"
            $('#absenPulangBtn').click(function() {
                // Tampilkan notifikasi ketika tombol diklik
                showNotification('Absen Pulang berhasil!', 'success');
            });
        });
    
    </script>
</body>
</html>
