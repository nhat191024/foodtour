<?php

namespace App\Service;

use App\Models\Tour;
use App\Models\TourItem;

use GeminiAPI\Client;
use GeminiAPI\GenerationConfig;
use GeminiAPI\Resources\Parts\TextPart;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AIService
{
    private Client $client;
    public const GEMINI_2_0_FLASH = 'gemini-2.0-flash';

    //* Temperature 0.2 is the best for the AI to not hallucinate
    public const TEMPERATURE = 0.2;

    public function __construct()
    {
        $this->client = new Client(env('GEMINI_API_KEY'));
    }

    // public function

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

         **Chỉ trả về một chuỗi JSON hợp lệ duy nhất, không bao gồm bất kỳ ký tự bao bọc nào như ```json hoặc ```.**
        ";

        $generationConfig = new GenerationConfig();
        $generationConfig = $generationConfig->withTemperature(self::TEMPERATURE);

        $response = $this->client->generativeModel(self::GEMINI_2_0_FLASH)
            ->withGenerationConfig($generationConfig)
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
                'food_type' => $foodType,
                'time' => $time,
                'number_of_days' => $numberOfDays
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
                            'status' => 1,
                        ]);
                    }
                }
            }

            DB::commit();
            //! Query lại qua database bởi vì response từ Gemini không có id của tour_items
            //! gây chút khó khăn khi làm chức năng 'xoá' lịch trình (vào lần đầu tạo tour)
            return $this->getTourById($tour->id);
        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'error' => 'Có lỗi xảy ra khi lưu lịch trình vào cơ sở dữ liệu. (có thể do lỗi định dạng JSON! idk my code suck anyway)',
                'message' => $e->getMessage(),
            ];
        }

        // return $response;
    }

    public function getNewTourItem(TourItem $tourItem)
    {
        $deletedTourItems = TourItem::whereHas('tour', function($query) {
            $query->where('user_id', Auth::id());
        })->where('tour_id', $tourItem->tour_id)
            ->where('status', 0)
            ->get();
        $existingTourItems = TourItem::whereHas('tour', function($query) {
            $query->where('user_id', Auth::id());
        })->where('tour_id', $tourItem->tour_id)
            ->where('status', 1)
            ->get();

        $nearbyLocation = $tourItem->tour->name;
        $foodType = $tourItem->tour->food_type;
        $suggestedTime = $tourItem->tour->time;
        $day = $tourItem->day;
        $deletedTourItemsAsString = $deletedTourItems->map(function ($item) {
            return $item->name . '(' . $item->address . ')';
        })->implode(', ');

        $existingTourItemsAsString = $existingTourItems->map(function ($item) {
            return $item->name . '(' . $item->address . ')';
        })->implode(', ');

        $prompt = "
        Lên lịch trình food tour gần $nearbyLocation với các yêu cầu sau:

        - Nơi đi (Trong phạm vi huyện/tỉnh thành): $nearbyLocation
        - Loại món ăn: $foodType
        - Thời gian đi (sáng/trưa/chiều/tối/cả ngày): $suggestedTime

        Hãy đề xuất một quán ăn đạt tiêu chuẩn trên, trả về kết quả dưới định dạng chuỗi JSON hợp lệ. Cấu trúc JSON phải là một object với các trường sau:

        - 'name': Tên địa điểm ăn uống (string)
        - 'address': Địa chỉ (string)
        - 'latitude': Vĩ độ (number)
        - 'longitude': Kinh độ (number)
        - 'description': Mô tả ngắn (string)
        - 'suggested_time': Buổi gợi ý (string, ví dụ: 'sáng', 'trưa', 'tối')
        - 'food_type': Tên loại món ăn (string, phù hợp với bảng food_types)

        Lưu ý 1: Loại trừ các địa điểm đã bị xóa như sau:
        '''
        $deletedTourItemsAsString
        '''
        Lưu ý 2: Loại trừ các địa điểm đã có sẵn như sau:
        '''
        $existingTourItemsAsString
        '''

         **Chỉ trả về một chuỗi JSON hợp lệ duy nhất, không bao gồm bất kỳ ký tự bao bọc nào như ```json hoặc ```.**
        ";

        // return $prompt;
        $generationConfig = new GenerationConfig();
        $generationConfig = $generationConfig->withTemperature(self::TEMPERATURE);

        $response = $this->client->generativeModel(self::GEMINI_2_0_FLASH)
            ->withGenerationConfig($generationConfig)
            ->generateContent(
                new TextPart($prompt),
                );
        $response = $response->text();
        $response = preg_replace('/^```json\s*|\s*```$/', '', $response);
        $response = json_decode($response, true);

        DB::beginTransaction();
        try {
            $newTourItem = TourItem::create([
                'tour_id' => $tourItem->tour_id,
                'day' => $day,
                'name' => $response['name'],
                'address' => $response['address'],
                'description' => $response['description'],
                'latitude' => $response['latitude'],
                'longitude' => $response['longitude'],
                'suggested_time' => $suggestedTime,
                'status' => 1,
            ]);

            DB::commit();
            return $newTourItem;
        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'error' => 'Có lỗi xảy ra khi tạo địa điểm mới.',
                'message' => $e->getMessage(),
            ];
        }
    }

    public function getTourById(int $tourId)
    {
        $tour = Tour::with(['tourItems' => function ($query) {
            $query->where('status', 1);
        }])->find($tourId);

        if (!$tour) {
            return null;
        }

        $formattedResponse = [];
        foreach ($tour->tourItems->groupBy('day') as $day => $items) {
            $formattedResponse[$day] = [];
            foreach ($items->groupBy('suggested_time') as $time => $timeItems) {
                $formattedResponse[$day][$time] = $timeItems->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'tour_id' => $item->tour_id,
                        'day' => $item->day,
                        'name' => $item->name,
                        'address' => $item->address,
                        'latitude' => (float)$item->latitude,
                        'longitude' => (float)$item->longitude,
                        'description' => $item->description,
                        'suggested_time' => $item->suggested_time,
                        'food_type' => $item->food_type ?? null,
                        'notes' => $item->notes ?? null,
                    ];
                })->toArray();
            }
        }

        return $formattedResponse;
    }
}
