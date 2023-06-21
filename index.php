<?php

// Deklarasi array kosong untuk menampung data berupa array
$daftarLengkapPesananMakanan = [];

// Mengambil data pada file json yang berupa string
// Mengubah datajson kedalam array object(tidak mengisi nilai true di arguumen 2 json_decode()) dan memasukkan kevariabel daftarLengkapPesananMakanan
$datasJson = file_get_contents("data/data.json");
$daftarLengkapPesananMakanan = json_decode($datasJson);

$hargaMakananDipesan = [];
$hargaMinumanDipesan = [];
$hargaPesanan = [];
$pesanan = [];
$kuantiti = [];
// Menerima gabungan kuantiti dari makana dan minuman sehingga berisi array asosiatif
$kuantitiAssosiatif = [];
$hargaKaliKuantiti = [];

// Nilai default untuk menu yang ada merupakan array asosiatif
$daftarMakanan = [
    "Geprek" => 13000,
    "Geprek Jumbo" => 18000,
    "Burger" => 16000,
    "Sosis" => 13000
];
$daftarMinuman = [
    "Teh" => 6000,
    "Jeruk" => 9500,
];


// $datasJson = file_get_contents("data/data.json");
// $daftarLengkapPesananMakanan = json_decode($datasJson);

// Jika ada post dengan nama submit
if (isset($_POST['submit'])) {

    // Apakah user sudah memilih makanan dan minuman
    if (!isset($_POST['makan']) || !isset($_POST['minum'])) {
        // Belum lempar ke halaman awal dari beri pesan gagal
        header("location:index.php?mess=Belum memilih pesanan");
        die;
    }

    // Menangkap semua inputan form ke sebuah variabel
    $nama = $_POST['nama'];
    $pesananMakanan = $_POST['makan'];
    $kuantitiMakanan = $_POST['kuantitiMakan'];
    $pesananMinuman = $_POST['minum'];
    $kuantitiMinuman = $_POST['kuantitiMinum'];

    // Memasukkan array pesanan yang dikirim dari form input makanan dan minuman kemudian menambahkan ke dalam array yang sama
    $pesanan[] = $pesananMakanan;
    $pesanan[] = $pesananMinuman;
    $kuantitiAssosiatif[] = $kuantitiMakanan;
    $kuantitiAssosiatif[] = $kuantitiMinuman;

    // Mengubah array multidimensi kebentuk satu dimensi
    // Atau jika 3 dimensi menjadi 2 dimensi
    $pesanan = array_reduce($pesanan, "array_merge", array());
    $kuantitiAssosiatif = array_reduce($kuantitiAssosiatif, "array_merge", array());


    /**
     * Fungsi cekFormInput
     *  Argumen 1 array pesanan
     *  Argumen 2 array kuantiti terbaru
     *  Argumen 3 array kuantiti terbaru array numerik satu dimensi 
     *  Fungsi mencari kuantiti < 0, menghitung kesamaan total pesanan dengan total jenis kuantiti,
     *  misal pesanan dipilih 5 maka kuantiti juga harus ada 5 yang > 0 
     */
    function cekFormInput($pesanan, $kuantitiAssosiatif, $kuantiti)
    {
        // Mengambil nilai pesanan yang berupa nama pesanan
        foreach ($pesanan as $keyPesan => $valuePesan) {
            // Mengulang sebanyak kuatitiAsosiatif yang ad
            foreach ($kuantitiAssosiatif as $value) {
                // Apakah total kuantiti tidak sama dengan total pesanan
                if (count($kuantiti) != count($pesanan)) {
                    header("location:index.php?mess=Total id pesanan tidak sama dengan total id kuantiti");
                    die;
                }
                // Jika total sama, cek apakah adakah kuantiti yang berindex dari nama pesanan yang < 1
                if ($kuantitiAssosiatif[$valuePesan] <= 0) {
                    header("location:index.php?mess=Id pesanan tidak sama dengan id kuantiti");
                    die;
                }
            }
        }
        // jika lulus pengecekan kembalikan nilai true
        return true;
    }

    /**
     * Fungsi cariHargaPesanan
     * Argumen 1 jenis pesanan makanan / minuman berupa string
     * Argumen 2 daftar makanan / minuman yang berupa array
     * Argumen 3 pesanan makanan / minuman yang berupa array
     * Fungsi mencari harga setiap menu yang dipilih dan memasukkan harganya kedalam array sesuai jenis pesanan
     */
    function cariHargaPesanan($jenis, $daftarPesanan, $pesanans)
    {
        // variabel global 
        global $hargaMakananDipesan, $hargaMinumanDipesan;
        //mengulang daftar makanan atau minuman (array default)
        foreach ($daftarPesanan as $namaPesanan1 => $harga) {
            // mengulang array pesanan dari form input
            foreach ($pesanans as $namaPesanan2) {
                // apakah nama makanan pada daftar pesanan = nama pesanan dari form input 
                if ($namaPesanan1 == $namaPesanan2) {
                    // Masukkan kedalam array sesuai jenis pesanan
                    if ($jenis == "makanan") {
                        $hargaMakananDipesan[] =  [
                            $namaPesanan1 => $harga
                        ];
                    } elseif ($jenis == "minuman") {
                        $hargaMinumanDipesan[] = [
                            $namaPesanan1 => $harga
                        ];
                    }
                }
            }
        }
    }

    /**
     * Fungsi kuantitiPesanan
     * Argument 1 array kuantiti assosiatif
     * Fungsi memasukkan nili kuantiti tiap pesanan kedalam array kuantiti dan mengembalikannilainya
     * Mengecek apakah kuantiti tidak null, tidak kosong atau > 0
     */
    function kuantitiPesanan($kuantitiAssosiatif)
    {
        global $kuantiti;
        foreach ($kuantitiAssosiatif as $key => $hasil) {
            if ($hasil != null || $hasil != "") {
                if ($hasil >= 1) {
                    $kuantiti[] = $hasil;
                } else {
                    header("location:index.php?mess=Kuantiti harus setidaknya 1");
                    die;
                }
            }
        }
        return $kuantiti;
    }

    /**
     * Fungsi hargKaliKuantiti
     * Argument 1 harga pesanan sesuai per jenis menu (makanan, minuman)
     * Argument 2 jumlah pesanan per menu
     * Mencari harga total per menu
     */
    function hargaKaliKuantiti($hargaPesanan, $kuantitiPesanan)
    {
        $hargaPesanan = array_reduce($hargaPesanan, 'array_merge', array());

        foreach ($kuantitiPesanan as $key => $kuantiti) {
            // Karena kuantiti merupakan form input text maka akan mengirim nilai "" jika tidak diisi
            // Maka kondisikan jika kuantiti tidak bernilai null atau kuantiti lebih dari 1
            if ($kuantiti != null || $kuantiti >= 1) {
                // Masukkan ke array nilai harga pesanan per menu x kuantitinya
                $temp[] = [
                    $hargaPesanan[$key] * $kuantiti
                ];
            }
        }
        // Mengubah dan mengembalikan array multidimensi kebentuk satu dimensi
        return array_reduce($temp, 'array_merge', array());
    }

    /**
     * Fungsi diskon
     * Argumen 1 berupa total harga
     * Mengembalikan besaran diskon berdasarkan harga total (11%)
     */
    function diskon($harga)
    {
        if ($harga >= 100000) {
            return $harga * 11 / 100;
        } else {
            return $harga * 0 / 100;
        }
    }

    /**
     * Fungsi aritmatika
     * Argumen 1 berupa string 
     * Argumen 2 berupa integer dan array
     * Untuk menghitung masing-masing perintah berdasarkan argumen 1
     */
    function aritmatika($string, $num)
    {
        switch ($string) {
            case 'total':
                // Menggunakan array_sum()untuk menjumlahkan setiap nilai array numerik
                return array_sum($num);
                break;

            case 'pajak':
                return $num * 9 / 100;
                break;
            case 'diskon':
                return diskon($num);
                break;
            case 'subtotal':
                return $num['total'] + $num['pajak'] - $num['diskon'];
                break;

            default:

                break;
        }
    }

    function kirimData()
    {
        global  $hargaKaliKuantiti,
            $kuantitiMakanan,
            $hargaPesanan,
            $pesanan,
            $hargaMakananDipesan,
            $kuantitiMinuman,
            $hargaMinumanDipesan,
            $daftarLengkapPesananMakanan,
            $nama,
            $kuantiti;


        // Memasukkan pengembalian nilai dari fungsi hargaKaliKuantiti untuk menghitung total harga per menu
        // dengan mengirim 2 parameter harga awal pesanan dan kuantiti pesanan sesuai jenis menu 
        $hargaKaliKuantiti[] = hargaKaliKuantiti($hargaMakananDipesan, $kuantitiMakanan);
        $hargaKaliKuantiti[] = hargaKaliKuantiti($hargaMinumanDipesan, $kuantitiMinuman);
        // Mengubah array multidimensi kebentuk satu dimensi
        $hargaKaliKuantiti  = array_reduce($hargaKaliKuantiti, 'array_merge', array());

        $hargaPesanan[] = $hargaMakananDipesan;
        $hargaPesanan[] = $hargaMinumanDipesan;

        // Mengubah array multidimensi menjadi satu dimensi
        // Karena ini array 3 dimensi maka kita ubah menjadi 2 dimensi terlebih dahaulu 
        $hargaPesanan = array_merge(...$hargaPesanan);
        $hargaPesanan = array_merge(...$hargaPesanan);
        // Mengubah index array string menjadi numerik
        $hargaPesanan = array_values($hargaPesanan);

        // Mnegisi nilai variabel masing masing dengan memanggil fungsi aritmatika yang menerima 2 argumen dan mengembalikan nilai integer
        $totalHarga = aritmatika("total", $hargaKaliKuantiti);
        $pajak = aritmatika("pajak", $totalHarga);
        $diskon = aritmatika("diskon", $totalHarga);
        // Membuat data array yang akan dikirim ke fungsi aritmatika untuk menghitung harga subtotal
        $data = [
            "total" => $totalHarga,
            "pajak" => $pajak,
            "diskon" => $diskon
        ];
        $totalAkhirHarga = aritmatika("subtotal", $data);
        $pesanan = array_values($pesanan);

        // Menambahakan array index terakhir
        $daftarLengkapPesananMakanan[] = [
            "nama" => $nama,
            "pesanan" => $pesanan,
            "harga" => $hargaPesanan,
            "kuantiti" => $kuantiti,
            "harga_kali_kuantiti" => $hargaKaliKuantiti,
            "total_harga" => $totalHarga,
            "pajak" => $pajak,
            "diskon" => $diskon,
            "subtotal" => $totalAkhirHarga
        ];

        // Mengubah araay menjadi string dan memasukkan kedalam file data json
        $dataKeJson = json_encode($daftarLengkapPesananMakanan, JSON_PRETTY_PRINT);
        file_put_contents("data/data.json", $dataKeJson);

        // Redirect data ke halaman index dengan mengirim pesan sukses
        header("location:index.php?mess=success");
    }

    // Mencari harga makanan dan minuman yang dipesan
    cariHargaPesanan("makanan", $daftarMakanan, $pesananMakanan);
    cariHargaPesanan("minuman", $daftarMinuman, $pesananMinuman);
    // Mengambil kuantiti terbaru menggunakan fungsi kuantiti pesanan dengan argument kuantiti asosiatif
    $kuantiti = kuantitiPesanan($kuantitiAssosiatif);

    // Apakah funhsi cekFormInput mengembalikan nilai true
    if (cekFormInput($pesanan, $kuantitiAssosiatif, $kuantiti) == true) {
        kirimData();
    } else {
        // Jika tidak lempar kehalaman awal
        header("location:index.php?mess=Terdapat keslan internal");
        die;
    }
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Makan Yuk</title>
    <link rel="stylesheet" href="library/bootsrap/css/bootstrap.css">
</head>

<body>
    <div class="container">
        <div class="content">
            <div class="header text-center m-5 border-bottom">
                <h1>Makan Yuk</h1>
            </div>
            <div class="menu-makna-minum my-5">
                <div class="img my-3 d-flex justify-content-between">
                    <img style="width: 70px;" src="img/geprek.jpg" alt="">
                    <img style="width: 70px;" src="img/burger.jpg" alt="">
                    <img style="width: 70px;" src="img/sosis.jpg" alt="">
                    <img style="width: 70px;" src="img/teh.png" alt="">
                    <img style="width: 70px;" src="img/jeruk.jpg" alt="">
                </div>
                <div class="table">
                    <table class="table table-bordered border-dark">
                        <thead>
                            <tr class="bg-light">
                                <th scope="col">#</th>
                                <th scope="col">Makanan</th>
                                <th scope="col">Harga</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $i = 1;
                            foreach ($daftarMakanan as $makanan => $harga) : ?>
                                <tr>
                                    <td scope="row"><?= $i++ ?></td>
                                    <td><?= $makanan ?></td>
                                    <td><?= $harga ?></td>
                                </tr>
                            <?php endforeach ?>
                        </tbody>
                        <thead>
                            <tr class="bg-light">
                                <th scope="col">#</th>
                                <th scope="col">Minuman</th>
                                <th scope="col">Harga</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $i = 1;
                            foreach ($daftarMinuman as $minuman => $harga) : ?>
                                <tr>
                                    <td scope="row"><?= $i++ ?></td>
                                    <td><?= $minuman ?></td>
                                    <td><?= $harga ?></td>
                                </tr>
                            <?php endforeach ?>
                            <tr>
                                <th colspan="2">Diskon 11% pembelian 100000 keatas</th>
                                <th>Pajak Ppn 9%</th>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="pesan text-center">
                <?php if (isset($_GET['mess'])) : ?>
                    <?php if ($_GET['mess'] == "success") : ?>
                        <div class="alert alert-success" role="alert">
                            Pesanan berhasil ditambahkan
                        </div>
                    <?php else : ?>
                        <div class="alert alert-danger" role="alert">
                            <?= $_GET['mess'] ?>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
            <form action="" method="post" class="form">
                <div class="form-group mt-3 row">
                    <label for="nama" class="form-label col-3">Nama</label>
                    <input type="text" name="nama" id="nama" class="form-control col" required>
                </div>
                <div class="form-group mt-3 row">
                    <span class="form-label col-3">Makanan</span>
                    <div class="col d-inline">
                        <div class="row my-1">
                            <?php foreach ($daftarMakanan as $makanan => $harga) : ?>
                                <div class="col-3 m-0">
                                    <input class="form-check-input" type="checkbox" value="<?=$makanan?>" name="makan[]" id="<?=$makanan?>">
                                    <label class="form-check-label" for="<?=$makanan?>">
                                        <?= $makanan ?>
                                    </label>
                                </div>
                                <div class="my-2 col-3 d-flex">
                                    <label class="form-check-label px-2" for="<?=$makanan?>Qty">
                                        Kuantiti
                                    </label>
                                    <input type="number" name="kuantitiMakan[<?=$makanan?>]" id="<?=$makanan?>Qty" class="form-control d-flex">
                                </div>
                            <?php endforeach ?>
                        </div>
                    </div>
                </div>
                <div class="form-group mt-3 row">
                    <span class="form-label col-3">Minuman</span>
                    <div class="col d-inline">
                        <div class="row my-1">
                            <div class="col-3 m-0">
                                <input class="form-check-input" type="checkbox" value="Teh" name="minum[Teh]" id="teh">
                                <label class="form-check-label" for="teh">
                                    Teh
                                </label>
                            </div>
                            <div class="col-3 d-flex">
                                <label class="form-check-label px-2" for="teh">
                                    Kuantiti
                                </label>
                                <input type="number" name="kuantitiMinum[Teh]" id="teh" class="form-control d-flex">
                            </div>
                        </div>
                        <div class="row my-1">
                            <div class="col-3">
                                <input class="form-check-input" type="checkbox" value="Jeruk" name="minum[Jeruk]" id="jeruk">
                                <label class="form-check-label" for="jeruk">
                                    Jeruk
                                </label>
                            </div>
                            <div class="col-3 d-flex">
                                <div class="px-2">Kuantiti</div>
                                <input type="number" name="kuantitiMinum[Jeruk]" class="form-control d-flex">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="text-center mt-3">
                    <button type="submit" name="submit" class="btn btn-primary btn-sm px-5 py-2 w-50">Submit</button>
                </div>
            </form>
            <?php if ($daftarLengkapPesananMakanan != "") : ?>
                <table class="table table-bordered mt-5">
                    <thead>
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">Nama</th>
                            <th scope="col">Pesanan</th>
                            <th scope="col">Harga</th>
                            <th scope="col">Kuatiti</th>
                            <th scope="col">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $i = 1;
                        foreach ($daftarLengkapPesananMakanan as $key) : ?>
                            <tr>
                                <th scope="row"><?= $i++ ?></th>
                                <td><?= $key->nama ?></td>
                                <td>
                                    <ol class="list-group">
                                        <?php foreach ($key->pesanan as $pesanan) : ?>
                                            <li class="list-group-item"><?= $pesanan ?></li>
                                        <?php endforeach; ?>
                                    </ol>
                                </td>
                                <td>
                                    <ol class="list-group">
                                        <?php foreach ($key->harga as $pesanan) : ?>
                                            <li class="list-group-item"><?= $pesanan ?>
                                            <?php endforeach; ?>
                                    </ol>
                                </td>
                                <td>
                                    <ol class="list-group ">
                                        <?php foreach ($key->kuantiti as $pesanan) : ?>
                                            <li class="list-group-item text-center"><?= $pesanan ?></li>
                                        <?php endforeach; ?>
                                    </ol>
                                </td>
                                <td>
                                    <ol class="list-group ">
                                        <?php foreach ($key->harga_kali_kuantiti as $pesanan) : ?>
                                            <li class="list-group-item"><?= $pesanan ?></li>
                                        <?php endforeach; ?>
                                    </ol>
                                </td>
                            </tr>
                            <tr>
                                <th colspan="1"></th>
                                <td>Pajak : Rp. <?= $key->pajak ?></td>
                                <td>Diskon : Rp. <?= $key->diskon ?></td>
                                <th colspan="2"></th>
                                <td>Rp.<?= $key->total_harga ?></td>
                            </tr>
                            <tr>
                                <th colspan="1"></th>
                                <th colspan="5" class="">Subtotal : Rp. <?= $key->subtotal ?></th>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif ?>
        </div>
    </div>
</body>

</html>