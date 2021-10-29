# GatewayPayment
Menggunakan Library https://github.com/kamshory/ISO8583-JSON-XML

## Setup
Memerlukan XAMPP yang terinstal Apache dan Mysql. Memerlukan extension Sockets pada PHP. Buat database "pembayaran" lalu import "pembayaran.sql".

## Menjalankan aplikasi
Sebelum menjalankan server, jalankan Apache dan Mysql pada XAMPP

### Run Server
> php server.php

### Run Tester
> php test.php

### Menjalankan Laman Payment Gateway
Jalankan gate.php pada browser dengan menggunakan XAMPP. Laman akan menerima input data-data yang diperlukan untuk melakukan transaksi. Data akan dikirimkan kepada server melalui sockets untuk diolah menjadi pesan berformat ISO 8583. Setelah itu pesan akan dikirimkan kepada tester sebagai simulasi bank. Tester akan mengasumsikan data yang diterima benar dan mengirimkan pesan balasan untuk konfirmasi transaksi. Setelah menerima konfirmasi dari tester, server akan menyimpan data transaksi pada database pembayaran.
