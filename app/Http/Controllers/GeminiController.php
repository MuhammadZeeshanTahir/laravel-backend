<?php

namespace App\Http\Controllers;

use Gemini\Laravel\Facades\Gemini;
use Gemini\Data\Blob;
use Illuminate\Http\Request;

class GeminiController extends Controller
{
    /**
     * UPDATED: Using 'gemini-2.0-flash' based on your list.
     * You can also try 'gemini-2.5-flash' or 'gemini-2.5-pro'.
     */
    protected string $model = 'gemini-2.0-flash';

    /**
     * 1. Text Generation
     */
    public function generate(Request $request)
    {
        $request->validate(['prompt' => 'required|string']);

        try {
            $result = Gemini::generativeModel(model: $this->model)
                ->generateContent($request->input('prompt'));

            return response()->json([
                'success' => true,
                'model_used' => $this->model,
                'response' => $result->text(),
            ]);

        } catch (\Exception $e) {
            return $this->handleError($e);
        }
    }

    /**
     * 2. Chat (Conversation)
     */
    public function chat(Request $request)
    {
        $request->validate([
            'message' => 'required|string',
            'history' => 'array',
        ]);

        try {
            $chat = Gemini::generativeModel(model: $this->model)
                ->startChat(history: $request->input('history', []));

            $response = $chat->sendMessage($request->input('message'));

            return response()->json([
                'success' => true,
                'response' => $response->text(),
            ]);

        } catch (\Exception $e) {
            return $this->handleError($e);
        }
    }

    /**
     * 3. Image Analysis
     * Gemini 2.0/2.5 are multimodal by default.
     */
    public function describeImage(Request $request)
    {
        $request->validate([
            'image' => 'required|image|max:10240', // 10MB limit
            'prompt' => 'nullable|string'
        ]);

        try {
            $file = $request->file('image');
            $mimeType = $file->getMimeType();
            $imageData = base64_encode(file_get_contents($file->getRealPath()));

            $result = Gemini::generativeModel(model: $this->model)
                ->generateContent([
                    $request->input('prompt', 'Describe this image'),
                    new Blob(mimeType: $mimeType, data: $imageData)
                ]);

            return response()->json([
                'success' => true,
                'description' => $result->text(),
            ]);

        } catch (\Exception $e) {
            return $this->handleError($e);
        }
    }

    /**
     * Helper to list models (Keep this if you need to check again later)
     */
    public function listModels()
    {
        try {
            $response = Gemini::models()->list();
            $available = [];
            foreach ($response->models as $model) {
                if (in_array('generateContent', $model->supportedGenerationMethods)) {
                    $available[] = $model->name;
                }
            }
            return response()->json(['models' => $available]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()]);
        }
    }

    private function handleError(\Exception $e)
    {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
            'suggestion' => 'Check if the model name is still valid in /api/gemini/models'
        ], 500);
    }
}
