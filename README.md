# Website Phim

# Windows

## Yêu cầu dự án

### Cài đặt PHP version 8.1

Dowload file zip của PHP version 8.1 bằng đường link sau 

[Link tải PHP 8.1](https://www.php.net/downloads.php)

Sau đó hãy giải nén chúng ra và cài đặt biến môi trường. 
Tham khảo [link](https://techdecodetutorials.com/how-to-install-php-on-windows-11/) sau để cài biến môi trường. Sau khi hoàn thành các bước trên khởi động lại máy.

Chạy lệnh sau để kiểm tra phiên bản PHP.
```
php --version
```

### Cài đặt Composer
Download file exe composer bằng đường link sau
[Link tải composer windows](https://getcomposer.org/Composer-Setup.exe)

Sau khi cài đặt vào command prompt chạy lệnh sau để kiểm tra composer trên máy

```
composer
```
Sau khi cài xong composer hãy vào dự án mở terminal để cài đặt cho dự án.

Cập nhập composer
```
composer update
```

Chú ý, nếu sảy ra lỗi về require ext-fileinfo * hãy cập nhập lại extension fileinfo trong file php.ini. Để tìm file php.ini chạy lệnh sau

```
php --ini
```
sau đó đến vị trí file php-ini và thêm đoạn code sau vào trong file.
```
extension=php_fileinfo.dll
```

sau đó chạy lại lệnh cập nhập
```
composer update
```

Sau khi cập nhập vào lại terminal của dự án để cài đặt các packages
```
composer install
```
Hệ thống sẽ bắt nhập tài khoản Nova Laravel, đăng nhập để tiếp tục cài đặt

Tiếp đó, tạo một file `.env` nội dung tương tự như `.env.example` và cấu hình môi trường phù hợp


Sau khi tạo file `.env` hãy cấu hình database sao cho phù hợp với mysql trong máy bạn, ví dụ:

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=finnal_project
DB_USERNAME=root
DB_PASSWORD=password
```
* Lưu ý, trước khi thực hiện các bước tiếp theo hãy đảm bảo rằng trong database mysql của bạn có database `finnal_project`, còn không hãy khởi tạo database bằng các truy cập vào database và chạy câu lệnh sau : 

    `CREATE DATABASE finnal_project;`


Nếu là lần đầu tiên khởi chạy dự án, bạn nên chạy câu lệnh sau để tạo table và seed data cho các table đó:

```
php artisan migrate:fresh --seed
```
Nếu xảy ra lỗi hãy kiểm tra lại cấu hình DB trong file `.env` 

Để chạy dự án mở hai terminal trong dự án và chạy lệnh sau

```
php artisan serve
```

Vậy là đã hoàn thiện các bước cài đặt hãy thử và trải nghiệm dự án.