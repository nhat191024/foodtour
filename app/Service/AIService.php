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
    private const aiNotice = "
        Lưu ý: Các đặc điểm quan tâm sẽ không còn bắt buộc nếu gần địa điểm được chọn không thể đáp ứng được, khi điều đó xảy ra, hãy đưa ra gợi ý gần giống kèm lời xin lỗi và giải thích.
        Lưu ý 2: Tất cả những dòng description hãy cố gắng viết một cách súc tích, bởi vì người đọc sẽ nhanh cảm thấy chán nếu quá dài.
        Lưu ý 3: Vui lòng sửa tất cả lỗi chính tả tồn tại trong input/output lịch trình. Nếu trong nội dung yêu cầu của tôi có chứa những từ ngữ viết tắt hoặc từ ngữ mang tính không phù hợp như: xúc phạm, spam, phân biệt đối xử, đùa giỡn, cợt nhả, hãy sửa lại cho đúng chính tả và viết lại nội dung đó một cách tế nhị và dễ hiểu nhất, đồng thời bắt buộc nhắc nhở hoặc cảnh báo nguy cơ bị hủy kích hoạt tài khoản của người dùng về việc này.
    ";

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
     * @param string $currentLocation địa điểm hiện tại
     * @param int|null $numberOfDays số ngày đi
     * @param string|null $startDate chủ yếu để phục vụ lấy thời tiết
     * @param string|null $endDate chủ yếu để phục vụ lấy thời tiết
     * @return array|null function này sẽ chỉ return id của bản ghi History mới được tạo
     */
    public function getTour(
        string $location,
        string $foodType,
        string $time,
        string $company,
        string $interests,
        string $memberCount,
        string $currentLocation,
        ?string $startDate = null,
        ?string $endDate = null,
    ): array {
        // add vietnam to the location if not already present
        if (!str_contains($location, 'Vietnam')) {
            $location .= ', Vietnam';
        }
        if (!str_contains($location, 'Vietnam')) {
            $location .= ', Vietnam';
        }
        $startDate ??= date('Y-m-d');
        $endDate ??= date('Y-m-d', strtotime("+1 days"));
        $weather = $this->weatherService->getWeatherInVietnam($location, $startDate, $endDate);
        $notice = self::aiNotice;
        $response = null;
        $memberCountString = $memberCount . ' người';

        if (env('AI_SERVICE_DEBUG') === false) {
            $prompt = "
        Tạo một lịch trình du lịch ẩm thực và tham quan chi tiết tại $location với các yêu cầu sau:
        - Địa điểm: $location
        - Loại ẩm thực chính cho chuyến đi: $foodType (áp dụng cho các địa điểm ăn uống được gợi ý).
        - Các địa điểm tham quan nên ở gần các địa điểm ăn uống, không quá xa.
        - Thông tin thời tiết cho khu vực này: " . json_encode($weather, JSON_UNESCAPED_UNICODE) . ". Hãy sử dụng thông tin này để gợi ý các địa điểm ăn uống và tham quan phù hợp với thời tiết.
        - Thời gian trong ngày tôi muốn tập trung: $time (Có thể là 'morning', 'lunch', 'afternoon', 'evening', hoặc 'full day'). Nếu là 'full day', hãy bao gồm tất cả các khung thời gian sáng, trưa, chiều, tối. Không được tự ý gợi ý thêm ngoài những buổi trong ngày tôi chọn.
        - Số ngày đi (bạn hãy tự suy ra số ngày dựa trên khoảng ngày sau đây, vui lòng bỏ qua dữ liệu giờ, phút, giây và chỉ tính theo ngày tháng): Từ $startDate đến $endDate
        - Tôi đi cùng với : $company
        - Số lượng thành viên đi cùng (dùng để chọn nhà xe) : $memberCountString
        - Những đặc điểm tôi quan tâm: $interests
        - Vị trí hiện tại của người dùng cung cấp (tùy chọn): $currentLocation

        **Đối với mỗi khung thời gian (morning, lunch, afternoon, evening) trong mỗi ngày, hãy gợi ý ít nhất một địa điểm. Mỗi địa điểm phải có các thông tin sau:**
        - **'type'**: Phải là 'food' (cho địa điểm ăn uống) hoặc 'sightseeing' (cho địa điểm tham quan).
        - **'name'**: Tên của địa điểm.
        - **'address'**: Địa chỉ cụ thể.
        - **'latitude'**: Vĩ độ. Nếu không có giá trị chính xác, hãy cung cấp một số ước tính hợp lý.
        - **'longitude'**: Kinh độ. Nếu không có giá trị chính xác, hãy cung cấp một số ước tính hợp lý.
        - **'description'**: Mô tả súc tích về địa điểm và lý do gợi ý (ví dụ: món ăn đặc trưng, điểm nổi bật của địa điểm tham quan).
        - **'food_type'**: Chỉ bao gồm trường này và gán giá trị '$foodType' nếu 'type' là 'food'. Không bao gồm trường này nếu 'type' là 'sightseeing'.

        $notice
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
                                'bus' => new Schema(
                                    type: DataType::ARRAY,
                                    description: 'Các gợi ý về công ty/hãng xe khách hoặc nơi tập trung xe khách để đón/trả khách (bến xe tư nhân). Nhà xe sẽ phải gần với Vị trí hiện tại của người dùng. Đưa ra 2 gợi ý tốt nhất',
                                    items: new Schema(
                                        type: DataType::OBJECT,
                                        properties: [
                                            'name' => new Schema(type: DataType::STRING, description: 'Tên nhà xe hoặc bến xe'),
                                            'address' => new Schema(type: DataType::STRING, description: 'Địa chỉ hoặc thông tin liên hệ'),
                                            'description' => new Schema(type: DataType::STRING, description: 'Mô tả chi tiết về nhà xe, tuyến đường, hoặc dịch vụ'),
                                            'phone' => new Schema(type: DataType::STRING, description: 'Số điện thoại liên hệ (nếu có, nếu không có thì chỉ ghi là không có)'),
                                            'website' => new Schema(type: DataType::STRING, description: 'Website hoặc trang thông tin (nếu có, nếu không có thì chỉ ghi là không có)'),
                                            'departure_time' => new Schema(type: DataType::STRING, description: 'Giờ xuất phát dự kiến (nếu có, nếu không có thì chỉ ghi là không có)'),
                                            'arrival_time' => new Schema(type: DataType::STRING, description: 'Giờ đến dự kiến (nếu có, nếu không có thì chỉ ghi là không có)'),
                                            'price' => new Schema(type: DataType::STRING, description: 'Giá vé tham khảo (nếu có, nếu không có thì chỉ ghi là không có)'),
                                        ],
                                        required: ['name', 'address', 'description']
                                    )
                                ),
                                'motel' => new Schema(
                                    type: DataType::ARRAY,
                                    description: 'Các địa điểm gợi ý khách sạn gần khu thăm quan để tiện cư trú tạm thời. Đưa ra 2 gợi ý tốt nhất',
                                    items: new Schema(
                                        type: DataType::OBJECT,
                                        properties: [
                                            'name' => new Schema(type: DataType::STRING, description: 'Tên địa điểm'),
                                            'address' => new Schema(type: DataType::STRING, description: 'Địa chỉ'),
                                            'latitude' => new Schema(type: DataType::NUMBER, description: 'Vĩ độ'),
                                            'longitude' => new Schema(type: DataType::NUMBER, description: 'Kinh độ'),
                                            'description' => new Schema(type: DataType::STRING, description: 'Mô tả chi tiết'),
                                        ],
                                        required: ['name', 'address', 'description']
                                    )
                                ),
                            ],
                            required: ['days', 'description', 'interests', 'company', 'bus', 'motel'],
                            // Đảm bảo thứ tự các key trong JSON
                            propertyOrdering: ['days', 'description', 'company', 'interests', 'bus', 'motel']
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
                'current_location' => $currentLocation,
                'member_count' => $memberCount,
                'cost' => 0,
            ]);

            if (!empty($response['bus'])) {
                foreach ($response['bus'] as $bus) {
                    $history->buses()->create([
                        'name' => $bus['name'] ?? '',
                        'address' => $bus['address'] ?? '',
                        'description' => $bus['description'] ?? '',
                        'phone' => $bus['phone'] ?? null,
                        'website' => $bus['website'] ?? null,
                        'departure_time' => $bus['departure_time'] ?? null,
                        'arrival_time' => $bus['arrival_time'] ?? null,
                        'price' => $bus['price'] ?? null,
                    ]);
                }
            }

            if (!empty($response['motel'])) {
                foreach ($response['motel'] as $motel) {
                    $history->motels()->create([
                        'name' => $motel['name'] ?? '',
                        'address' => $motel['address'] ?? '',
                        'latitude' => $motel['latitude'] ?? null,
                        'longitude' => $motel['longitude'] ?? null,
                        'description' => $motel['description'] ?? '',
                    ]);
                }
            }

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
            $returnData = $history;
            return [
                'success' => true,
                'data' => $returnData
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::debug($e->getMessage());
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

    public function getNewBusItem(string $userPrompt, object $context, object $oldItems): ?array
    {
        // note: the old items is
        // $history = History::findOrFail($id);
        // $oldItems = $history->buses;

        $location = $context->location;
        $currentLocation = $context->current_location;
        $interests = $context->interests;
        $company = $context->company;
        $memberCount = $context->member_count;

        $notice = self::aiNotice;

        $oldNames = [];
        foreach ($oldItems as $item) {
            if (isset($item->name)) {
            $oldNames[] = $item->name;
            }
        }

        $prompt = "
        Dựa trên một lịch trình du lịch có sẵn tại '$location', hãy tìm một nhà xe mới, không trùng với các nhà xe hiện tại sau: " . json_encode($oldNames, JSON_UNESCAPED_UNICODE) . ".


        Thông tin về bối cảnh chuyến đi:
        - Địa điểm chính: $location
        - Sở thích chung: $interests
        - Đi cùng với: $company
        - Địa điểm hiện tại của người dùng: $currentLocation
        - Số người đi cùng: $memberCount

        Yêu cầu mới của người dùng là: \"$userPrompt\"

        Nhiệm vụ của bạn là tìm **chỉ một (1)** nhà xe mới thỏa mãn yêu cầu người dùng, không trùng lặp với các item cũ.

        Đầu ra phải là một object JSON duy nhất, không phải mảng.

        $notice
        ";

        try {
            $schema = new Schema(
                type: DataType::OBJECT,
                properties: [
                    'name' => new Schema(type: DataType::STRING),
                    'address' => new Schema(type: DataType::STRING),
                    'description' => new Schema(type: DataType::STRING),
                    'phone' => new Schema(type: DataType::STRING),
                    'website' => new Schema(type: DataType::STRING),
                    'departure_time' => new Schema(type: DataType::STRING),
                    'arrival_time' => new Schema(type: DataType::STRING),
                    'price' => new Schema(type: DataType::STRING),
                ],
                required: ['name', 'address', 'description']
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

    public function getNewMotelItem(string $userPrompt, object $context, object $oldItems): ?array {
        $location = $context->location;
        $currentLocation = $context->current_location;
        $interests = $context->interests;
        $company = $context->company;
        $memberCount = $context->member_count;

        $notice = self::aiNotice;

        $oldNames = [];
        foreach ($oldItems as $item) {
            if (isset($item->name)) {
            $oldNames[] = $item->name;
            }
        }

        $prompt = "
        Dựa trên một lịch trình du lịch có sẵn tại '$location', hãy tìm một khách sạn/nhà nghỉ mới, không trùng với các khách sạn hiện tại sau: " . json_encode($oldNames, JSON_UNESCAPED_UNICODE) . ".

        Thông tin về bối cảnh chuyến đi:
        - Địa điểm chính: $location
        - Sở thích chung: $interests
        - Đi cùng với: $company
        - Địa điểm hiện tại của người dùng: $currentLocation
        - Số người đi cùng: $memberCount

        Yêu cầu mới của người dùng là: \"$userPrompt\"

        Nhiệm vụ của bạn là tìm **chỉ một (1)** khách sạn/nhà nghỉ mới thỏa mãn yêu cầu người dùng, không trùng lặp với các item cũ.

        Đầu ra phải là một object JSON duy nhất, không phải mảng.

        $notice
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
            Log::error("AI Service failed to get replacement motel item: " . $e->getMessage());
            return null;
        }
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
    public function getReplacementItem(string $type, string $userPrompt, object $context, object $oldFoodItems, object $oldSightseeingItems): ?array
    {
        $location = $context->location;
        $interests = $context->interests;
        $company = $context->company;
        $dayTime = $context->dayTime;
        $foodType = $context->foodType ?? 'đa dạng';
        $memberCount = $context->member_count;
        $notice = self::aiNotice;
        $vietnameseType = $type === 'food' ? 'địa điểm ăn uống' : 'địa điểm tham quan';

        $oldNames = [];
        foreach ($oldFoodItems as $item) {
            if (isset($item->name)) {
            $oldNames[] = $item->name;
            }
        }
        foreach ($oldSightseeingItems as $item) {
            if (isset($item->name)) {
            $oldNames[] = $item->name;
            }
        }

        $prompt = "
        Dựa trên một lịch trình du lịch có sẵn tại '$location', hãy tìm một địa điểm THAY THẾ cho địa điểm cũ không trùng với các item hiện tại sau: " . json_encode($oldNames, JSON_UNESCAPED_UNICODE) . ".

        Thông tin về bối cảnh chuyến đi:
        - Địa điểm chính: $location
        - Sở thích chung: $interests
        - Đi cùng với: $company
        - Buổi trong ngày: $dayTime
        - Loại ẩm thực chính (nếu thay thế địa điểm ăn uống): $foodType
        - Số người đi cùng: $memberCount

        Yêu cầu mới của người dùng là: \"$userPrompt\"

        Nhiệm vụ của bạn là tìm **chỉ một (1)** địa điểm '$vietnameseType' mới phù hợp với yêu cầu của người dùng và bối cảnh trên.

        Đầu ra phải là một object JSON duy nhất, không phải mảng, chứa các thông tin sau:
        - 'name': Tên của địa điểm mới.
        - 'address': Địa chỉ cụ thể.
        - 'latitude': Vĩ độ.
        - 'longitude': Kinh độ.
        - 'description': Mô tả súc tích về địa điểm mới và lý do nó phù-hợp với yêu cầu.
        - 'food_type': Chỉ bao gồm trường này và gán giá trị '$foodType' nếu loại địa điểm là 'food'.

        $notice
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
