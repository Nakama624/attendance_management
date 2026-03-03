<form action="/login" class="login-form" method="post">
    @csrf
    <h1 class="content-title">{{ $title }}</h1>

    <div class="form-input">
        <label for="email" class="form__label--item">メールアドレス</label>
        <input id="email" type="text" name="email" class="form__input--item"
            value="{{ old('email') }}" />
        <div class="form__error">
            @error('email')
            {{ $message }}
            @enderror
        </div>
    </div>

    <div class="form-input">
        <label for="password" class="form__label--item">パスワード</label>
        <input id="password" type="password" name="password" class="form__input--item"/>
        <div class="form__error">
            @error('password')
            {{ $message }}
            @enderror
        </div>
    </div>

    <!-- 管理者の場合のみ -->
    @isset($isAdmin)
        <input type="hidden" name="is_admin_login" value="1">
    @endisset
    
    <div class="form__button">
        <button class="form__button-submit" type="submit">{{ $btn }}</button>
        <a class="form__button-tran" href="/register">会員登録はこちら</a>
    </div>
</form>
