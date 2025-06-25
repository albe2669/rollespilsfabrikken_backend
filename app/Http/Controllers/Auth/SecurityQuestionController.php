<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\Auth\SecurityQuestion\Destroy;
use App\Http\Requests\API\Auth\SecurityQuestion\Index;
use App\Http\Requests\API\Auth\SecurityQuestion\Show;
use App\Http\Requests\API\Auth\SecurityQuestion\Store;
use App\Http\Requests\API\Auth\SecurityQuestion\Update;
use App\Http\Resources\SecurityQuestion\SecurityQuestion as SecurityQuestionResource;
use App\Http\Resources\SecurityQuestion\SecurityQuestionWithoutAnswer;
use App\Models\SecurityQuestion;
use Illuminate\Http\JsonResponse;

class SecurityQuestionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function index(Index $request)
    {
        return response()->json([
            'message' => 'success',
            'security_questions' => SecurityQuestionResource::collection(SecurityQuestion::all()),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return JsonResponse
     */
    public function store(Store $request)
    {
        $securityQuestion = (new SecurityQuestion)->create($request->validated());

        return response()->json([
            'message' => 'success',
            'security_question' => new SecurityQuestionResource($securityQuestion->refresh()),
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @return JsonResponse
     */
    public function show(Show $request)
    {
        $question = (new SecurityQuestion)
            ->inRandomOrder()
            ->limit(1)
            ->first();

        if ($question == null) {
            return response()->json([
                'message' => 'There is no security questions, contact an administrator',
            ], 404);
        }

        return response()->json([
            'message' => 'success',
            'security_question' => new SecurityQuestionWithoutAnswer($question),
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @return JsonResponse
     */
    public function update(Update $request, SecurityQuestion $securityQuestion)
    {
        $securityQuestion->update($request->validated());

        return response()->json([
            'message' => 'success',
            'security_question' => new SecurityQuestionResource($securityQuestion->refresh()),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return JsonResponse
     */
    public function destroy(Destroy $request, SecurityQuestion $securityQuestion)
    {
        $securityQuestion->delete();

        return response()->json([
            'message' => 'success',
        ]);
    }
}
