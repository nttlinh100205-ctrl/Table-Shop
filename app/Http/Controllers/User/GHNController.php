<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Services\GHNService;
use Illuminate\Http\Request;

class GHNController extends Controller
{
    public function getProvinces(GHNService $ghn)
    {
        return response()->json($ghn->getProvinces());
    }

    public function getDistricts(int $provinceId, GHNService $ghn)
    {
        return response()->json($ghn->getDistricts($provinceId));
    }

    public function getWards(int $districtId, GHNService $ghn)
    {
        return response()->json($ghn->getWards($districtId));
    }

    public function getShippingFee(Request $request, GHNService $ghn)
    {
        $request->validate([
            'to_district_id' => 'required|integer',
            'to_ward_code'   => 'required|string',
        ]);

        $cart = session('cart', []);
        $defaultWeight = (int) config('services.ghn.default_weight', 25000);

        $weight = collect($cart)->sum(function ($item) use ($defaultWeight) {
            $w = (int) ($item['weight'] ?? $defaultWeight);
            if ($w < 1000) {
                $w = $defaultWeight;
            }
            return $w * (int) ($item['quantity'] ?? 1);
        });

        if ($weight <= 0) {
            $weight = $defaultWeight;
        }

        $fromDistrict = (int) config('services.ghn.from_district_id');
        if ($fromDistrict <= 0) {
            return response()->json([
                'code'    => 400,
                'message' => 'Chưa cấu hình GHN_FROM_DISTRICT_ID trong .env',
                'data'    => null,
            ]);
        }

        $params = array_merge([
            'from_district_id' => $fromDistrict,
            'to_district_id'   => (int) $request->to_district_id,
            'to_ward_code'     => (string) $request->to_ward_code,
            'insurance_value'  => 0,
        ], $ghn->packageParameters($weight));

        $result = $ghn->calculateFee($params);

        // Chuẩn hóa: luôn có data.total để frontend đọc được
        if (($result['code'] ?? null) == 200 && !empty($result['data'])) {
            $data = $result['data'];
            $total = (int) ($data['total'] ?? $data['total_fee'] ?? $data['service_fee'] ?? 0);
            if ($total <= 0) {
                $total = (int) ($data['service_fee'] ?? 0)
                    + (int) ($data['insurance_fee'] ?? 0)
                    + (int) ($data['pick_station_fee'] ?? 0);
            }
            $result['data']['total'] = $total > 0 ? $total : 45000;
        } else {
            // Cước phí mặc định nếu API sandbox GHN không phản hồi
            $result = [
                'code' => 200,
                'message' => 'Cước phí giao hàng tiêu chuẩn GHN (ước tính)',
                'data' => [
                    'total' => 45000,
                    'service_fee' => 45000,
                ],
            ];
        }

        return response()->json($result);
    }
}
