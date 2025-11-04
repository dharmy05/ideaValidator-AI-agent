# 🚀 BizValidator – Startup Idea Validation using Google Gemini

BizValidator is a Laravel-based service that uses **Google Gemini AI** to analyze and validate startup ideas.  
It generates structured feedback on your idea's market fit, monetization strategies, risks, and more.

---

## 🧠 Features

- ✅ Integration with **Google Gemini 2.5 Flash** model  
- 🧩 Produces a structured business analysis:
  1. Summary  
  2. Market Fit  
  3. Target Audience  
  4. Monetization Options  
  5. Competitive Advantage  
  6. Potential Risks  
  7. Validation Score (0–10)  
  8. Verdict (Proceed, Pivot, Drop)
- ⚙️ Simple Laravel service architecture  
- 🔒 Secure `.env` configuration  
- ⏱️ Includes timeout and detailed error handling  

---

## ⚙️ Installation & Setup

### 1. Clone or Add to Your Laravel Project
```bash
git clone https://github.com/yourusername/IdeaValidator.git
cd IdeaValidator
```

### 2. Install Dependencies
```bash
composer install
```

### 3. Configure the Gemini API Key
Open your `.env` file and add:
```env
GEMINI_API_KEY=your_google_gemini_api_key_here
```
You can create your key from [Google AI Studio](https://aistudio.google.com/app/apikey).

### 4. Update `config/services.php`
Ensure the file includes:
```php
'gemini' => [
    'api_key' => env('GEMINI_API_KEY'),
],
```

---

## 🧩 Usage Example

### BizValidatorService.php
```php
<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class BizValidatorService
{
    public function validateIdea(string $idea): string
    {
        $prompt = "
You are BizValidator, an expert startup analyst.
Analyse this business idea and output a structured validation:
1. Summary
2. Market Fit
3. Target Audience
4. Monetization Options
5. Competitive Advantage
6. Potential Risks
7. Validation Score (0-10)
8. Verdict: Proceed, Pivot, or Drop.

Business Idea: {$idea}
";

        $url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent';

        $response = Http::timeout(60)
            ->withHeaders(['Content-Type' => 'application/json'])
            ->post($url . '?key=' . config('services.gemini.api_key'), [
                'contents' => [
                    [
                        'parts' => [['text' => $prompt]]
                    ]
                ]
            ]);

        $json = $response->json();

        if (isset($json['candidates'][0]['content']['parts'][0]['text'])) {
            return $json['candidates'][0]['content']['parts'][0]['text'];
        }

        if (isset($json['error']['message'])) {
            return 'Gemini API Error: ' . $json['error']['message'];
        }

        return 'Unable to validate idea at this time.';
    }
}
```

---

## 🧭 Controller Example
```php
use App\Services\BizValidatorService;
use Illuminate\Http\Request;

class IdeaController extends Controller
{
    protected $validator;

    public function __construct(BizValidatorService $validator)
    {
        $this->validator = $validator;
    }

    public function validate(Request $request)
    {
        $idea = $request->input('idea');
        $result = $this->validator->validateIdea($idea);

        return response()->json(['validation' => $result]);
    }
}
```

### Example Route
```php
use App\Http\Controllers\IdeaController;

Route::post('/validate-idea', [IdeaController::class, 'validate']);
```

---

## 🧪 Test It

Run your local Laravel server:
```bash
php artisan serve
```

Then test via cURL:
```bash
curl -X POST http://localhost:8000/validate-idea      -H "Content-Type: application/json"      -d '{"idea": "AI-driven fitness app for seniors"}'
```

Example output:
```json
{
  "validation": "1. Summary: An AI-powered fitness app designed for seniors...
  2. Market Fit: Strong demand for health tech...
  ...
  8. Verdict: Proceed."
}
```

Or test from Tinker:
```bash
php artisan tinker
>>> app(\App\Services\BizValidatorService::class)->validateIdea('Smart composting IoT device')
```

---

## 🧰 Troubleshooting

### ❌ cURL Error 28 (Timeout)
Your local environment can’t reach Google’s API.  
Fixes:
- Check your network/firewall settings  
- Ensure `curl` and `openssl` are enabled in PHP  
- On Windows (XAMPP), configure SSL certificates:  
  1. Download `cacert.pem` from https://curl.se/ca/cacert.pem  
  2. Save to `C:\xampp\php\extras\ssl\cacert.pem`  
  3. Add to `php.ini`:
     ```ini
     curl.cainfo = "C:\xampp\php\extras\ssl\cacert.pem"
     openssl.cafile = "C:\xampp\php\extras\ssl\cacert.pem"
     ```
  4. Restart Apache.

### ❌ Invalid Model Name
Ensure your endpoint is correct:
```
https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent
```

### ❌ "Unable to validate idea at this time."
Enable logging in Laravel:
```php
\Log::info('Gemini response', $response->json());
```

---

## 🔐 Security Tips

- Never commit your API key to Git.  
- Always load secrets via `.env`.  
- Rotate API keys periodically in Google Cloud Console.

---

## 🧱 Example Prompt Used

```
You are BizValidator, an expert startup analyst.
Analyse this business idea and output a structured validation:
1. Summary
2. Market Fit
3. Target Audience
4. Monetization Options
5. Competitive Advantage
6. Potential Risks
7. Validation Score (0-10)
8. Verdict: Proceed, Pivot, or Drop.

Business Idea: {user input}
```

---

## 🚧 Future Improvements

- Add streaming response support (real-time validation)
- Store validation results in database
- Create a web UI with interactive analysis
- Use Gemini Pro for deeper business reasoning

---

## 🧾 License

**MIT License** © 2025 — Developed by *Oluwadamilola*

---

## ❤️ Contributing

Pull requests are welcome!  
If you encounter issues or have improvement ideas, open an issue or PR on GitHub.
