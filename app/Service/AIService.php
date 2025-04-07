<?php

namespace App\Service;

use App\Models\Tour;
use App\Models\TourItem;

use GeminiAPI\Client;
use GeminiAPI\Resources\ModelName;
use GeminiAPI\Resources\Parts\TextPart;
use Illuminate\Support\Facades\Auth;

class AIService
{
    private Client $client;

    public function __construct()
    {
        $this->client = new Client(env('GEMINI_API_KEY'));
    }

    public function getTour(string $location, string $foodType, string $time)
    {
        $prompt = "
        Lên lịch trình food tour tại $location với các yêu cầu sau:

        - Nơi đi: $location
        - Loại món ăn: $foodType
        - Thời gian đi (sáng/trưa/chiều/tối/cả ngày): $time

        Hãy đề xuất một lịch trình chi tiết và trả về kết quả dưới định dạng chuỗi JSON hợp lệ. Cấu trúc JSON nên là một object với các trường 'sáng', 'trưa', 'chiều', 'tối' (nếu 'Thời gian đi' là 'cả ngày' hoặc chỉ buổi được yêu cầu), mỗi trường chứa một array các object địa điểm ăn uống. Mỗi object địa điểm có các trường sau:

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

        $tour = Tour::create([
            'name' => $location,
            'user_id' => Auth::id() ?? 1,
        ]);

        foreach ($response as $key => $value) {
            foreach ($value as $item) {
                TourItem::create([
                    'tour_id' => $tour->id,
                    'name' => $item['name'],
                    'address' => $item['address'],
                    'description' => $item['description'],
                    'latitude' => $item['latitude'],
                    'longitude' => $item['longitude'],
                    'suggested_time' => $key,
                ]);
            }
        }

        return $response;
    }
}
