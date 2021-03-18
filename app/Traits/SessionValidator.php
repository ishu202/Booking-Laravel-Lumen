<?php


namespace R7\Booking\Traits;


trait SessionValidator
{
    public function create_item_session($request){
        if (!$request->session()->has('items')) {
            $request->session()->put($request->get('items'));
        }

        return $request->session()->get('items');
    }

    public function create_user_session($request)
    {
        if (!$request->session()->has('user')){
            $request->session()->put($request->get('user'));
        }

        return $request->session()->get('user');
    }

    public function destroy_user_session($request)
    {
        $request->session()->forget('user');
    }

    public function destroy_item_session($request)
    {
        $request->session()->forget('items');
    }

}
