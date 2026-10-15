<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;

class Controller extends BaseController
{
    protected function page(Request $request, string $view, array $data = [])
    {
        return view($view, array_merge($data, [
            'errors' => $request->session()->get('form_errors', []),
            'old' => $request->session()->get('old', []),
            'flashSuccess' => $request->session()->get('success'),
            'authUser' => $request->attributes->get('auth_user'),
        ]));
    }

    protected function validateForm(Request $request, array $rules, array $messages = [])
    {
        $validator = app('validator')->make($request->all(), $rules, $messages);
        if ($validator->fails()) {
            $request->session()->flash('form_errors', $validator->errors()->all());
            $request->session()->flash('old', $request->except(['password', 'password_confirmation', 'current_password', '_token']));
            return null;
        }

        return $validator->validated();
    }

    protected function invalid(Request $request, string $path)
    {
        return redirect($path);
    }

    protected function currentUser(Request $request)
    {
        return $request->attributes->get('auth_user');
    }

    protected function companyId(Request $request)
    {
        return $this->currentUser($request)->company_id;
    }
}
