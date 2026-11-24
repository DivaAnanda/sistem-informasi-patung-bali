<x-layouts.admin title="Masuk">
    <div class="mx-auto mt-10 max-w-sm rounded-2xl border border-stone-200 bg-white p-6 shadow-sm">
        <div class="mb-6 flex items-center gap-2.5">
            <x-logo-mark class="size-8 text-brand-700" />
            <h1 class="font-display text-xl font-semibold">Masuk admin</h1>
        </div>

        <form method="POST" action="{{ route('admin.login.store') }}" class="space-y-4">
            @csrf
            <div>
                <label for="email" class="mb-1 block text-sm font-medium">Email</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                       class="w-full rounded-lg border border-stone-300 px-3 py-2 focus:border-brand-600 focus:outline-none">
            </div>
            <div>
                <label for="password" class="mb-1 block text-sm font-medium">Kata sandi</label>
                <input id="password" name="password" type="password" required autocomplete="current-password"
                       class="w-full rounded-lg border border-stone-300 px-3 py-2 focus:border-brand-600 focus:outline-none">
            </div>

            @error('email')<p class="text-sm text-red-700">{{ $message }}</p>@enderror
            @error('password')<p class="text-sm text-red-700">{{ $message }}</p>@enderror

            <button class="w-full rounded-full bg-brand-600 px-4 py-2.5 font-medium text-white hover:bg-brand-700">Masuk</button>
        </form>
    </div>
</x-layouts.admin>
