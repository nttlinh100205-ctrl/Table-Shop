# Nội Thất Tinh Hoa

> Website thương mại điện tử dành cho cửa hàng Nội Thất Tinh Hoa, chuyên giới thiệu và kinh doanh bàn gỗ tự nhiên. Hệ thống cung cấp trải nghiệm mua sắm cho khách hàng cùng khu vực quản trị để vận hành sản phẩm, đơn hàng, khuyến mãi và dịch vụ khách hàng.

## Tổng quan

Ứng dụng được xây dựng theo mô hình Laravel MVC. Khách hàng có thể xem danh mục và chi tiết sản phẩm, chọn biến thể, thêm hàng vào giỏ, áp dụng voucher, thanh toán và theo dõi đơn hàng. Nhân viên quản trị có thể quản lý hoạt động cửa hàng từ trang quản trị.

Một số chức năng đáng chú ý:

- Danh mục nhiều cấp, sản phẩm, biến thể, màu sắc và hình ảnh.
- Giỏ hàng, mã khuyến mãi, đặt hàng, lịch sử đơn và yêu cầu hủy/đổi trả.
- Thanh toán MoMo và thanh toán khi nhận hàng (COD).
- Tích hợp GHN để tra cứu địa chỉ, tính phí vận chuyển và tạo đơn giao hàng.
- Đăng ký, đăng nhập, xác thực email và phân quyền khách hàng/quản trị viên.
- Đánh giá sản phẩm, hỗ trợ khách hàng qua live chat và chatbot tư vấn sản phẩm dùng AI.
- Điểm thành viên, hạng thành viên, voucher đổi điểm, xu điểm danh và vòng quay phần thưởng.
- Khu vực quản trị cho sản phẩm, danh mục, đơn hàng, khách hàng, khuyến mãi, đánh giá, tài chính và báo cáo.
- Phân tích câu hỏi AI để hỗ trợ quản trị hiểu nhu cầu khách hàng.

## Công nghệ

- **Backend:** PHP 8+, Laravel 9.
- **Cơ sở dữ liệu:** MySQL (cấu hình mặc định trong `.env.example`).
- **Giao diện:** Blade, HTML, CSS, JavaScript và Laravel Mix.
- **Tích hợp đang sử dụng:** Brevo HTTP API để gửi email xác thực, Groq API cho chatbot AI, MoMo Sandbox và GHN môi trường thử nghiệm. Cloudinary là tùy chọn cho ảnh sản phẩm.
- **Kiểm thử:** PHPUnit; một số kiểm thử JavaScript dùng Node.js built-in test runner.
- **Triển khai container:** Docker, Nginx và PHP-FPM; cấu hình phù hợp cho Render.

## Yêu cầu

- PHP 8.0 trở lên, Composer và các extension Laravel cần thiết (ví dụ PDO MySQL, Mbstring, OpenSSL, Fileinfo, GD, cURL, XML, ZIP).
- MySQL 8.x hoặc phiên bản tương thích.
- Node.js và npm để biên dịch tài nguyên giao diện.
- Git (khuyến nghị).

> Phiên bản PHP cho môi trường Docker được khai báo trong `Dockerfile` (PHP 8.2). Hãy cài các extension PHP theo yêu cầu Laravel và cấu hình kết nối database phù hợp môi trường của bạn.

## Cài đặt chạy local

### 1. Lấy mã nguồn và cài dependency

```bash
git clone <URL_REPOSITORY>
cd lar_vidu3
composer install
npm install
```

### 2. Tạo cấu hình môi trường

```bash
cp .env.example .env
php artisan key:generate
```

Trên Windows PowerShell, có thể dùng:

```powershell
Copy-Item .env.example .env
php artisan key:generate
```

Mở `.env` và cập nhật ít nhất các giá trị sau cho máy local:

```dotenv
APP_NAME="Noi That Tinh Hoa"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=lar_vidu3
DB_USERNAME=root
DB_PASSWORD=
```

Tạo database `lar_vidu3` trong MySQL trước khi chạy migration. Thay tên database, username và password theo cấu hình máy của bạn.

### 3. Tạo cấu trúc database

```bash
php artisan migrate
```

Nếu muốn nạp dữ liệu mẫu, cấu hình thông tin quản trị trong `.env` rồi chạy seeder:

```dotenv
SEED_ADMIN_NAME="Shop Admin"
SEED_ADMIN_EMAIL=admin@example.com
SEED_ADMIN_PASSWORD=change-this-password
```

```bash
php artisan db:seed
```

Chỉ sử dụng mật khẩu mẫu trong môi trường local. Không commit thông tin đăng nhập hoặc khóa dịch vụ vào Git.

### 4. Biên dịch giao diện và khởi động ứng dụng

```bash
npm run dev
php artisan serve
```

Mở địa chỉ được Laravel in ra, thường là `http://127.0.0.1:8000`. Trong lúc phát triển giao diện, có thể dùng `npm run watch` để tự biên dịch lại tài nguyên khi file thay đổi.

### 5. Queue và tác vụ định kỳ

Email xác thực được đưa vào hàng đợi `emails` và gửi qua Brevo HTTP API khi worker xử lý job. Để thử ở local, đặt `MAIL_QUEUE_CONNECTION=database`, cấu hình `BREVO_API_KEY` và chạy worker trong một terminal riêng:

```bash
php artisan queue:work database --queue=emails,default --tries=3 --timeout=30
```

Ứng dụng có tác vụ hết hạn điểm thành viên chạy hằng ngày lúc 00:05 theo múi giờ Việt Nam. Trên máy chủ cần cấu hình Laravel Scheduler chạy mỗi phút:

```cron
* * * * * cd /path/to/lar_vidu3 && php artisan schedule:run >> /dev/null 2>&1
```

## Cấu hình dịch vụ ngoài

Các dịch vụ ngoài cần thông tin xác thực riêng. Bổ sung biến tương ứng trong `.env` local hoặc phần Environment/Secret của nền tảng triển khai; không ghi khóa thật vào README hay commit `.env`.

### Thanh toán MoMo (Sandbox)

Các biến cấu hình được đọc trong `config/services.php`:

```dotenv
MOMO_ENDPOINT=
MOMO_PARTNER_CODE=
MOMO_ACCESS_KEY=
MOMO_SECRET_KEY=
MOMO_REDIRECT_URL=
MOMO_IPN_URL=
```

Tích hợp hiện dùng môi trường thử nghiệm MoMo, mặc định endpoint là `https://test-payment.momo.vn/v2/gateway/api/create`. Dùng bộ thông tin sandbox do MoMo cấp; giao dịch thử nghiệm không phải giao dịch thu tiền thật. URL callback/IPN phải trỏ đến các route thanh toán của ứng dụng và có thể được MoMo truy cập. Khi chạy local, cần dùng tunnel HTTPS nếu sandbox cần gọi ngược về máy của bạn.

### Giao hàng GHN (môi trường thử nghiệm)

```dotenv
GHN_BASE_URL=
GHN_TOKEN=
GHN_SHOP_ID=
GHN_FROM_NAME=
GHN_FROM_PHONE=
GHN_FROM_ADDRESS=
GHN_FROM_DISTRICT_ID=
GHN_FROM_WARD_CODE=
GHN_DEFAULT_WEIGHT=15000
GHN_MAX_COD_AMOUNT=50000000
```

Tích hợp hiện trỏ đến GHN dev gateway (`https://dev-online-gateway.ghn.vn/shiip/public-api`). Điền token, shop ID và địa chỉ kho thử nghiệm theo tài khoản GHN. Luồng này phục vụ tích hợp/kiểm tra, không nên hiểu là tạo vận đơn giao hàng thật. Dữ liệu tỉnh/thành, quận/huyện và phường/xã được cache trong 24 giờ để hạn chế gọi lại dịch vụ.

### AI chatbot và tư vấn sản phẩm

```dotenv
AI_PROVIDER=groq
GROQ_API_KEY=
GROQ_MODEL=openai/gpt-oss-20b
```

Chatbot hiện dùng Groq (`AI_PROVIDER=groq`) và gửi yêu cầu tới API tương thích OpenAI của Groq. Cấu hình `GROQ_API_KEY` trong môi trường chạy ứng dụng; chọn model bằng `GROQ_MODEL` nếu cần. Không đưa API key vào mã nguồn. Nếu thiếu hoặc sai key, chatbot không thể trả lời bằng AI; phần tìm kiếm sản phẩm trong catalog có thể xử lý một số câu hỏi trực tiếp.

### Email xác thực

Email xác thực được gửi qua Brevo REST API bằng HTTPS, không gửi SMTP trực tiếp từ request đăng ký. Notification được đưa vào queue `emails`; worker lấy job, gọi Brevo và thử lại khi gửi thất bại. Cấu hình:

```dotenv
MAIL_QUEUE_CONNECTION=database
BREVO_API_KEY=
MAIL_API_PROVIDER=brevo
MAIL_FROM_ADDRESS=
MAIL_FROM_NAME="Noi That Tinh Hoa"
```

Địa chỉ gửi phải được xác minh/cấu hình trong Brevo. Cần giữ worker chạy để email trong queue được gửi. Ứng dụng không tự xác thực tài khoản khi chưa cấu hình gửi mail thành công. Trên Render, entrypoint Docker khởi chạy worker `emails` riêng bên cạnh tiến trình web; ở local hãy chạy lệnh queue trong terminal riêng như phần trên.

### Lưu trữ hình ảnh Cloudinary (tùy chọn)

```dotenv
CLOUDINARY_URL=
# Hoặc khai báo riêng:
CLOUDINARY_CLOUD_NAME=
CLOUDINARY_API_KEY=
CLOUDINARY_API_SECRET=
```

Khi chưa cấu hình Cloudinary, ứng dụng có đường lưu dự phòng trong cơ sở dữ liệu. Với môi trường production, nên cấu hình dịch vụ lưu trữ ảnh phù hợp và kiểm tra dung lượng database.

## Docker và triển khai lên Render

Repo có sẵn `Dockerfile`, cấu hình Nginx/PHP-FPM trong `docker/` và health check tại `/up`. Docker image chạy web app bằng Nginx/PHP-FPM, đồng thời entrypoint quản lý các worker nền. Build image để chạy thử local:

```bash
docker build -t lar-vidu3 .
```

Khi chạy container, cần cung cấp tối thiểu `APP_KEY`, `APP_URL` dạng HTTPS công khai và các biến database. Mặc định entrypoint chạy migration (`RUN_MIGRATIONS=true`), tạo storage link, cache route/view và khởi chạy web server. Nó cũng chạy worker riêng cho queue email (`emails`), worker riêng cho phản hồi AI review (`ai-reviews`) và worker `default` nếu `QUEUE_CONNECTION` không phải `sync`.

Các biến triển khai đáng chú ý:

- `RUN_MIGRATIONS=true|false`: bật/tắt chạy migration khi container khởi động.
- `RUN_SEEDERS=true|false`: tùy chọn chạy seeders; cấu hình `SEED_ADMIN_EMAIL` và `SEED_ADMIN_PASSWORD` nếu cần tạo admin.
- `MAIL_QUEUE_CONNECTION=database`: queue connection dành cho email và job AI review.
- `SESSION_DRIVER=database`: khuyến nghị cho môi trường nhiều lần restart/container.
- `SESSION_SECURE_COOKIE=true`: bật cookie session an toàn khi chạy HTTPS.
- `MYSQL_ATTR_SSL_CA`: đường dẫn CA certificate nếu nhà cung cấp MySQL yêu cầu TLS.

### Các bước deploy trên Render

1. Đẩy mã nguồn lên GitHub/Git provider đã kết nối với Render.
2. Trên Render, tạo **New → Web Service**, chọn repository và chọn runtime **Docker** để Render build từ `Dockerfile` trong repo.
3. Thêm các biến môi trường trong mục **Environment**. Tối thiểu gồm:

   ```dotenv
   APP_ENV=production
   APP_DEBUG=false
   APP_KEY=<key được tạo bằng php artisan key:generate>
   APP_URL=https://<ten-dich-vu>.onrender.com
   DB_CONNECTION=mysql
   DB_HOST=<mysql-host>
   DB_PORT=3306
   DB_DATABASE=<database>
   DB_USERNAME=<username>
   DB_PASSWORD=<password>
   SESSION_DRIVER=database
   SESSION_SECURE_COOKIE=true
   QUEUE_CONNECTION=database
   MAIL_QUEUE_CONNECTION=database
   BREVO_API_KEY=<brevo-api-key>
   MAIL_API_PROVIDER=brevo
   MAIL_FROM_ADDRESS=<sender-da-xac-minh-tren-brevo>
   AI_PROVIDER=groq
   GROQ_API_KEY=<groq-api-key>
   ```

   Thêm thông tin thử nghiệm `MOMO_*` và `GHN_*` khi cần kiểm tra thanh toán/vận chuyển. Để nguyên endpoint sandbox/dev của MoMo và GHN trong quá trình thử nghiệm. Không dán giá trị bí mật vào Git hoặc README.
4. Cấu hình health check path là `/up`. Dockerfile đã khai báo cổng từ biến `PORT` và Render cung cấp biến này.
5. Để `RUN_MIGRATIONS=true` (mặc định) cho lần deploy cần tạo/cập nhật schema. Entrypoint chạy `php artisan migrate --force` trước khi mở web. Chỉ bật `RUN_SEEDERS=true` khi thực sự muốn chạy seeder; đặt `SEED_ADMIN_EMAIL` và `SEED_ADMIN_PASSWORD` nếu seeder cần tạo tài khoản quản trị.
6. Deploy và xem log khởi động. Xác nhận ứng dụng trả `200 OK` ở `/up`, kết nối database thành công và log cho biết Brevo/Groq đã được cấu hình. Gửi thử email đăng ký để kiểm tra worker `emails`.

Render filesystem của web service không nên được xem là nơi lưu trữ bền vững. Dùng database MySQL bên ngoài/managed và cấu hình Cloudinary (hoặc dịch vụ object storage) cho ảnh. Nếu MySQL yêu cầu TLS, cung cấp CA certificate qua Secret File và đặt `MYSQL_ATTR_SSL_CA` theo hướng dẫn của Docker entrypoint. Giữ nguyên `APP_KEY` và các thông tin session/database qua những lần deploy để người dùng không bị mất session và dữ liệu.

MoMo và GHN hiện đang ở chế độ thử nghiệm. Trước khi vận hành thật, phải cấu hình thông tin production do từng nhà cung cấp cấp và xác nhận callback, IPN, phí giao hàng, bảo mật TLS cùng quy trình xử lý đơn.

## Kiểm thử

Chạy bộ kiểm thử PHP bằng PHPUnit:

```bash
php artisan test
```

Một số kiểm thử JavaScript có thể chạy bằng Node.js:

```bash
node --test tests/JavaScript/*.test.cjs
```

Các bài kiểm thử bao phủ một số luồng như trạng thái đơn hàng, tính phí/tạo đơn GHN, khuyến mãi, điểm thưởng và tương tác chatbot. Kết quả chạy phụ thuộc cấu hình database và môi trường hiện tại.

## Các route chính

| Nhóm | Đường dẫn tiêu biểu | Chức năng |
|---|---|---|
| Cửa hàng | `/`, `/products/{id}` | Trang chủ và chi tiết sản phẩm |
| Tài khoản | `/login`, `/register`, `/email/verify` | Đăng nhập, đăng ký, xác thực email |
| Giỏ hàng | `/user/cart` | Xem và cập nhật giỏ, áp dụng voucher |
| Đơn hàng | `/user/payment`, `/user/orders` | Thanh toán, lịch sử và chi tiết đơn |
| Thành viên | `/user/points`, `/user/check-in`, `/user/spin` | Điểm, điểm danh, vòng quay thưởng |
| Chat AI | `/ai-chat` | Tư vấn sản phẩm và hỗ trợ tự động |
| Quản trị | `/admin/dashboard` | Quản lý cửa hàng; yêu cầu đăng nhập, xác thực email và quyền admin |
| Health check | `/up` | Kiểm tra ứng dụng còn hoạt động |

Các route thay đổi theo phiên bản; xem `routes/web.php` để biết danh sách đầy đủ.

## Một số quy tắc nghiệp vụ

- Điểm danh tính theo giờ Việt Nam, tối đa một lần mỗi ngày. Một chu kỳ gồm 7 lần điểm danh, không yêu cầu các ngày phải liên tiếp: lần 1–6 nhận 100 xu/lần, lần thứ 7 nhận 200 xu rồi chu kỳ bắt đầu lại.
- Tỷ lệ mặc định là 1 xu = 1 đồng. Khi checkout, khách hàng chọn số xu để trừ trực tiếp vào tiền hàng sau khi áp dụng voucher; xu không giảm phí vận chuyển và đơn vẫn phải còn ít nhất 1.000đ tiền hàng để tương thích cổng thanh toán.
- Xu được ghi nhận vào lịch sử giao dịch. Thao tác điểm danh dùng database transaction và khóa bản ghi người dùng để tránh cộng trùng; đơn bị hủy hợp lệ được hoàn xu đã sử dụng một lần.
- Điểm thành viên được cộng khi đơn hoàn tất; điểm có thời hạn và hạng thành viên được cấu hình trong `config/membership.php`.
- Đơn bị hủy hoặc hoàn trả hoàn tất có cơ chế hoàn xu phù hợp, tránh ghi nhận lặp.
- Email xác thực được xếp vào queue `emails`, rồi worker riêng gọi Brevo HTTP API; request đăng ký không phải chờ Brevo gửi xong.

## Cấu trúc thư mục

```text
app/
  Console/       Artisan commands và scheduler
  Http/          Controllers, middleware, requests
  Models/        Eloquent models
  Services/      Nghiệp vụ, tích hợp GHN/MoMo/AI/email
  Jobs/          Tác vụ chạy nền
  Listeners/     Xử lý events
config/          Cấu hình Laravel và cửa hàng
database/
  migrations/    Cấu trúc và thay đổi database
  seeders/       Dữ liệu khởi tạo
resources/       Blade views, CSS và JavaScript
routes/          Định nghĩa web, API và console routes
tests/            PHPUnit và JavaScript tests
docker/           Cấu hình container, Nginx và entrypoint
```

## Bảo mật và vận hành

- Không commit `.env`, API key, mật khẩu, token GHN/MoMo, hoặc khóa Cloudinary.
- Dùng HTTPS cho môi trường công khai; giữ `APP_DEBUG=false` trong production.
- Giới hạn truy cập `/admin` qua tài khoản admin và bật xác thực email.
- Theo dõi queue bị lỗi bằng `php artisan queue:failed`; thử lại job sau khi sửa nguyên nhân bằng `php artisan queue:retry <ID>`.
- Trước khi deploy migration thay đổi dữ liệu, backup database và xác nhận các biến kết nối production.
- Đặt `APP_URL`, cấu hình mail, session, queue và database đúng với domain/nhà cung cấp triển khai.

## Tài liệu hữu ích trong repo

- `.env.example`: biến môi trường mẫu.
- `config/shop.php`: tên, tagline và thông tin hiển thị của cửa hàng.
- `config/coins.php`, `config/membership.php`: quy tắc xu và hạng thành viên.
- `routes/web.php`: route cửa hàng, người dùng và quản trị.
- `Dockerfile`, `docker/entrypoint.sh`: quy trình build và khởi chạy container.
