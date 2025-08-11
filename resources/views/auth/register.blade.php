<x-guest-layout>
    <form method="POST" action="{{ route('register') }}">
        @csrf

        <!-- First Name -->
        <div>
            <x-input-label for="first_name" :value="__('First Name')" />
            <x-text-input id="first_name" class="block mt-1 w-full" type="text" name="first_name" :value="old('first_name')" required autofocus autocomplete="given-name" />
            <x-input-error :messages="$errors->get('first_name')" class="mt-2" />
        </div>

        <!-- Last Name -->
        <div class="mt-4">
            <x-input-label for="last_name" :value="__('Last Name')" />
            <x-text-input id="last_name" class="block mt-1 w-full" type="text" name="last_name" :value="old('last_name')" required autocomplete="family-name" />
            <x-input-error :messages="$errors->get('last_name')" class="mt-2" />
        </div>

        <!-- Other Name -->
        <div class="mt-4">
            <x-input-label for="other_name" :value="__('Other Name (Optional)')" />
            <x-text-input id="other_name" class="block mt-1 w-full" type="text" name="other_name" :value="old('other_name')" autocomplete="additional-name" />
            <x-input-error :messages="$errors->get('other_name')" class="mt-2" />
        </div>

        <!-- Gender -->
        <div class="mt-4">
            <x-input-label for="gender" :value="__('Gender')" />
            <select id="gender" name="gender" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                <option value="Male">Male</option>
                <option value="Female">Female</option>
            </select>
            <x-input-error :messages="$errors->get('gender')" class="mt-2" />
        </div>

        <!-- LIN -->
        <div class="mt-4">
            <x-input-label for="lin" :value="__('Learner Identification Number (LIN)')" />
            <x-text-input id="lin" class="block mt-1 w-full" type="text" name="lin" :value="old('lin')" />
            <x-input-error :messages="$errors->get('lin')" class="mt-2" />
        </div>

        <!-- Email Address -->
        <div class="mt-4">
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4" x-data="{ password: '', confirmation: '' }">
            <x-input-label for="password" :value="__('Password')" />

            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="new-password"
                            x-model="password"
                            @input="validatePassword" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />

            <!-- Password Strength Indicators -->
            <div id="password-strength" class="mt-2 text-sm space-y-1">
                <p id="length" class="text-red-500">Minimum 8 characters</p>
                <p id="letter" class="text-red-500">At least one letter</p>
                <p id="symbol" class="text-red-500">At least one symbol or number</p>
            </div>

            <!-- Confirm Password -->
            <div class="mt-4">
                <x-input-label for="password_confirmation" :value="__('Confirm Password')" />

                <x-text-input id="password_confirmation" class="block mt-1 w-full"
                                type="password"
                                name="password_confirmation" required autocomplete="new-password"
                                x-model="confirmation" />

                <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                <p id="match" class="text-red-500 mt-2 text-sm hidden">Passwords do not match</p>
            </div>
        </div>

        <!-- Confirm Password -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" />

            <x-text-input id="password_confirmation" class="block mt-1 w-full"
                            type="password"
                            name="password_confirmation" required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('login') }}">
                {{ __('Already registered?') }}
            </a>

            <x-primary-button class="ms-4">
                {{ __('Register') }}
            </x-primary-button>
        </div>
    </form>

    <script>
        function validatePassword() {
            const password = this.password;
            const confirmation = this.confirmation;

            const length = document.getElementById('length');
            const letter = document.getElementById('letter');
            const symbol = document.getElementById('symbol');
            const match = document.getElementById('match');

            // Length check
            if (password.length >= 8) {
                length.classList.remove('text-red-500');
                length.classList.add('text-green-500');
            } else {
                length.classList.add('text-red-500');
                length.classList.remove('text-green-500');
            }

            // Letter check
            if (/[a-zA-Z]/.test(password)) {
                letter.classList.remove('text-red-500');
                letter.classList.add('text-green-500');
            } else {
                letter.classList.add('text-red-500');
                letter.classList.remove('text-green-500');
            }

            // Symbol/Number check
            if (/[0-9\W]/.test(password)) {
                symbol.classList.remove('text-red-500');
                symbol.classList.add('text-green-500');
            } else {
                symbol.classList.add('text-red-500');
                symbol.classList.remove('text-green-500');
            }

            // Match check
            if (confirmation) {
                if (password === confirmation) {
                    match.classList.add('hidden');
                } else {
                    match.classList.remove('hidden');
                }
            }
        }
    </script>
</x-guest-layout>
