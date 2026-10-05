<?php
namespace App\Services;

use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Str;

class ShopChatSupport
{
    public static function reply(string $message, ?User $user): ?string
    {
        $text = strtolower(Str::ascii($message));
        $orders = preg_match('/\b(don hang|van don|tra cuu don|xem don|kiem tra don)\b/', $text) || preg_match('/^\s*#\d+\s*$/', $text);
        $points = preg_match('/\b(diem thuong|diem tich|diem cua|xu cua|bao nhieu diem|kiem tra diem|xem diem|hang thanh vien|hang muc|hang cua|hang gi|so du xu|bao nhieu xu|diem va hang)\b/', $text);
        $parts = [];
        if ($orders || $points) {
            if (!$user) return 'Bạn vui lòng [đăng nhập]('.route('login').') để xem đơn hàng, điểm, xu và hạng thành viên của mình.';
            if (!$user->hasVerifiedEmail()) return 'Bạn cần [xác thực email]('.route('verification.notice').') trước khi xem thông tin tài khoản.';
            if ($orders) {
                $link = '[Xem đơn hàng của tôi]('.route('user.orders.index').')';
                // Every lookup is scoped to the authenticated owner, including guessed IDs/codes.
                $query = Order::where('user_id', $user->id);
                if (preg_match('/(?:#|\bdon(?: hang)?\s*(?:so|ma)?\s*[:#]?\s*)(\d+)\b/', $text, $match)) {
                    $query->whereKey($match[1]);
                } elseif (preg_match('/\b(?:van don|ghn)\s*[:#]?\s*([a-z0-9]*[0-9][a-z0-9]*)\b/', $text, $match)) {
                    $query->where('ghn_order_code', strtoupper($match[1]));
                } else {
                    $query->latest('id')->limit(3);
                }
                $found = $query->get();
                $parts[] = $found->isEmpty() ? 'Không tìm thấy đơn phù hợp trong tài khoản của bạn. '.$link : $link;
                $statuses = ['pending'=>'Chờ xử lý','confirmed'=>'Đã xác nhận','processing'=>'Đang xử lý','shipping'=>'Đang giao hàng','delivered'=>'Đã giao hàng','completed'=>'Hoàn thành','cancelled'=>'Đã hủy','paid'=>'Đã thanh toán','cancel_requested'=>'Đang yêu cầu hủy'];
                foreach ($found as $order) {
                    $parts[] = '[Đơn #'.$order->id.']('.route('user.orders.show', $order->id).'): '.($statuses[$order->status] ?? 'Xem trạng thái trong chi tiết đơn')
                        .'. '.($order->ghn_order_code ? 'Mã vận đơn: '.$order->ghn_order_code : 'Chưa có mã vận đơn.');
                    $shipping = ['pending'=>'Chờ xử lý','ready_to_pick'=>'Chờ lấy hàng','picking'=>'Đang lấy hàng','picked'=>'Đã lấy hàng','storing'=>'Đang lưu kho','transporting'=>'Đang vận chuyển','sorting'=>'Đang phân loại','delivering'=>'Đang giao hàng','delivered'=>'Đã giao hàng','delivery_fail'=>'Giao hàng chưa thành công','return'=>'Đang hoàn hàng','returned'=>'Đã hoàn hàng','cancel'=>'Đã hủy','cancelled'=>'Đã hủy'];
                    if (isset($shipping[$order->shipping_status])) $parts[] = 'Vận chuyển: '.$shipping[$order->shipping_status].'.';
                }
                $parts[] = 'Bạn có thể nhập “Tra cứu đơn #123” hoặc “Tra cứu vận đơn MÃ_GHN”. Trạng thái trên là thông tin hiện lưu tại shop.';
            }
            if ($points) {
                $user->refresh();
                $tier = $user->tier;
                $parts[] = 'Điểm hiện có: '.number_format($user->points_balance, 0, ',', '.').' điểm. Xu hiện có: '.number_format($user->coin_balance, 0, ',', '.').' xu.';
                $parts[] = 'Hạng hiện tại: '.$tier['name'].'. Tổng điểm tích lũy xét hạng: '.number_format($user->lifetime_points, 0, ',', '.').'.';
                if ($tier['next_tier']) $parts[] = 'Cần thêm '.number_format($tier['points_needed'], 0, ',', '.').' điểm tích lũy để lên '.$tier['next_tier']['name'].'.';
                $parts[] = '[Xem điểm và hạng thành viên]('.route('user.points.index').')';
                $parts[] = 'Đơn hoàn thành nhận 1 điểm mỗi '.number_format(config('membership.earn_rate', 10000), 0, ',', '.').'đ tiền hàng sau giảm giá, không gồm phí vận chuyển; làm tròn xuống. Điểm và xu là hai số dư riêng.';
            }
            // Personal account data is returned directly, never sent to the AI provider or AI history.
            return implode("\n", $parts);
        }
        $faqs = [
            'phi van chuyen va thoi gian giao hang la bao lau?' => 'Phí vận chuyển được tính khi bạn nhập địa chỉ ở bước thanh toán. Thời gian giao phụ thuộc địa chỉ và tình trạng xử lý đơn; bạn có thể chọn Nhân viên để được xác nhận trước khi đặt.',
            'hien shop co chuong trinh khuyen mai hoac ma giam gia nao khong?' => 'Bạn có thể xem voucher của mình và đổi điểm tại [Hồ sơ và ưu đãi]('.route('user.points.index').'). Nhập mã ở bước thanh toán để kiểm tra điều kiện áp dụng.',
            'chinh sach bao hanh va doi tra cua shop nhu the nao?' => 'Điều kiện bảo hành phụ thuộc sản phẩm. Bạn hãy gửi tên sản phẩm và chọn Nhân viên để xác nhận. Với đơn đã mua, mở [Đơn hàng của tôi]('.route('user.orders.index').') để xem chi tiết và các thao tác đổi trả đang được hỗ trợ.',
            'shop co ho tro lap dat tai nha khong?' => 'Bạn cho shop biết sản phẩm và khu vực cần lắp đặt nhé. Chọn Nhân viên để xác nhận dịch vụ và chi phí; mình chưa có thông tin để khẳng định lắp đặt miễn phí.',
            'shop ho tro nhung phuong thuc thanh toan nao?' => 'Shop hỗ trợ COD và MoMo. COD phụ thuộc hạn mức thu hộ; các phương thức khả dụng được hiển thị tại bước thanh toán.',
            'san pham nay hien con hang khong a?' => 'Bạn gửi tên hoặc đường dẫn sản phẩm, kèm biến thể muốn mua nhé. Mình chưa thể xác nhận tồn kho chỉ từ câu hỏi này; bạn cũng có thể chọn Nhân viên để kiểm tra.',
        ];
        return $faqs[trim($text)] ?? null;
    }
}
