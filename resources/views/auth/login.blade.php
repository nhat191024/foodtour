<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div class="mb-4 text-center">
            <a href="{{ route('login.google') }}" class="btn btn-block btn-soft">
                <svg class="w-5 h-5 mr-2" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12.545,10.239v3.821h5.445c-0.712,2.315-2.647,3.972-5.445,3.972c-3.332,0-6.033-2.701-6.033-6.032s2.701-6.032,6.033-6.032c1.498,0,2.866,0.549,3.921,1.453l2.814-2.814C17.503,2.988,15.139,2,12.545,2C7.021,2,2.543,6.477,2.543,12s4.478,10,10.002,10c8.396,0,10.249-7.85,9.426-11.748L12.545,10.239z"/>
                </svg>
                {{ __('Đăng nhập với Google') }}
            </a>
        </div>

        <!-- Email Address -->
        <div>
            <x-inputs.label for="email" :value="__('Email')" />
            <x-inputs.text id="email" class="mt-1 block w-full" name="email" type="email" required :value="old('email')"
                autofocus autocomplete="username" />
            <x-inputs.error class="mt-2" :messages="$errors->get('email')" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-inputs.label for="password" :value="__('Password')" />

            <x-inputs.text id="password" class="mt-1 block w-full" name="password" type="password" required
                autocomplete="current-password" />

            <x-inputs.error class="mt-2" :messages="$errors->get('password')" />
        </div>

        <!-- Remember Me -->
        <div class="mt-4 block">
            <label class="inline-flex items-center" for="remember_me">
                <input id="remember_me"
                    class="shadow-xs rounded-sm border-gray-300 text-indigo-600 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:focus:ring-indigo-600 dark:focus:ring-offset-gray-800"
                    name="remember" type="checkbox">
                <span class="ms-2 text-sm text-gray-600 dark:text-gray-400">{{ __('Remember me') }}</span>
            </label>
        </div>

        <div class="mt-4 flex items-center justify-end gap-2">
            @if (Route::has('password.request'))
                <a class="focus:outline-hidden rounded-md text-sm text-gray-600 underline hover:text-gray-900 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:text-gray-400 dark:hover:text-gray-100 dark:focus:ring-offset-gray-800"
                    href="{{ route('register') }}">
                    {{ __('Chưa có tài khoản?') }}
                </a>
                <a class="focus:outline-hidden rounded-md text-sm text-gray-600 underline hover:text-gray-900 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:text-gray-400 dark:hover:text-gray-100 dark:focus:ring-offset-gray-800"
                    href="{{ route('password.request') }}">
                    {{ __('Quên mật khẩu?') }}
                </a>
            @endif

            <x-buttons.primary class="ms-3">
                {{ __('Log in') }}
            </x-buttons.primary>
        </div>
    </form>
</x-guest-layout>
