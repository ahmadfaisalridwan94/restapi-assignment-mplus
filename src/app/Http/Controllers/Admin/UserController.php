<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;

use App\Interfaces\UserRepositoryInterface;
use App\Http\Resources\UserResource;
use App\Http\Resources\PaginateResource;
use App\Helpers\ResponseHelper;

class UserController extends Controller
{

    private UserRepositoryInterface $userRepository;
    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $users = $this->userRepository->getAll($request->search, $request->limit, true);
            return ResponseHelper::jsonResponse(true, 'success', UserResource::collection($users), 200);
        } catch (\Exception $e) {
            return ResponseHelper::jsonResponse(false, $e->getMessage(), null, 500);
        }
    }


    public function getAllPaginated(Request $request)
    {
        $request = $request->validate([
            'search' => 'nullable|string',
            'row_per_page' => 'required|integer',
        ]);

        try {
            $users = $this->userRepository->getAllPaginated($request['search'] ?? null, $request['row_per_page']);
            return ResponseHelper::jsonResponse(true, "0000", 'success', PaginateResource::make($users, UserResource::class), 200);
        } catch (\Exception $e) {
            return ResponseHelper::jsonResponse(false, "0001", $e->getMessage(), null, 500);
        }
    }
}
