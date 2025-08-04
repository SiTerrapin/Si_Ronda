<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Login;

class LoginController extends Controller
{
    public function index(){
        return view('auth.login');
    }
    public function login(Request $request){
        $isi = $request->validate([
            'nama'=>'required|string',
            'password'=>'required'
        ]);
        
        $islogin = Login::where('nama',$isi['nama'])->first();
        if($islogin && $islogin->password === $isi['password']){
            $request->session()->put('nama', $islogin->nama);
            return redirect('/beranda')->with('success', "login sukses");
        }else{
            return back()->withErrors([
                'nama' => 'Nama atau password salah',
            ])->withInput();
        }
    }
    public function logout(Request $request)
{
    $request->session()->flush(); // hapus semua data session
    return redirect('/')->with('success', 'Berhasil logout.');
}   
}
