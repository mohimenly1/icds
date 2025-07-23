<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiBotController extends Controller
{
    public function ask(Request $request)
    {
        set_time_limit(120);

        $validated = $request->validate(['question' => 'required|string']);
        $userQuestion = $validated['question'];

        // --- 1. تحليل النص عبر استدعاء Python ---
        try {
            $analysisResponse = Http::post('http://127.0.0.1:8001/analyze', ['text' => $userQuestion]);

            if (!$analysisResponse->successful()) {
                Log::error('AI analysis service failed to respond.', ['status' => $analysisResponse->status()]);
                return response()->json(['answer' => 'عذرًا، خدمة التحليل الذكي لا تعمل حاليًا.'], 500);
            }

            $analysis = $analysisResponse->json();
            Log::info('AI Bot Analysis Received:', $analysis);
        
        } catch (\Exception $e) {
            Log::error('Exception during AI analysis call: ' . $e->getMessage());
            return response()->json(['answer' => 'عذرًا، لا يمكن الوصول لخدمة الذكاء الاصطناعي.'], 500);
        }
        
        $intent = $analysis['intent'] ?? null;
        $entities = $analysis['entities'] ?? [];
        $responseText = 'عذرًا، لم أفهم طلبك. هل يمكنك طرح السؤال بطريقة أخرى؟';
        $action = null; // نهيئ متغير الأوامر

        // --- 2. بناء الرد النصي وتحديد الأمر ---
        switch ($intent) {
            case 'معرفة_جدول_طبيب':
            case 'التحقق_من_حالة_طبيب':
                if (isset($entities['PERS'])) {
                    $doctorName = implode(' ', $entities['PERS']);
                    $doctor = Doctor::where('name', 'like', '%' . $doctorName . '%')->with('schedules')->first();
                    
                    if ($doctor) {
                        // بما أننا وجدنا الطبيب، نحدد الأمر
                        $action = [
                            'type' => 'SELECT_DOCTOR',
                            'payload' => ['id' => $doctor->id]
                        ];

                        // بناء الرد النصي بناءً على القصد الأصلي
                        if ($intent === 'معرفة_جدول_طبيب') {
                            if ($doctor->schedules->isNotEmpty()) {
                                $responseText = "بالتأكيد، هذا هو جدول الدكتور {$doctor->name}: ";
                                $daysOfWeek = [0 => 'الأحد', 1 => 'الإثنين', 2 => 'الثلاثاء', 3 => 'الأربعاء', 4 => 'الخميس', 5 => 'الجمعة', 6 => 'السبت'];
                                foreach ($doctor->schedules as $schedule) {
                                    $dayName = $daysOfWeek[$schedule->day_of_week] ?? 'يوم غير محدد';
                                    $responseText .= "يوم {$dayName} من " . substr($schedule->start_time, 0, 5) . " إلى " . substr($schedule->end_time, 0, 5) . ". ";
                                }
                            } else {
                                $responseText = "لم يتم تحديد جدول عمل للدكتور {$doctor->name} بعد، ولكن سيتم عرض تفاصيله.";
                            }
                        } else { // intent === 'التحقق_من_حالة_طبيب'
                            $responseText = $doctor->status === 'available'
                                ? "نعم، الدكتور {$doctor->name} متوفر حاليًا."
                                : "لا، الدكتور {$doctor->name} حاليًا في حالة '{$doctor->status}'.";
                        }
                    } else {
                        $responseText = "عذرًا، لم أجد طبيبًا بالاسم: {$doctorName}";
                    }
                } else {
                    $responseText = "لم أتمكن من تحديد اسم الطبيب في سؤالك.";
                }
                break;

            case 'البحث_عن_تخصص':
                preg_match('/(أسنان|عظام|جلدية|أطفال|باطنية)/', $userQuestion, $matches);
                $specialty = $matches[0] ?? null;
                if ($specialty) {
                    $doctors = Doctor::where('specialty', 'like', '%' . $specialty . '%')->get();
                    if ($doctors->isNotEmpty()) {
                        $responseText = "أطباء تخصص {$specialty} هم: " . $doctors->pluck('name')->join('، ');
                    } else {
                        $responseText = "عذرًا، لا يوجد لدينا أطباء في تخصص {$specialty} حاليًا.";
                    }
                } else {
                    $responseText = "لم أتمكن من تحديد اسم التخصص في سؤالك.";
                }
                break;
        }

        // --- 3. إنشاء الصوت باستخدام واجهة مترجم جوجل ---
        try {
            $encodedText = urlencode($responseText);
            $url = "https://translate.google.com/translate_tts?ie=UTF-8&client=tw-ob&tl=ar&q={$encodedText}";
            $audioResponse = Http::timeout(30)->get($url);

            if ($audioResponse->successful() && !empty($audioResponse->body())) {
                $audioContent = $audioResponse->body();
                return response()->json([
                    'answer' => $responseText,
                    'audio' => base64_encode($audioContent),
                    'action' => $action // إرسال الأمر مع الرد
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Exception during Google Translate TTS synthesis: ' . $e->getMessage());
        }

        // في حال فشل أي شيء في إنشاء الصوت، أعد النص والأمر فقط
        return response()->json(['answer' => $responseText, 'audio' => null, 'action' => $action]);
    }
}