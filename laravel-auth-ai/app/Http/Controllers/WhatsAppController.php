<?php

namespace App\Http\Controllers;

use App\Modules\WaGateway\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class WhatsAppController extends Controller
{
    protected $whatsAppService;

    public function __construct(WhatsAppService $whatsAppService)
    {
        $this->whatsAppService = $whatsAppService;
    }

    /**
     * Send WhatsApp message
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function send(Request $request): JsonResponse
    {
        $request->validate([
            "target" => "required|string",
            "message" => "required|string",
            "url" => "nullable|string",
            "filename" => "nullable|string",
            "schedule" => "nullable|integer",
            "delay" => "nullable|string",
            "countryCode" => "nullable|string",
            "location" => "nullable|string",
            "typing" => "nullable|boolean",
            "choices" => "nullable|string",
            "select" => "nullable|string",
            "pollname" => "nullable|string",
            "connectOnly" => "nullable|boolean",
            "data" => "nullable|string",
            "sequence" => "nullable|boolean",
            "preview" => "nullable|boolean",
            "inboxid" => "nullable|integer",
            "duration" => "nullable|integer",
        ]);

        $options = $request->except(["target", "message"]);

        $response = $this->whatsAppService->sendMessage(
            $request->input("target"),
            $request->input("message"),
            $options
        );

        return response()->json($response);
    }
}
