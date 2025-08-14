<?php

namespace App\Traits;

trait FlashMessageTrait
{
    /**
     * Flash a success message to the session
     *
     * @param string $message
     * @param string $redirect_route
     * @param array $route_params
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function flashSuccess($message, $redirect_route = null, $route_params = [])
    {
        if ($redirect_route) {
            return redirect()->route($redirect_route, $route_params)->with('success', $message);
        }
        
        return back()->with('success', $message);
    }
    
    /**
     * Flash an error message to the session
     *
     * @param string $message
     * @param string $redirect_route
     * @param array $route_params
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function flashError($message, $redirect_route = null, $route_params = [])
    {
        if ($redirect_route) {
            return redirect()->route($redirect_route, $route_params)->with('error', $message);
        }
        
        return back()->with('error', $message);
    }
    
    /**
     * Flash a warning message to the session
     *
     * @param string $message
     * @param string $redirect_route
     * @param array $route_params
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function flashWarning($message, $redirect_route = null, $route_params = [])
    {
        if ($redirect_route) {
            return redirect()->route($redirect_route, $route_params)->with('warning', $message);
        }
        
        return back()->with('warning', $message);
    }
    
    /**
     * Flash an info message to the session
     *
     * @param string $message
     * @param string $redirect_route
     * @param array $route_params
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function flashInfo($message, $redirect_route = null, $route_params = [])
    {
        if ($redirect_route) {
            return redirect()->route($redirect_route, $route_params)->with('info', $message);
        }
        
        return back()->with('info', $message);
    }
    
    /**
     * Flash multiple messages at once
     *
     * @param array $messages ['success' => 'message', 'error' => 'message']
     * @param string $redirect_route
     * @param array $route_params
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function flashMultiple($messages, $redirect_route = null, $route_params = [])
    {
        $redirect = $redirect_route ? redirect()->route($redirect_route, $route_params) : back();
        
        foreach ($messages as $type => $message) {
            if (in_array($type, ['success', 'error', 'warning', 'info'])) {
                $redirect = $redirect->with($type, $message);
            }
        }
        
        return $redirect;
    }
    
    /**
     * Check if there are any session messages
     *
     * @return bool
     */
    protected function hasSessionMessages()
    {
        return session()->hasAny(['success', 'error', 'warning', 'info']);
    }
    
    /**
     * Get all session messages
     *
     * @return array
     */
    protected function getSessionMessages()
    {
        return [
            'success' => session('success'),
            'error' => session('error'),
            'warning' => session('warning'),
            'info' => session('info'),
        ];
    }
    
    /**
     * Clear all session messages
     *
     * @return void
     */
    protected function clearSessionMessages()
    {
        session()->forget(['success', 'error', 'warning', 'info']);
    }
}
