<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Service\AIService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class SurveyController extends Controller
{
    public function store(Request $request)
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }
        // $start = time();
        // while (time() - $start < 30) {
        //     sleep(1);
        // }
        // dd($request->all());
        $messages = [
            'answers.required' => 'Vui lòng điền đầy đủ thông tin.',
            'answers.location.required' => 'Vui lòng nhập địa điểm.',
            'answers.member_count.required' => 'Vui lòng nhập số người đi.',
            'answers.member_count.required' => 'Vui lòng nhập số người đi.',
            'answers.member_count.integer' => 'Số người đi phải là một số nguyên.',
            'answers.member_count.min' => 'Số người đi tối thiểu là 1.',
            'answers.member_count.max' => 'Số người đi tối đa là 53.',
            'answers.food_type.required' => 'Vui lòng nhập loại món ăn.',
            'answers.location.min' => 'Địa điểm phải có ít nhất 3 ký tự.',
            'answers.location.max' => 'Địa điểm không được vượt quá 50 ký tự.',
            'answers.duration.required' => 'Vui lòng chọn số ngày.',
            'answers.duration.max' => 'Số ngày tối đa là 14.',
            'answers.company.required' => 'Vui lòng chọn đối tượng đi cùng.',
            'answers.company.min' => 'Thông tin đối tượng đi cùng phải có ít nhất 2 ký tự.',
            'answers.company.max' => 'Thông tin đối tượng đi cùng không được vượt quá 30 ký tự.',
            'answers.interests.required' => 'Vui lòng chọn sở thích.',
            'answers.interests.max' => 'Không được chọn quá 10 sở thích.',
            'answers.interests.*.min' => 'Mỗi sở thích phải có ít nhất 4 ký tự.',
            'answers.interests.*.max' => 'Mỗi sở thích không được vượt quá 50 ký tự.',
        ];

        $validatedData = $request->validate([
            'answers' => 'required|array',
            'answers.location'  => 'required|string|min:3|max:50',
            'answers.duration'  => 'required|array|max:2',
            'answers.company'   => 'required|string|min:2|max:30',
            'answers.interests' => 'required|array|max:5',
            'answers.food_type' => 'required|array|max:5',
            'answers.food_type.*' => 'string|min:4|max:50',
            'answers.member_count' => 'required|integer|min:1|max:53',
            'answers.interests.*' => 'string|min:4|max:50',
        ], $messages);

        $location = $validatedData['answers']['location'];
        // Validate date format for duration
        $iso8601Regex = '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}\.\d{3}Z$/';
        foreach (['0', '1'] as $idx) {
            if (!empty($validatedData['answers']['duration'][$idx]) &&
            !preg_match($iso8601Regex, $validatedData['answers']['duration'][$idx])) {
            return back()->with('error', 'Ngày không đúng định dạng. Vui lòng chọn lại.');
            }
        }
        $startDate = $validatedData['answers']['duration'][0] ?? null;
        $endDate = $validatedData['answers']['duration'][1] ?? null;
        $company = $validatedData['answers']['company'];
        $memberCount = $validatedData['answers']['member_count'];
        // avoid sending []array to the service
        $interests = implode(',', $validatedData['answers']['interests']);
        $time = implode(',', (array)$request->input('answers.time', ['all-day']));
        $foodType = implode(',', (array)$request->input('answers.food_type', ['everything']));

        Log::info('getting tour with this info...'.$location.$company.$interests.$time.$foodType);
        $aiService = new AIService();

        //* tránh trường hợp người dùng bật F12 và xóa validation
        if (str_contains($foodType, 'user_defined') ||
            str_contains($company, 'user_defined') ||
            str_contains($interests, 'user_defined')) {
            return back()->with('error', 'Vui lòng nhập đầy đủ thông tin');
        }
        // dd('getting tour with this info...'.$location.$company.$interests.$time.$foodType, $startDate, $endDate);
        $currentLocation = $request->input('current_location');
        if (is_null($currentLocation) || $currentLocation === '') {
            $currentLocation = 'Người dùng chưa cung cấp';
        }
        $currentLocation = mb_substr($currentLocation, 0, 60);
        $result = $aiService->getTour($location, $foodType, $time, $company, $interests, $memberCount, $currentLocation, $startDate, $endDate);
        if (!$result['success']) {
            return back()->with('error', 'Đã có lỗi xảy ra khi tạo lịch trình. Vui lòng thử lại sau.');
        }
        return to_route('survey.result', ['id' => $result['data']->id]);
    }

    public function index()
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }
        //* TODO: phải chuyển hết mẫu câu hỏi này sang file khác cho gọn
        // Hiện tại ko thể lưu vào DB vì cấu trúc chưa đc nhất quán
        // note: type=radio, type=checkbox mới hỗ trợ value='user_defined'
        // note: khi user chọn option có allow_multi_select=false, các option có allow_multi_select=true sẽ bị HỦY SELECT
        $questions = [
            [
                'id' => 'location',
                'text' => 'Bạn muốn tham quan ở đâu?',
                'type' => 'text',
                'placeholder' => 'Ví dụ: Hà Nội, Đà Nẵng, ...',
                'options' => [
                    ['value' => 'hai-phong', 'label' => 'Hải Phòng'],
                    ['value' => 'thai-binh', 'label' => 'Thái Bình'],
                    ['value' => 'ha-noi', 'label' => 'Hà Nội'],
                    ['value' => 'da-nang', 'label' => 'Đà Nẵng'],
                    ['value' => 'ho-chi-minh', 'label' => 'Hồ Chí Minh'],
                    ['value' => 'nha-trang', 'label' => 'Nha Trang'],
                    ['value' => 'hue', 'label' => 'Huế'],
                    ['value' => 'sa-pa', 'label' => 'Sa Pa'],
                    ['value' => 'ha-long', 'label' => 'Hạ Long'],
                    ['value' => 'phu-quoc', 'label' => 'Phú Quốc'],
                    ['value' => 'hoi-an', 'label' => 'Hội An'],
                    ['value' => 'da-lat', 'label' => 'Đà Lạt']
                ],
            ],
            // [
            //     'id' => 'duration', // number of days
            //     'text' => 'Bạn sẽ đi khoảng mấy ngày?',
            //     'type' => 'number',
            //     'placeholder' => 'Ví dụ: 3. TỐi đa 14 ngày',
            //     'options' => [
            //         ['value' => '1', 'label' => '1'],
            //         ['value' => '2', 'label' => '2'],
            //         ['value' => '3', 'label' => '3'],
            //         ['value' => '4', 'label' => '4'],
            //         ['value' => '5', 'label' => '5'],
            //         ['value' => '6', 'label' => '6'],
            //         ['value' => '7', 'label' => '7'],
            //         ['value' => '8', 'label' => '8'],
            //         ['value' => '9', 'label' => '9'],
            //         ['value' => '10', 'label' => '10'],
            //         ['value' => '11', 'label' => '11'],
            //         ['value' => '12', 'label' => '12'],
            //         ['value' => '13', 'label' => '13'],
            //         ['value' => '14', 'label' => '14'],
            //     ],
            // ],
            [
                'id' => 'duration', // number of days
                'text' => 'Bạn sẽ đi từ ngày nào đến ngày nào?',
                'type' => 'date-range',
                'placeholder' => 'Bấm để mở bảng chọn khoảng ngày',
                'hint' => 'Chọn ngày bắt đầu trên bảng lịch thứ 1 sau đó chọn tiếp ngày kết thúc trên lịch, nếu ngày kết thúc nằm ở tháng sau, bấm chọn nó ở bảng lịch thứ 2. TỐi đa khoảng 14 ngày.',
                'max' => 14
            ],
            [
                'id' => 'member_count',
                'text' => 'Bạn sẽ đi cùng bao nhiêu người?',
                'type' => 'number',
                'placeholder' => 'Điền số người tham gia hành trình. VD: 8',
                'options' => [
                    ['value' => '1', 'label' => '1'],
                    ['value' => '2', 'label' => '2'],
                    ['value' => '3', 'label' => '3'],
                    ['value' => '4', 'label' => '4'],
                    ['value' => '5', 'label' => '5'],
                    ['value' => '6', 'label' => '6'],
                    ['value' => '7', 'label' => '7'],
                    ['value' => '8', 'label' => '8'],
                    ['value' => '9', 'label' => '9'],
                    ['value' => '10', 'label' => '10'],
                    ['value' => '11', 'label' => '11'],
                    ['value' => '12', 'label' => '12'],
                    ['value' => '13', 'label' => '13'],
                    ['value' => '14', 'label' => '14'],
                ],
            ],
            [
                'id' => 'company',
                'text' => 'Bạn sẽ đi cùng với ai?',
                'type' => 'radio',
                'options' => [
                    ['value' => 'solo_travel', 'label' => 'Một mình'],
                    ['value' => 'couple_or_lover', 'label' => 'Người yêu'],
                    ['value' => 'friends', 'label' => 'Bạn bè'],
                    ['value' => 'family', 'label' => 'Gia đình'],
                    ['value' => 'user_defined', 'label' => 'Khác, bạn tự nhập', 'placeholder' => 'TỐi đa 30 ký tự, đối tượng bạn đi cùng là ai?' ],
                ],
            ],
            [
                'id' => 'food_type',
                'text' => 'Bạn thích những loại món ăn nào?',
                'type' => 'checkbox',
                'options' => [
                    ['allow_multi_select'=>true, 'value' => 'Phở và các món canh hoặc nước', 'label' => 'Phở và các món canh, nước'],
                    ['allow_multi_select'=>true, 'value' => 'Các loại bánh truyền thống', 'label' => 'Các loại bánh truyền thống'],
                    ['allow_multi_select'=>true, 'value' => 'Cơm và món mặn', 'label' => 'Cơm và món mặn'],
                    ['allow_multi_select'=>true, 'value' => 'Đặc sản địa phương', 'label' => 'Đặc sản địa phương'],
                    ['allow_multi_select'=>false, 'value' => 'user_defined', 'label' => 'Khác, bạn tự nhập', 'placeholder' => 'TỐi đa 50 ký tự, nói về sở thích món ăn của bạn' ],
                ],
            ],
            [
                'id' => 'interests',
                'text' => 'Bạn có hứng thú với những lựa chọn nào dưới đây?',
                'type' => 'checkbox',
                'options' => [
                    ['allow_multi_select'=>true, 'value' => 'Leo núi, khám phá thiên nhiên', 'label' => 'Leo núi, khám phá thiên nhiên'],
                    ['allow_multi_select'=>true, 'value' => 'Tắm biển, nghỉ dưỡng', 'label' => 'Tắm biển, nghỉ dưỡng'],
                    ['allow_multi_select'=>true, 'value' => 'Tham quan di tích, văn hóa', 'label' => 'Tham quan di tích, văn hóa'],
                    ['allow_multi_select'=>true, 'value' => 'Mua sắm, giải trí', 'label' => 'Mua sắm, giải trí'],
                    ['allow_multi_select'=>false, 'value' => 'user_defined', 'label' => 'Khác, bạn tự nhập', 'placeholder' => 'TỐi đa 50 ký tự, nói về sở thích du lịch của bạn' ],
                ],
            ],
            [
                'id' => 'time',
                'text' => 'Bạn sẽ đi vào thời điểm nào trong ngày? ?',
                'type' => 'checkbox',
                'options' => [
                    ['allow_multi_select'=>true, 'value' => 'morning', 'label' => 'Buổi sáng'],
                    ['allow_multi_select'=>true, 'value' => 'noon', 'label' => 'Buổi trưa'],
                    ['allow_multi_select'=>true, 'value' => 'afternoon', 'label' => 'Buổi chiều'],
                    ['allow_multi_select'=>true, 'value' => 'night', 'label' => 'Buổi tối'],
                    ['allow_multi_select'=>false, 'value' => 'morning,noon,afternoon,night', 'label' => 'Cả ngày'],
                ],
            ],
        ];
        return Inertia::render('survey/Index', [
            'questions' => $questions
        ]);
    }
}
