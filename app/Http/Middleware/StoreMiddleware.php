<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class StoreMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // in the case of API Request, make sure that the request contains store_id
        if ($request->is('api/*') && !$request->has('store_id')) {
            return response()->json(['message' => 'Missing storeID'], 400);
        }

        // ifsessionNo shopsID, get the first available store
        if (!session()->has('store_id')) {
            $store = Warehouse::where('is_store', true)
                ->where('status', true)
                ->first();
            
            // If no shop is available
            if (!$store) {
                // in the case ofAJAXRequest, returnJSONresponse
                if ($request->ajax()) {
                    return response()->json([
                        'message' => 'Please create a store first',
                        'redirect' => route('dashboard')
                    ], 403);
                }

                // Set display to create store modal box
                session(['show_create_store_modal' => true]);
                
                // If already indashboardPage, return directly
                if ($request->routeIs('dashboard')) {
                    return $next($request);
                }

                // Otherwise redirect todashboardAnd display the message
                return redirect()->route('dashboard')
                    ->with('warning', 'Please create a store before doing it');
            }
            
            // Save store information tosession
            session([
                'store_id' => $store->id,
                'store_name' => $store->name
            ]);
        } else {
            // Verify that the currently selected store is valid
            $store = Warehouse::where('id', session('store_id'))
                ->where('is_store', true)
                ->where('status', true)
                ->first();
                
            if (!$store) {
                // If the currently selected store is invalid, reselect the first available store
                $store = Warehouse::where('is_store', true)
                    ->where('status', true)
                    ->first();

                if ($store) {
                    session([
                        'store_id' => $store->id,
                        'store_name' => $store->name
                    ]);
                } else {
                    // in the case ofAJAXRequest, returnJSONresponse
                    if ($request->ajax()) {
                        return response()->json([
                            'message' => 'Please create a store first',
                            'redirect' => route('dashboard')
                        ], 403);
                    }

                    // Set display to create store modal box
                    session(['show_create_store_modal' => true]);
                    
                    // If already indashboardPage, return directly
                    if ($request->routeIs('dashboard')) {
                        return $next($request);
                    }

                    // Otherwise redirect todashboardAnd display the message
                    return redirect()->route('dashboard')
                        ->with('warning', 'Please create a store before doing it');
                }
            }
        }

        // Put the current storeIDAdd to all requests
        $request->merge(['store_id' => session('store_id')]);

        return $next($request);
    }
}
