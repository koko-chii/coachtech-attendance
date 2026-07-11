<?php

namespace App\Http\Requests\Auth;

// laravelが用意したFormRequest(入力チェック)機能を使うために呼び出す
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
// ログイン失敗時にエラーを発生させるための呼び出し
use Illuminate\Validation\ValidationException;

// FormRequest機能を継承したLoginRequest(オリジナルログインチェック機能)を
// 作成するためのクラス(設置)
class LoginRequest extends FormRequest
{
    /**
     *入力チェックの許可を判定するための関数(機能)
    */
    public function authorize(): bool
    {
        //誰でも許可します
        return true;
    }

    /**
     * 入力チェックのルールを決めるための関数(機能)
     */
    public function rules(): array
    {
        //メールアドレスとパスワードは入力必須、文字列であること
        return [
            'email' => ['required', 'string'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * エラーメッセージを決めるための関数(機能)
     */
    public function messages(): array
    {
        // メールアドレスとパスワードが未入力の場合のメッセージ
        return [
            // 未入力の場合
            'email.required' => 'メールアドレスを入力してください',
            'password.required' => 'パスワードを入力してください',
        ];
    }

    /**
     * ユーザーのログイン認証処理を実行する
     *
     * @return void 戻り値なし
     * @throws ValidationException 認証失敗時のバリデーションエラー
     */
    public function authenticate(): void
    {
        // 認証情報の取得
        $credentials = $this->only('email', 'password');

        // ログインに失敗した場合
        if (! Auth::attempt($credentials, $this->boolean('remember'))) {
            // lang/ja/auth.php の failed（ログイン情報が登録されていません）をセットしてエラーを返します
            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }
    }
}
