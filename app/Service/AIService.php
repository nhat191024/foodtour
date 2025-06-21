<?php

namespace App\Service;

use Gemini;
use Gemini\Data\Schema;
use Gemini\GeminiHelper;
use Gemini\Enums\DataType;
use Gemini\Enums\ModelVariation;
use Gemini\Data\GenerationConfig;
use Gemini\Enums\ResponseMimeType;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

use App\Models\History;
use App\Models\HistoryItem;
use App\Models\HistoryFood;
use App\Models\HistorySightseeing;

use App\Service\OpenWeatherService;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\Log;

class AIService
{
    private Gemini\Client $client;
    private  $weatherService;

    public function __construct()
    {
        $this->client = Gemini::client(env('GEMINI_API_KEY'));
        $this->weatherService = app(OpenWeatherService::class);
    }

    /**
     * Tạo một lịch trình du lịch ẩm thực và tham quan chi tiết tại $location.
     *
     *
     * @param string $location
     * @param string $foodType đa dạng loại món ăn
     * @param string $time buổi sáng/trưa/chiều/tối
     * @param string $company đi cùng với ai
     * @param string $interests sở thích, đặc điểm khác
     * @param int|null $numberOfDays số ngày đi
     * @param string|null $startDate chủ yếu để phục vụ lấy thời tiết
     * @param string|null $endDate chủ yếu để phục vụ lấy thời tiết
     * @return array|null function này sẽ chỉ return id của bản ghi History mới được tạo
     */
    public function getTour
    (
        string $location,
        string $foodType,
        string $time,
        string $company,
        string $interests,
        ?int $numberOfDays = 0,
        ?string $startDate = null,
        ?string $endDate = null,
        ) : array
    {
        // add vietnam to the location if not already present
        if (!str_contains($location, 'Vietnam')) {
            $location .= ', Vietnam';
        }
        if (!str_contains($location, 'Vietnam')) {
            $location .= ', Vietnam';
        }
        $startDate ??= date('Y-m-d');
        $endDate ??= date('Y-m-d', strtotime("+$numberOfDays days"));
        $weather = $this->weatherService->getWeatherInVietnam($location, $startDate, $endDate);
        // weather service requires a start date and end date
        $numberOfDays = $numberOfDays > 0 ? $numberOfDays : 1;

        $response = null;

        if (env('AI_SERVICE_DEBUG') === false) {
            $prompt = "
        Tạo một lịch trình du lịch ẩm thực và tham quan chi tiết tại $location với các yêu cầu sau:
        - Địa điểm: $location
        - Loại ẩm thực chính cho chuyến đi: $foodType (áp dụng cho các địa điểm ăn uống được gợi ý).
        - Các địa điểm tham quan nên ở gần các địa điểm ăn uống, không quá xa.
        - Thông tin thời tiết cho khu vực này: " . json_encode($weather, JSON_UNESCAPED_UNICODE) . ". Hãy sử dụng thông tin này để gợi ý các địa điểm ăn uống và tham quan phù hợp với thời tiết.
        - Thời gian trong ngày tôi muốn tập trung: $time (Có thể là 'morning', 'lunch', 'afternoon', 'evening', hoặc 'full day'). Nếu là 'full day', hãy bao gồm tất cả các khung thời gian sáng, trưa, chiều, tối. Không được tự ý gợi ý thêm ngoài những buổi trong ngày tôi chọn.
        - Số ngày: $numberOfDays
        - Tôi đi cùng với : $company
        - Những đặc điểm tôi quan tâm: $interests

        **Đối với mỗi khung thời gian (morning, lunch, afternoon, evening) trong mỗi ngày, hãy gợi ý ít nhất một địa điểm. Mỗi địa điểm phải có các thông tin sau:**
        - **'type'**: Phải là 'food' (cho địa điểm ăn uống) hoặc 'sightseeing' (cho địa điểm tham quan).
        - **'name'**: Tên của địa điểm.
        - **'address'**: Địa chỉ cụ thể.
        - **'latitude'**: Vĩ độ. Nếu không có giá trị chính xác, hãy cung cấp một số ước tính hợp lý.
        - **'longitude'**: Kinh độ. Nếu không có giá trị chính xác, hãy cung cấp một số ước tính hợp lý.
        - **'description'**: Mô tả chi tiết về địa điểm và lý do gợi ý (ví dụ: món ăn đặc trưng, điểm nổi bật của địa điểm tham quan).
        - **'food_type'**: Chỉ bao gồm trường này và gán giá trị '$foodType' nếu 'type' là 'food'. Không bao gồm trường này nếu 'type' là 'sightseeing'.

        Lưu ý: Các đặc điểm quan tâm sẽ không còn bắt buộc nếu gần địa điểm được chọn không thể đáp ứng được, khi điều đó xảy ra, hãy đưa ra gợi ý gần giống kèm lời xin lỗi và giải thích.
        ";
        // **Quan trọng: Đầu ra phải là một cấu trúc JSON hoàn chỉnh và bằng tiếng Việt.**

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
                                            'day' => new Schema(type: DataType::STRING, description: 'Ngày của chuyến đi và thông tin của ngày hôm đó, ví dụ: "Ngày 1 (d/m/yy - {temp}, {weather})", "Ngày 2 (d/m/yy - {temp}, {weather})"'),
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
                                ),
                                'description'  => new Schema(
                                    type: DataType::STRING,
                                    description: 'Một đoạn mô tả ngắn miêu tả chung về toàn bộ chuyến đi. Có thể chèn lời nhắc nhở, lời chúc, lưu ý, v.v...'
                                ),
                                'interests'  => new Schema(
                                    type: DataType::STRING,
                                    description: 'Những Sở thích của người dùng đã chọn (viết dưới dạng tiếng Việt có dấu, phiên bản viết đúng chính tả)'
                                ),
                                'company'  => new Schema(
                                    type: DataType::STRING,
                                    description: 'Đối tượng mà người dùng đi tham quan cùng (viết dưới dạng tiếng Việt có dấu, phiên bản viết đúng chính tả)'
                                ),
                            ],
                            required: ['days', 'description', 'interests', 'company'],
                            // Đảm bảo thứ tự các key trong JSON
                            propertyOrdering: ['days', 'description', 'company', 'interests']
                        )
                    )
                )
                ->generateContent($prompt);

            $response = $response->text();
            $response = json_decode($response, true);
        } else {
            $testData = file_get_contents(base_path('database/test.json'));
            $response = json_decode($testData, true);
        }
        Log::info('got response:', $response);
        DB::beginTransaction();
        try {
            $history = History::create([
                'user_id' => Auth::id() ?? 1, // Default to 1 if no user is authenticated
                'title' => "Lịch trình du lịch tại $location",
                'start_date' => $startDate,
                'end_date' => $endDate,
                'description' => $response['description'],
                'company' => $response['company'],
                'interests' => $response['interests'],
                'cost' => 0,
            ]);

            foreach ($response['days'] as $dayData) {
                $day = $dayData['day'];
                $day = $this->formattedDayTitle($dayData['day']);
                foreach ($dayData['times'] as $dayTime => $items) {
                    $historyItem = HistoryItem::create([
                        'history_id' => $history->id,
                        'day_number' => $day,
                        'day_time' => $dayTime,
                    ]);
                    foreach ($items as $item) {
                        if ($item['type'] === 'food') {
                            HistoryFood::create([
                                'history_item_id' => $historyItem->id,
                                'name' => $item['name'],
                                'description' => $item['description'],
                                'address' => $item['address'],
                                'food_type' => $item['food_type'] ?? null,
                                'latitude' => $item['latitude'],
                                'longitude' => $item['longitude'],
                            ]);
                        } elseif ($item['type'] === 'sightseeing') {
                            HistorySightseeing::create([
                                'history_item_id' => $historyItem->id,
                                'name' => $item['name'],
                                'address' => $item['address'],
                                'latitude' => $item['latitude'],
                                'longitude' => $item['longitude'],
                                'description' => $item['description'],
                            ]);
                        }
                    }
                }
            }

            DB::commit();

            //* always get the 'truth' from database
            $returnData = $history->load('items', 'items.sightseeing', 'items.food');
            return [
                'success' => true,
                'data' => $returnData
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'error' => 'Có lỗi xảy ra khi tạo lịch trình du lịch.',
                'message' => $e->getMessage(),
            ];
        }

        //? this is only for testing, never send raw AI response to the client
        // return $response;
        // instead, return an error to notify the client
        return [
            'success' => false,
            'error' => 'Vui lòng thử lại sau.',
            'message' => 'Error',
            'data' => $response
        ];
    }

    /**
     * get a single replacement item from the AI.
     *
     * @param string $type 'food' or 'sightseeing'
     * @param string $userPrompt The user's new request
     * @param object $context Context from the original trip (location, interests, vv...)
     * @param object $oldItem The item being replaced
     * @return array|null The new item data, or null on failure.
     */
    public function getReplacementItem(string $type, string $userPrompt, object $context, object $oldItem): ?array
    {
        $location = $context->location;
        $interests = $context->interests;
        $company = $context->company;
        $dayTime = $context->dayTime;
        $foodType = $context->foodType ?? 'đa dạng';

        $vietnameseType = $type === 'food' ? 'địa điểm ăn uống' : 'địa điểm tham quan';

        $prompt = "
        Dựa trên một lịch trình du lịch có sẵn tại '$location', hãy tìm một địa điểm THAY THẾ cho địa điểm cũ là '{$oldItem->name}'.

        Thông tin về bối cảnh chuyến đi:
        - Địa điểm chính: $location
        - Sở thích chung: $interests
        - Đi cùng với: $company
        - Buổi trong ngày: $dayTime
        - Loại ẩm thực chính (nếu thay thế địa điểm ăn uống): $foodType

        Yêu cầu mới của người dùng là: \"$userPrompt\"

        Nhiệm vụ của bạn là tìm **chỉ một (1)** địa điểm '$vietnameseType' mới phù hợp với yêu cầu của người dùng và bối cảnh trên.

        Đầu ra phải là một object JSON duy nhất, không phải mảng, chứa các thông tin sau:
        - 'name': Tên của địa điểm mới.
        - 'address': Địa chỉ cụ thể.
        - 'latitude': Vĩ độ.
        - 'longitude': Kinh độ.
        - 'description': Mô tả chi tiết về địa điểm mới và lý do nó phù-hợp với yêu cầu.
        - 'food_type': Chỉ bao gồm trường này và gán giá trị '$foodType' nếu loại địa điểm là 'food'.
        ";

        try {
            $schema = new Schema(
                type: DataType::OBJECT,
                properties: [
                    'name' => new Schema(type: DataType::STRING),
                    'address' => new Schema(type: DataType::STRING),
                    'latitude' => new Schema(type: DataType::NUMBER),
                    'longitude' => new Schema(type: DataType::NUMBER),
                    'description' => new Schema(type: DataType::STRING),
                    'food_type' => new Schema(type: DataType::STRING),
                ],
                required: ['name', 'address', 'description', 'latitude', 'longitude']
            );

            $response = $this->client
                ->generativeModel(model: 'gemini-2.5-flash')
                ->withGenerationConfig(
                    generationConfig: new GenerationConfig(
                        responseMimeType: ResponseMimeType::APPLICATION_JSON,
                        responseSchema: $schema
                    )
                )
                ->generateContent($prompt);

            return json_decode($response->text(), true);

        } catch (\Exception $e) {
            Log::error("AI Service failed to get replacement item: " . $e->getMessage());
            return null;
        }
    }

    //* Convert from "Ngày 1 (12/06/2025 - 32.5°C, Dông kèm mưa đá nhẹ)" to "Ngày 1 • 12/06/2025 • 32.5°C, Dông kèm mưa đá nhẹ"
    protected function formattedDayTitle(string $day_title): string
    {
        return str_replace(
            [' (', ')', '-', ','],
            [' • ', '', '•', ' • Thời tiết:'],
            $day_title
        );
    }

}
