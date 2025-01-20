<div>
    @error('invalidCredentials')
    <span>{{ $message }}</span>
    @enderror

    @error('rateLimiter')
    <span>{{ $message }}</span>
    @enderror
</div>
