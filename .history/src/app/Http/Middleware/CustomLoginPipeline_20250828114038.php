<

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Laravel\Fortify\Fortify; // Fortifyクラスをインポート
use Illuminate\Validation\ValidationException; // ValidationExceptionをインポート
use App\Models\User; // Userモデルをインポート
use Illuminate\Support\Facades\Hash; // Hashファサードをインポート
use Illuminate\Support\Facades\Auth; // Authファサードをインポート

class CustomLoginPipeline
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // ここにFortifyServiceProviderのauthenticateUsingの中身を移植します。

        // バリデーション処理 (必要であれば、FormRequestに置き換えることも可能)
        // FortifyServiceProviderのauthenticateUsingの中身をそのまま持ってきます
        $loginRequest = new \App\Http\Requests\LoginRequest(); // あなたのLoginRequestを適切に指定してください
        $rules = $loginRequest->rules();
        $messages = $loginRequest->messages();

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        // 認証ロジック
        $user = User::where('email', $request->email)->first();

        if ($user && Hash::check($request->password, $user->password)) {
            // 認証成功
            Auth::login($user, $request->boolean('remember')); // remember me も考慮
            $request->session()->regenerate(); // セッション固定攻撃対策

            // FortifyのauthenticateUsingがユーザーを返すのと同じように、
            // ここではパイプラインの次の処理に進むか、直接レスポンスを返します。
            // 認証成功後、FortifyはFortify::redirects('login', ...) に従います。
            // そのため、ここでは認証が完了したことをFortifyに伝える必要があります。
            // FortifyのAuthenticatedSessionControllerは、loginPipelineからユーザーオブジェクトが返されることを期待します。
            // しかし、パイプラインミドルウェアはResponseを返す必要があります。

            // ここで直接Fortifyのパイプラインに認証済みユーザーを渡すのではなく、
            // 認証を完了させ、Fortifyのコントローラーが通常通りリダイレクトできるようにします。

            // 認証が成功したら、次のミドルウェアに進む
            return $next($request);

        }

        // 認証失敗
        throw ValidationException::withMessages([
            'email' => [trans('auth.failed')],
        ]);
    }
}