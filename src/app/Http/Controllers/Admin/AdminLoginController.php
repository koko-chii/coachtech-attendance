<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
// AdminLoginRequestクラスをこのファイルで利用するための読み込み
use App\Http\Requests\AdminLoginRequest;

// Laravelの認証機能を使うためのインポート
use Illuminate\Support\Facades\Auth;

// バリデーションエラー時の例外処理に使うためのインポート
use Illuminate\Validation\ValidationException;

// 別のページへ移動する機能を使うためのインポート
use Illuminate\Http\RedirectResponse;

// 画面表示機能を使うためのインポート
use Illuminate\View\View;

// リクエスト機能(セッション破棄など)を使うための読み込み
use Illuminate\Http\Request;

// Laravelのコントローラー機能を継承したクラス
class AdminLoginController extends Controller
{
    /**
     * 管理者ログイン画面を表示
     *
     * @return View 管理者ログイン画面のビュー
     */
    public function showLoginForm(): View
    {
        // 管理者ログイン画面を表示
        return view('admin.auth.login');
    }

    /**
     * 管理者ログインの認証処理を行う
     *
     * @param AdminLoginRequest $request ログイン入力データが入った箱
     * @return RedirectResponse 認証成功後のリダイレクト先
     * @throws ValidationException 認証失敗時のバリデーションエラー
     */
    public function login(AdminLoginRequest $request): RedirectResponse
    {
        // メールアドレスとパスワードを取得
        $credentials = $request->only('email', 'password');

        $credentials['admin_status'] = true;

        // ログイン後に管理者かチェック 認証されたら管理者用勤怠一覧画面に遷移
        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->route('admin.attendance.list');
        }

        // 認証失敗時はエラーメッセージを返す
        throw ValidationException::withMessages([
            'login_failed' => 'ログイン情報が登録されていません',
        ]);
    }

    /**
     * 管理者ログアウト処理を行う
     *
     * @param Request $request セッション操作用のリクエストデータが入った箱
     * @return RedirectResponse ログアウト後のリダイレクト先
     */
    public function logout(Request $request): RedirectResponse
    {
        // ログイン時の認証ガードからログアウト
        Auth::logout();

        // 現在のセッションを無効化し、トークンを作り直す
        // CSRFトークンは正規画面から送信されたことを証明する秘密の文字列
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // ログアウト後は管理者ログイン画面へ戻る
        return redirect()->route('admin.login');
    }
}
