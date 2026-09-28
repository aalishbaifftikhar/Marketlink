@extends(dashboard_layout())

@section('title', 'Profile settings')

@section('content')
    <div class="mx-auto max-w-3xl px-4 py-10 sm:px-6">
        <h1 class="font-display text-3xl font-semibold text-leaf-950 dark:text-cream-50">Profile settings</h1>
        <p class="mt-1.5 text-stone-500 dark:text-stone-400">Update your details and how you appear across {{ settings('site_name', 'MarketLink') }}.</p>

        <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="card mt-8 space-y-5 p-6">
            @csrf
            @method('PUT')

            <div class="flex items-center gap-5">
                <img src="{{ $user->avatarUrl() }}" alt="" class="h-20 w-20 rounded-3xl object-cover">
                <div>
                    <label for="avatar" class="input-label">Profile photo</label>
                    <input id="avatar" name="avatar" type="file" accept="image/*"
                        class="mt-1 block w-full text-sm text-stone-500 file:mr-3 file:rounded-full file:border-0 file:bg-leaf-100 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-leaf-700 hover:file:bg-leaf-200 dark:text-stone-400 dark:file:bg-leaf-900 dark:file:text-leaf-300">
                    <p class="mt-1 text-xs text-stone-400 dark:text-stone-500">PNG or JPG, up to 2 MB.</p>
                    <x-input-error field="avatar" />
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="name" class="input-label">Full name</label>
                    <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}" required class="input">
                    <x-input-error field="name" />
                </div>
                <div>
                    <label for="phone" class="input-label">Phone</label>
                    <input id="phone" name="phone" type="tel" value="{{ old('phone', $user->phone) }}" class="input"
                        inputmode="numeric" maxlength="15" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                    <p class="mt-1 text-xs text-stone-400 dark:text-stone-500">Numbers only, 10–15 digits.</p>
                    <x-input-error field="phone" />
                </div>
            </div>

            <div>
                <label for="email" class="input-label">Email</label>
                <input id="email" type="email" value="{{ $user->email }}" disabled class="input opacity-60">
                <p class="mt-1 text-xs text-stone-400 dark:text-stone-500">Email can't be changed as it's tied to your sign-in.</p>
            </div>

            <div>
                <label for="address" class="input-label">Address</label>
                <input id="address" name="address" type="text" value="{{ old('address', $user->address) }}" class="input">
                <x-input-error field="address" />
            </div>

            <div class="flex justify-end border-t border-stone-100 pt-5 dark:border-leaf-800">
                <button type="submit" class="btn-primary">Save changes</button>
            </div>
        </form>

        <form method="POST" action="{{ route('profile.password') }}" class="card mt-8 space-y-5 p-6" x-data="passwordRules">
            @csrf
            @method('PUT')

            <div>
                <h2 class="font-display text-xl font-semibold text-leaf-950 dark:text-cream-50">Change password</h2>
                <p class="mt-1 text-sm text-stone-500 dark:text-stone-400">Not needed if you sign in with Google only.</p>
            </div>

            <div>
                <label for="current_password" class="input-label">Current password</label>
                <input id="current_password" name="current_password" type="password" required class="input" autocomplete="current-password">
                <x-input-error field="current_password" />
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="password" class="input-label">New password</label>
                    <input id="password" name="password" type="password" required class="input" autocomplete="new-password" x-model="password">
                    <x-input-error field="password" />
                </div>
                <div>
                    <label for="password_confirmation" class="input-label">Confirm new password</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" required class="input" autocomplete="new-password" x-model="confirmation">
                    <p class="mt-1 text-xs" x-cloak x-show="confirmation !== ''"
                        :class="confirmationMatches ? 'text-leaf-700 dark:text-leaf-300' : 'text-red-600 dark:text-red-400'"
                        x-text="confirmationMatches ? 'Passwords match' : 'Passwords do not match yet'"></p>
                </div>
            </div>

            <x-password-rules />

            <div class="flex justify-end border-t border-stone-100 pt-5 dark:border-leaf-800">
                <button type="submit" class="btn-primary" x-bind:disabled="!ready">Update password</button>
            </div>
        </form>
    </div>
@endsection
