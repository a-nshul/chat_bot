<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Storage;
use Smalot\PdfParser\Parser; 
class ChatController extends Controller
{
    public function sendMessage(Request $request)
{
    try {
        $message = $request->input('message');
        $file = $request->file('file');

        $chatHistory = session()->get('chat_history', []);
        $fileContent = '';

        if ($file) {
            $path = $file->storeAs('uploads', $file->getClientOriginalName());
            $extension = $file->getClientOriginalExtension();
            if ($extension === 'pdf') {
                $fileContent = $this->processFile($path, $extension);
            }
        }

        $chatHistory[] = ['sender' => 'user', 'message' => $message];

        $client = new Client();
        $apiRequest = [
            'headers' => [
                'Authorization' => 'Bearer ' . env('OPENAI_API_KEY'),
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'model' => 'gpt-4',
                'messages' => [
                    ['role' => 'system', 'content' => 'You are a helpful assistant.'],
                    ['role' => 'user', 'content' => $message],
                    ['role' => 'user', 'content' => $fileContent],
                ],
            ]
        ];

        $response = $client->post('https://api.openai.com/v1/chat/completions', $apiRequest);
        $data = json_decode($response->getBody()->getContents(), true);
        $botReply = $data['choices'][0]['message']['content'];
        $chatHistory[] = ['sender' => 'bot', 'message' => $botReply];
        
        session()->put('chat_history', $chatHistory);
        return response()->json(['reply' => $botReply]);

    } catch (\Exception $e) {
        return response()->json(['error' => 'Unable to process your request at the moment.'], 500);
    }
}


    // Process file content (PDF text extraction or returning file URL)
    private function processFile($path, $extension)
    {
        if ($extension === 'pdf') {
            $pdf = new Parser();
            $pdfDocument = $pdf->parseFile(Storage::path($path));
            return $pdfDocument->getText(); // Return text extracted from PDF
        } else {
            // For images, just return the path to display
            return '/storage/' . $path;
        }
    }
}