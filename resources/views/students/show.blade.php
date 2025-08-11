<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Student Profile') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <!-- Student Header -->
                    <div class="flex items-center mb-6">
                        <img class="h-24 w-24 rounded-full object-cover mr-6" src="{{ $student->photo ? asset('storage/' . $student->photo) : 'https://ui-avatars.com/api/?name=' . urlencode($student->name) . '&color=7F9CF5&background=EBF4FF&size=128' }}" alt="{{ $student->name }}">
                        <div>
                            <h3 class="text-2xl font-bold text-gray-900">{{ $student->name }}</h3>
                            <p class="text-sm text-gray-500">{{ $student->unique_id }}</p>
                            <p class="text-sm text-gray-500">
                                Class: {{ $student->streams->first()->classLevel->name ?? 'N/A' }} {{ $student->streams->first()->name ?? '' }}
                            </p>
                        </div>
                    </div>

                    <!-- Main Content Tabs -->
                    <div x-data="{ tab: 'details' }">
                        <div class="border-b border-gray-200">
                            <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                                <a href="#" @click.prevent="tab = 'details'" :class="{ 'border-indigo-500 text-indigo-600': tab === 'details' }" class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300">
                                    Personal Details
                                </a>
                                <a href="#" @click.prevent="tab = 'discipline'" :class="{ 'border-indigo-500 text-indigo-600': tab === 'discipline' }" class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300">
                                    Discipline Log
                                </a>
                                <a href="#" @click.prevent="tab = 'accommodation'" :class="{ 'border-indigo-500 text-indigo-600': tab === 'accommodation' }" class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300">
                                    Accommodation
                                </a>
                                <a href="#" @click.prevent="tab = 'activities'" :class="{ 'border-indigo-500 text-indigo-600': tab === 'activities' }" class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300">
                                    Activities
                                </a>
                                <a href="#" @click.prevent="tab = 'admin_actions'" :class="{ 'border-indigo-500 text-indigo-600': tab === 'admin_actions' }" class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300">
                                    Admin Actions
                                </a>
                                @if(in_array(Auth::user()->role, [\App\Enums\Role::ROOT, \App\Enums\Role::HEADTEACHER]))
                                <a href="{{ route('students.health-record.edit', $student) }}" class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300">
                                    Manage Health Record
                                </a>
                                @endif
                            </nav>
                        </div>

                        <!-- Personal Details Tab Content -->
                        <div x-show="tab === 'details'" class="mt-6">
                            <dl class="grid grid-cols-1 gap-x-4 gap-y-8 sm:grid-cols-2">
                                <div class="sm:col-span-1">
                                    <dt class="text-sm font-medium text-gray-500">Full Name</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $student->name }}</dd>
                                </div>
                                <div class="sm:col-span-1">
                                    <dt class="text-sm font-medium text-gray-500">Email</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $student->email }}</dd>
                                </div>
                                @if($student->lin)
                                <div class="sm:col-span-1">
                                    <dt class="text-sm font-medium text-gray-500">Learner ID (LIN)</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $student->lin }}</dd>
                                </div>
                                @endif
                                <div class="sm:col-span-1">
                                    <dt class="text-sm font-medium text-gray-500">Gender</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $student->gender }}</dd>
                                </div>
                                <div class="sm:col-span-1">
                                    <dt class="text-sm font-medium text-gray-500">Date of Birth</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $student->date_of_birth ? \Carbon\Carbon::parse($student->date_of_birth)->format('M d, Y') : 'N/A' }}</dd>
                                </div>
                            </dl>
                        </div>

                        <!-- Discipline Log Tab Content -->
                        <div x-show="tab === 'discipline'" class="mt-6" x-cloak>
                            <div x-data="{ addLog: false }">
                                <div class="flex justify-between items-center mb-4">
                                    <h4 class="text-lg font-semibold text-gray-800">Discipline & Conduct Records</h4>
                                    <button @click="addLog = !addLog" class="inline-flex items-center px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600">
                                        <x-heroicon-o-plus class="w-5 h-5 mr-2"/> Add New Log
                                    </button>
                                </div>
                                <div x-show="addLog" x-collapse class="p-4 border rounded-lg bg-gray-50 mb-6">
                                    <form action="{{ route('students.discipline-logs.store', $student) }}" method="POST">
                                        @csrf
                                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                            <div class="md:col-span-1"><x-input-label for="log_date" :value="__('Date of Incident/Commendation')" /><x-text-input id="log_date" class="block mt-1 w-full" type="date" name="log_date" :value="old('log_date', now()->format('Y-m-d'))" required /><x-input-error :messages="$errors->get('log_date')" class="mt-2" /></div>
                                            <div class="md:col-span-1"><x-input-label for="type" :value="__('Log Type')" /><select name="type" id="type" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required><option value="incident" @selected(old('type') == 'incident')>Incident</option><option value="commendation" @selected(old('type') == 'commendation')>Commendation</option></select><x-input-error :messages="$errors->get('type')" class="mt-2" /></div>
                                            <div class="md:col-span-3"><x-input-label for="description" :value="__('Description')" /><textarea name="description" id="description" rows="4" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>{{ old('description') }}</textarea><x-input-error :messages="$errors->get('description')" class="mt-2" /></div>
                                        </div>
                                        <div class="flex justify-end mt-4"><x-primary-button>{{ __('Save Log') }}</x-primary-button></div>
                                    </form>
                                </div>
                            </div>
                            <div class="overflow-x-auto"><table class="min-w-full bg-white"><thead class="bg-gray-100"><tr><th class="py-2 px-4 text-left">Date</th><th class="py-2 px-4 text-left">Type</th><th class="py-2 px-4 text-left">Description</th><th class="py-2 px-4 text-left">Recorded By</th><th class="py-2 px-4 text-left">Actions</th></tr></thead><tbody>@forelse($student->disciplineLogs->sortByDesc('log_date') as $log)<tr class="border-b"><td class="py-2 px-4">{{ $log->log_date->format('M d, Y') }}</td><td class="py-2 px-4"><span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $log->type === 'incident' ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800' }}">{{ ucfirst($log->type) }}</span></td><td class="py-2 px-4">{{ $log->description }}</td><td class="py-2 px-4">{{ $log->recordedBy->name }}</td><td class="py-2 px-4"><form action="{{ route('discipline-logs.destroy', $log) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this log?');">@csrf @method('DELETE')<button type="submit" class="text-red-600 hover:text-red-900" title="Delete Log"><x-heroicon-o-trash class="w-5 h-5" /></button></form></td></tr>@empty<tr><td colspan="5" class="py-4 px-4 text-center text-gray-500">No discipline records found for this student.</td></tr>@endforelse</tbody></table></div>
                        </div>

                        <!-- Accommodation Tab Content -->
                        <div x-show="tab === 'accommodation'" class="mt-6" x-cloak>
                            <h4 class="text-lg font-semibold text-gray-800 mb-4">Accommodation Details</h4>
                            @if($student->roomAssignments->count() > 0)
                                <dl class="grid grid-cols-1 gap-x-4 gap-y-8 sm:grid-cols-2">
                                    @foreach($student->roomAssignments as $assignment)
                                    <div class="sm:col-span-1">
                                        <dt class="text-sm font-medium text-gray-500">Dormitory ({{ $assignment->academic_year }})</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ $assignment->room->dormitory->name }}</dd>
                                    </div>
                                    <div class="sm:col-span-1">
                                        <dt class="text-sm font-medium text-gray-500">Room Number</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ $assignment->room->room_number }}</dd>
                                    </div>
                                    @endforeach
                                </dl>
                            @else
                                <p class="text-gray-500">This student has no room assignment.</p>
                            @endif
                        </div>

                        <!-- Activities Tab Content -->
                        <div x-show="tab === 'activities'" class="mt-6" x-cloak>
                            <h4 class="text-lg font-semibold text-gray-800 mb-4">Extracurricular Activities</h4>
                             @if($student->clubs->count() > 0)
                                <ul class="list-disc pl-5 space-y-2">
                                    @foreach($student->clubs as $club)
                                        <li>{{ $club->name }}</li>
                                    @endforeach
                                </ul>
                            @else
                                <p class="text-gray-500">This student is not a member of any clubs.</p>
                            @endif
                        </div>

                        <!-- Admin Actions Tab Content -->
                        <div x-show="tab === 'admin_actions'" class="mt-6" x-cloak>
                             <h4 class="text-lg font-semibold text-gray-800 mb-4">Administrative Actions</h4>
                             @if(!$student->is_alumni)
                                <div class="p-4 border rounded-lg bg-gray-50">
                                    <h5 class="font-semibold">Transition to Alumni</h5>
                                    <p class="text-sm text-gray-600 mb-4">This will mark the student as graduated and move them to the alumni network. This action cannot be easily undone.</p>
                                    <form action="{{ route('students.graduate', $student) }}" method="POST">
                                        @csrf
                                        <div class="flex items-end space-x-4">
                                            <div>
                                                <x-input-label for="graduation_year" :value="__('Graduation Year')" />
                                                <x-text-input id="graduation_year" type="number" name="graduation_year" :value="date('Y')" required />
                                            </div>
                                            <div>
                                                <x-danger-button type="submit">
                                                    Graduate Student
                                                </x-danger-button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                             @else
                                <p class="text-gray-600">This user is already an alumnus (Graduated {{ $student->graduation_year }}).</p>
                             @endif
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
