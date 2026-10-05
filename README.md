<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400"></a></p>

<p align="center">
<a href="https://travis-ci.org/laravel/framework"><img src="https://travis-ci.org/laravel/framework.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains over 2000 video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the Laravel [Patreon page](https://patreon.com/taylorotwell).

### Premium Partners

- **[Vehikl](https://vehikl.com/)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Cubet Techno Labs](https://cubettech.com)**
- **[Cyber-Duck](https://cyber-duck.co.uk)**
- **[Many](https://www.many.co.uk)**
- **[Webdock, Fast VPS Hosting](https://www.webdock.io/en)**
- **[DevSquad](https://devsquad.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel/)**
- **[OP.GG](https://op.gg)**
- **[WebReinvent](https://webreinvent.com/?utm_source=laravel&utm_medium=github&utm_campaign=patreon-sponsors)**
- **[Lendio](https://lendio.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).


## Điểm danh, xu và email xác thực

- `/user/check-in`: 7 lần điểm danh không cần liên tiếp, tối đa một lần mỗi ngày theo giờ Việt Nam. Lần 1–6: 100 xu; lần 7: 200 xu; sau đó bắt đầu chu kỳ mới.
- Xu điểm danh tách khỏi điểm thành viên. Mặc định 1 xu = 1đ, cấu hình tại `config/coins.php`. Xu giảm tiền hàng sau voucher, không giảm phí giao hàng; giữ tối thiểu 1.000đ tiền hàng để tương thích thanh toán. Đơn hủy/trả hàng hoàn tất được hoàn xu một lần.
- Chạy `php artisan migrate --force` khi deploy. Docker Render tự chạy nếu `RUN_MIGRATIONS=true`.
- Email xác thực luôn đưa vào queue `emails` trên connection `MAIL_QUEUE_CONNECTION` (mặc định `database`), kể cả khi queue ứng dụng là `sync`. Không tự xác thực tài khoản khi thiếu cấu hình mail.
- Render: đặt `MAIL_QUEUE_CONNECTION=database`, `SESSION_DRIVER=database`, `SESSION_SECURE_COOKIE=true`, `APP_URL` đúng URL HTTPS và giữ nguyên `APP_KEY` qua các lần deploy. Không đặt `SESSION_DOMAIN` sang domain khác.
- Resend: đặt `RESEND_API_KEY`, `MAIL_MAILER=resend`, `MAIL_FROM_ADDRESS` thuộc domain đã xác minh trên Resend. Key riêng không đủ nếu domain gửi chưa được xác minh. Không commit key.
- Docker khởi động worker email riêng tự động. Local/hosting khác cần chạy `php artisan queue:work database --queue=emails --tries=3 --timeout=30`.
- Kiểm tra mail thất bại bằng `php artisan queue:failed` và log của nhà cung cấp. Sau khi sửa cấu hình, có thể thử lại một job cụ thể bằng `php artisan queue:retry ID`.
- 419 là lỗi session/CSRF; gửi mail chậm không tự chứng minh nguyên nhân 419. Luồng mới không chờ HTTP gửi mail trong request đăng ký, dùng session bền vững trên Render và đưa người dùng về form mới nếu token đăng ký/gửi lại email đã hết hạn; CSRF vẫn được kiểm tra.
