<?php

namespace App\Service;

use App\Models\Tour;
use App\Models\TourItem;


use GeminiAPI\Client;
use GeminiAPI\Resources\ModelName;
use GeminiAPI\Resources\Parts\TextPart;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AIService
{
    private Client $client;

    public function __construct()
    {
        $this->client = new Client(env('GEMINI_API_KEY'));
    }

    public function getTour(string $location, string $foodType, string $time, int $numberOfDays)
    {
        $prompt = "
        Lên lịch trình food tour tại $location với các yêu cầu sau:

        - Nơi đi: $location
        - Loại món ăn: $foodType
        - Thời gian đi (sáng/trưa/chiều/tối/cả ngày): $time
        - Số ngày đi: $numberOfDays

        Hãy đề xuất một lịch trình chi tiết cho **$numberOfDays ngày**, trả về kết quả dưới định dạng chuỗi JSON hợp lệ. Cấu trúc JSON nên là một object với các trường tương ứng với từng ngày (ví dụ: 'ngày 1', 'ngày 2', ...), mỗi trường chứa một object với các trường 'sáng', 'trưa', 'chiều', 'tối', mỗi trường này lại chứa một array các object địa điểm ăn uống. Mỗi object địa điểm có các trường sau:

        - 'name': Tên địa điểm ăn uống (string)
        - 'address': Địa chỉ (string)
        - 'latitude': Vĩ độ (number)
        - 'longitude': Kinh độ (number)
        - 'description': Mô tả ngắn (string)
        - 'suggested_time': Buổi gợi ý (string, ví dụ: 'sáng', 'trưa', 'tối')
        - 'food_type': Tên loại món ăn (string, phù hợp với bảng food_types)
        - 'notes': Ghi chú (string hoặc null)

         **Chỉ trả về một chuỗi JSON hợp lệ duy nhất, không bao gồm bất kỳ ký tự bao bọc nào như ```json hoặc ```.**
        ";

        $response = $this->client->generativeModel(ModelName::GEMINI_2_0_FLASH)
            ->generateContent(
                new TextPart($prompt),
            );
        $response = $response->text();
        $response = preg_replace('/^```json\s*|\s*```$/', '', $response);
        $response = json_decode($response, true);

        DB::beginTransaction();
        try {
            $tour = Tour::create([
                'name' => $location,
                'user_id' => Auth::id() ?? 1,
            ]);

            foreach ($response as $day => $times) {
                foreach ($times as $time => $items) {
                    foreach ($items as $item) {
                        TourItem::create([
                            'tour_id' => $tour->id,
                            'day' => $day,
                            'name' => $item['name'],
                            'address' => $item['address'],
                            'description' => $item['description'],
                            'latitude' => $item['latitude'],
                            'longitude' => $item['longitude'],
                            'suggested_time' => $time,
                        ]);
                    }
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'error' => 'Có lỗi xảy ra khi lưu lịch trình vào cơ sở dữ liệu. (có thể do lỗi định dạng JSON! idk my code suck anyway)',
                'message' => $e->getMessage(),
            ];
        }

        return $response;
    }
}
