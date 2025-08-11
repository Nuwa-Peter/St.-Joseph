<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Manage Papers for') }} {{ $subject->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="mb-4 p-4 bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700">
                        <p class="font-bold">CAUTION</p>
                        <p>When marks for a subject are already entered, addition or removal of a paper(s) can affect existing records. Therefore, carry out this exercise before entry of marks starts!</p>
                    </div>

                    <form action="{{ route('subjects.store-papers', $subject) }}" method="POST"
                          x-data="{ papers: {{ json_encode($subject->papers->map(fn($p) => ['id' => $p->id, 'name' => $p->name])) }} }">
                        @csrf

                        <div id="papers-container" class="space-y-4">
                            <template x-for="(paper, index) in papers" :key="index">
                                <div class="flex items-center gap-4">
                                    <input type="hidden" :name="`papers[${index}][id]`" x-model="paper.id">
                                    <div class="flex-grow">
                                        <x-input-label :value="`Paper ${index + 1} Name`" />
                                        <x-text-input type="text" :name="`papers[${index}][name]`" x-model="paper.name" class="w-full" required />
                                    </div>
                                    <button type="button" @click="papers.splice(index, 1)" class="px-3 py-2 bg-red-500 text-white rounded-md mt-6">&times;</button>
                                </div>
                            </template>
                        </div>

                        <div class="mt-4">
                            <button type="button" @click="papers.push({ id: null, name: '' })" class="px-4 py-2 bg-gray-500 text-white rounded-md">
                                Add Another Paper
                            </button>
                        </div>

                        <div class="mt-6 border-t pt-4">
                            <x-primary-button>
                                {{ __('Save Papers') }}
                            </x-primary-button>
                            <a href="{{ route('subjects.index') }}" class="ml-4 text-gray-600 hover:underline">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
