<?php

namespace Packages\Core\Src\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller;

abstract class BaseController extends Controller
{
    use AuthorizesRequests, ValidatesRequests;

    /**
     * Return success JSON response
     */
    protected function success($data = null, string $message = 'Success', int $code = 200)
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $code);
    }

    /**
     * Return error JSON response
     */
    protected function error(string $message = 'Error', int $code = 400, $errors = null)
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => $errors,
        ], $code);
    }

    /**
     * Flash success message and redirect
     *
     * @param  string  $route  Route name
     * @param  string  $message  Flash message
     * @param  array  $params  Route parameters (e.g., ['id' => 1, 'slug' => 'example'])
     */
    protected function redirectWithSuccess(string $route, string $message, array $params = [])
    {
        return redirect()->route($route, $params)->with('success', $message);
    }

    /**
     * Flash error message and redirect
     *
     * @param  string  $route  Route name
     * @param  string  $message  Flash message
     * @param  array  $params  Route parameters
     */
    protected function redirectWithError(string $route, string $message, array $params = [])
    {
        return redirect()->route($route, $params)->with('error', $message);
    }

    /**
     * Flash success message and redirect back
     */
    protected function backWithSuccess(string $message)
    {
        return redirect()->back()->with('success', $message);
    }

    /**
     * Flash error message and redirect back
     */
    protected function backWithError(string $message)
    {
        return redirect()->back()->with('error', $message);
    }
}
