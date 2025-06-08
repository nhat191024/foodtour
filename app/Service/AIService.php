<?php

namespace App\Service;

use Gemini;
use App\Models\Tour;

// use GeminiAPI\Client;
// use GeminiAPI\GenerationConfig;
// use GeminiAPI\Resources\Parts\TextPart;

use App\Models\TourItem;

use Gemini\Data\Schema;
use Gemini\GeminiHelper;
use Gemini\Enums\DataType;
use Gemini\Enums\ModelVariation;
use Gemini\Data\GenerationConfig;
use Gemini\Enums\ResponseMimeType;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class AIService
{
    private Gemini\Client $client;

    public function __construct()
    {
        $this->client = Gemini::client(env('GEMINI_API_KEY'));
    }

    // public function

    public function getTour(string $location, string $foodType, string $time, int $numberOfDays, array $weather = [])
    {
        $prompt = "
        Tạo một lịch trình du lịch ẩm thực và tham quan chi tiết tại $location với các yêu cầu sau:
        - Địa điểm: $location
        - Loại ẩm thực chính cho chuyến đi: $foodType (áp dụng cho các địa điểm ăn uống được gợi ý).
        - Các địa điểm tham quan nên ở gần các địa điểm ăn uống, không quá xa.
        - Thông tin thời tiết cho khu vực này: " . json_encode($weather, JSON_UNESCAPED_UNICODE) . ". Hãy sử dụng thông tin này để gợi ý các địa điểm ăn uống và tham quan phù hợp với thời tiết.
        - Thời gian trong ngày bạn muốn tập trung: $time (Có thể là 'morning', 'lunch', 'afternoon', 'evening', hoặc 'full day'). Nếu là 'full day', hãy bao gồm tất cả các khung thời gian sáng, trưa, chiều, tối.
        - Số ngày: $numberOfDays

        **Đối với mỗi khung thời gian (morning, lunch, afternoon, evening) trong mỗi ngày, hãy gợi ý ít nhất một địa điểm. Mỗi địa điểm phải có các thông tin sau:**
        - **'type'**: Phải là 'food' (cho địa điểm ăn uống) hoặc 'sightseeing' (cho địa điểm tham quan).
        - **'name'**: Tên của địa điểm.
        - **'address'**: Địa chỉ cụ thể.
        - **'latitude'**: Vĩ độ. Nếu không có giá trị chính xác, hãy cung cấp một số ước tính hợp lý.
        - **'longitude'**: Kinh độ. Nếu không có giá trị chính xác, hãy cung cấp một số ước tính hợp lý.
        - **'description'**: Mô tả chi tiết về địa điểm và lý do gợi ý (ví dụ: món ăn đặc trưng, điểm nổi bật của địa điểm tham quan).
        - **'food_type'**: Chỉ bao gồm trường này và gán giá trị '$foodType' nếu 'type' là 'food'. Không bao gồm trường này nếu 'type' là 'sightseeing'.

        **Quan trọng: Đầu ra phải là một cấu trúc JSON hoàn chỉnh và bằng tiếng Việt.**
        ";

        $response = $this->client
            ->generativeModel(
                model: GeminiHelper::generateGeminiModel(
                    variation: ModelVariation::FLASH,
                    generation: 2.5,
                    version: "preview-05-20"
                ),
            )
            ->withGenerationConfig(
                generationConfig: new GenerationConfig(
                    responseMimeType: ResponseMimeType::APPLICATION_JSON,
                    responseSchema: new Schema(
                        type: DataType::OBJECT,
                        properties: [
                            'days' => new Schema(
                                type: DataType::ARRAY,
                                description: 'Danh sách các ngày trong chuyến đi với thời gian ăn uống và địa điểm tham quan',
                                items: new Schema(
                                    type: DataType::OBJECT,
                                    properties: [
                                        'day' => new Schema(type: DataType::STRING, description: 'Ngày của chuyến đi, ví dụ: "Ngày 1", "Ngày 2"'),
                                        'times' => new Schema(
                                            type: DataType::OBJECT,
                                            description: 'Thời gian ăn uống và địa điểm tham quan tương ứng',
                                            properties: [
                                                'morning' => new Schema(
                                                    type: DataType::ARRAY,
                                                    description: 'Các địa điểm cho buổi sáng',
                                                    items: new Schema(
                                                        type: DataType::OBJECT,
                                                        properties: [
                                                            'type' => new Schema(type: DataType::STRING, description: 'Loại địa điểm: "food" hoặc "sightseeing"'),
                                                            'name' => new Schema(type: DataType::STRING, description: 'Tên địa điểm'),
                                                            'address' => new Schema(type: DataType::STRING, description: 'Địa chỉ'),
                                                            'latitude' => new Schema(type: DataType::NUMBER, description: 'Vĩ độ'),
                                                            'longitude' => new Schema(type: DataType::NUMBER, description: 'Kinh độ'),
                                                            'description' => new Schema(type: DataType::STRING, description: 'Mô tả chi tiết'),
                                                            'food_type' => new Schema(type: DataType::STRING, description: 'Loại ẩm thực (chỉ cho địa điểm ăn uống)')
                                                        ],
                                                        // Đã bỏ 'latitude' và 'longitude' khỏi required
                                                        required: ['type', 'name', 'address', 'description']
                                                    )
                                                ),
                                                'lunch' => new Schema(
                                                    type: DataType::ARRAY,
                                                    description: 'Các địa điểm cho buổi trưa',
                                                    items: new Schema(
                                                        type: DataType::OBJECT,
                                                        properties: [
                                                            'type' => new Schema(type: DataType::STRING, description: 'Loại địa điểm: "food" hoặc "sightseeing"'),
                                                            'name' => new Schema(type: DataType::STRING, description: 'Tên địa điểm'),
                                                            'address' => new Schema(type: DataType::STRING, description: 'Địa chỉ'),
                                                            'latitude' => new Schema(type: DataType::NUMBER, description: 'Vĩ độ'),
                                                            'longitude' => new Schema(type: DataType::NUMBER, description: 'Kinh độ'),
                                                            'description' => new Schema(type: DataType::STRING, description: 'Mô tả chi tiết'),
                                                            'food_type' => new Schema(type: DataType::STRING, description: 'Loại ẩm thực (chỉ cho địa điểm ăn uống)')
                                                        ],
                                                        required: ['type', 'name', 'address', 'description']
                                                    )
                                                ),
                                                'afternoon' => new Schema(
                                                    type: DataType::ARRAY,
                                                    description: 'Các địa điểm cho buổi chiều',
                                                    items: new Schema(
                                                        type: DataType::OBJECT,
                                                        properties: [
                                                            'type' => new Schema(type: DataType::STRING, description: 'Loại địa điểm: "food" hoặc "sightseeing"'),
                                                            'name' => new Schema(type: DataType::STRING, description: 'Tên địa điểm'),
                                                            'address' => new Schema(type: DataType::STRING, description: 'Địa chỉ'),
                                                            'latitude' => new Schema(type: DataType::NUMBER, description: 'Vĩ độ'),
                                                            'longitude' => new Schema(type: DataType::NUMBER, description: 'Kinh độ'),
                                                            'description' => new Schema(type: DataType::STRING, description: 'Mô tả chi tiết'),
                                                            'food_type' => new Schema(type: DataType::STRING, description: 'Loại ẩm thực (chỉ cho địa điểm ăn uống)')
                                                        ],
                                                        required: ['type', 'name', 'address', 'description']
                                                    )
                                                ),
                                                'evening' => new Schema(
                                                    type: DataType::ARRAY,
                                                    description: 'Các địa điểm cho buổi tối',
                                                    items: new Schema(
                                                        type: DataType::OBJECT,
                                                        properties: [
                                                            'type' => new Schema(type: DataType::STRING, description: 'Loại địa điểm: "food" hoặc "sightseeing"'),
                                                            'name' => new Schema(type: DataType::STRING, description: 'Tên địa điểm'),
                                                            'address' => new Schema(type: DataType::STRING, description: 'Địa chỉ'),
                                                            'latitude' => new Schema(type: DataType::NUMBER, description: 'Vĩ độ'),
                                                            'longitude' => new Schema(type: DataType::NUMBER, description: 'Kinh độ'),
                                                            'description' => new Schema(type: DataType::STRING, description: 'Mô tả chi tiết'),
                                                            'food_type' => new Schema(type: DataType::STRING, description: 'Loại ẩm thực (chỉ cho địa điểm ăn uống)')
                                                        ],
                                                        required: ['type', 'name', 'address', 'description']
                                                    )
                                                )
                                            ],
                                            // Đảm bảo thứ tự các key trong JSON
                                            propertyOrdering: ['morning', 'lunch', 'afternoon', 'evening']
                                        )
                                    ],
                                    required: ['day', 'times'],
                                    // Đảm bảo thứ tự các key trong JSON
                                    propertyOrdering: ['day', 'times']
                                )
                            )
                        ],
                        required: ['days'],
                        // Đảm bảo thứ tự các key trong JSON
                        propertyOrdering: ['days']
                    )
                )
            )
            ->generateContent($prompt);

        $response = $response->text();
        $response = json_decode($response, true);
        return $response;
    }

    // public function getNewTourItem(TourItem $tourItem)
    // {
    //     $deletedTourItems = TourItem::whereHas('tour', function ($query) {
    //         $query->where('user_id', Auth::id());
    //     })->where('tour_id', $tourItem->tour_id)
    //         ->where('status', 0)
    //         ->get();
    //     $existingTourItems = TourItem::whereHas('tour', function ($query) {
    //         $query->where('user_id', Auth::id());
    //     })->where('tour_id', $tourItem->tour_id)
    //         ->where('status', 1)
    //         ->get();

    //     $nearbyLocation = $tourItem->tour->name;
    //     $foodType = $tourItem->tour->food_type;
    //     $suggestedTime = $tourItem->tour->time;
    //     $day = $tourItem->day;
    //     $deletedTourItemsAsString = $deletedTourItems->map(function ($item) {
    //         return $item->name . '(' . $item->address . ')';
    //     })->implode(', ');

    //     $existingTourItemsAsString = $existingTourItems->map(function ($item) {
    //         return $item->name . '(' . $item->address . ')';
    //     })->implode(', ');

    //     $prompt = "
    //     Lên lịch trình food tour gần $nearbyLocation với các yêu cầu sau:

    //     - Nơi đi (Trong phạm vi huyện/tỉnh thành): $nearbyLocation
    //     - Loại món ăn: $foodType
    //     - Thời gian đi (sáng/trưa/chiều/tối/cả ngày): $suggestedTime

    //     Hãy đề xuất một quán ăn đạt tiêu chuẩn trên, trả về kết quả dưới định dạng chuỗi JSON hợp lệ. Cấu trúc JSON phải là một object với các trường sau:

    //     - 'name': Tên địa điểm ăn uống (string)
    //     - 'address': Địa chỉ (string)
    //     - 'latitude': Vĩ độ (number)
    //     - 'longitude': Kinh độ (number)
    //     - 'description': Mô tả ngắn (string)
    //     - 'suggested_time': Buổi gợi ý (string, ví dụ: 'sáng', 'trưa', 'tối')
    //     - 'food_type': Tên loại món ăn (string, phù hợp với bảng food_types)

    //     Lưu ý 1: Loại trừ các địa điểm đã bị xóa như sau:
    //     '''
    //     $deletedTourItemsAsString
    //     '''
    //     Lưu ý 2: Loại trừ các địa điểm đã có sẵn như sau:
    //     '''
    //     $existingTourItemsAsString
    //     '''

    //      **Chỉ trả về một chuỗi JSON hợp lệ duy nhất, không bao gồm bất kỳ ký tự bao bọc nào như ```json hoặc ```.**
    //     ";

    //     // return $prompt;
    //     $generationConfig = new GenerationConfig();
    //     $generationConfig = $generationConfig->withTemperature(self::TEMPERATURE);

    //     $response = $this->client->generativeModel(self::GEMINI_2_0_FLASH)
    //         ->withGenerationConfig($generationConfig)
    //         ->generateContent(
    //             new TextPart($prompt),
    //         );
    //     $response = $response->text();
    //     $response = preg_replace('/^```json\s*|\s*```$/', '', $response);
    //     $response = json_decode($response, true);

    //     DB::beginTransaction();
    //     try {
    //         $newTourItem = TourItem::create([
    //             'tour_id' => $tourItem->tour_id,
    //             'day' => $day,
    //             'name' => $response['name'],
    //             'address' => $response['address'],
    //             'description' => $response['description'],
    //             'latitude' => $response['latitude'],
    //             'longitude' => $response['longitude'],
    //             'suggested_time' => $suggestedTime,
    //             'status' => 1,
    //         ]);

    //         DB::commit();
    //         return $newTourItem;
    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         return [
    //             'error' => 'Có lỗi xảy ra khi tạo địa điểm mới.',
    //             'message' => $e->getMessage(),
    //         ];
    //     }
    // }

    // public function getTourById(int $tourId)
    // {
    //     $tour = Tour::with(['tourItems' => function ($query) {
    //         $query->where('status', 1);
    //     }])->find($tourId);

    //     if (!$tour) {
    //         return null;
    //     }

    //     $formattedResponse = [];
    //     foreach ($tour->tourItems->groupBy('day') as $day => $items) {
    //         $formattedResponse[$day] = [];
    //         foreach ($items->groupBy('suggested_time') as $time => $timeItems) {
    //             $formattedResponse[$day][$time] = $timeItems->map(function ($item) {
    //                 return [
    //                     'id' => $item->id,
    //                     'tour_id' => $item->tour_id,
    //                     'day' => $item->day,
    //                     'name' => $item->name,
    //                     'address' => $item->address,
    //                     'latitude' => (float)$item->latitude,
    //                     'longitude' => (float)$item->longitude,
    //                     'description' => $item->description,
    //                     'suggested_time' => $item->suggested_time,
    //                     'food_type' => $item->food_type ?? null,
    //                     'notes' => $item->notes ?? null,
    //                 ];
    //             })->toArray();
    //         }
    //     }

    //     return $formattedResponse;
    // }
}
