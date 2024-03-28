<?php

namespace App\Http\Middleware;

use Closure;

class CheckRole
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $role
     * @return mixed
     */
    public function handle($request, Closure $next, $role)
    {
        $arr_level = explode(".", $role);
		if(!in_array(session('kdlevel'), $arr_level)){
			return response('Anda tidak memiliki akses ini!', 403);
		}
		
		return $next($request);
    }

}