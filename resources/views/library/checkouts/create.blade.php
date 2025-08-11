<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('New Book Checkout') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form method="post" action="{{ route('checkouts.store') }}">
                        @csrf
                        <div class="space-y-6">
                            <div>
                                <x-input-label for="book_id" :value="__('Book ID')" />
                                <p class="text-sm text-gray-600 mb-1">Enter the ID of the book to be checked out. You can find this from the Book Catalog.</p>
                                <x-text-input id="book_id" name="book_id" type="number" class="mt-1 block w-full" :value="old('book_id')" required autofocus />
                                <x-input-error class="mt-2" :messages="$errors->get('book_id')" />
                            </div>

                            <div>
                                <x-input-label for="user_id" :value="__('Student ID')" />
                                <p class="text-sm text-gray-600 mb-1">Enter the ID of the student checking out the book. You can find this from the User Management section.</p>
                                <x-text-input id="user_id" name="user_id" type="number" class="mt-1 block w-full" :value="old('user_id')" required />
                                <x-input-error class="mt-2" :messages="$errors->get('user_id')" />
                            </div>

                            <div class="flex items-center gap-4">
                                <x-primary-button>{{ __('Check Out Book') }}</x-primary-button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
