<aside class="w-64 bg-gray-800 text-white h-screen flex flex-col">
    <div class="p-4 text-center flex-shrink-0">
        <a href="{{ route('dashboard') }}">
            <img src="{{ asset('images/logo.png') }}" alt="School Logo" class="w-20 h-20 mx-auto mb-2 rounded-full">
            <h1 class="text-xl font-bold text-white">St. Joseph's VSS</h1>
        </a>
    </div>

    @php
        $user = auth()->user();
        $userRole = $user->role;
        $isAdmin = in_array($userRole, [\App\Enums\Role::ROOT, \App\Enums\Role::HEADTEACHER]);
    @endphp

    <nav class="flex-1 overflow-y-auto px-4">
        <ul class="space-y-2">
            {{-- ========== STUDENT MENU ========== --}}
            @if($userRole === \App\Enums\Role::STUDENT)
                <li>
                    <a href="{{ route('student.dashboard') }}" class="flex items-center p-2 text-gray-300 hover:bg-gray-700 rounded-md {{ request()->routeIs('student.dashboard') ? 'bg-gray-700' : '' }}">
                        <x-heroicon-o-home class="w-6 h-6 mr-3" />
                        <span>Dashboard</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('videos.index') }}" class="flex items-center p-2 text-gray-300 hover:bg-gray-700 rounded-md {{ request()->routeIs('videos.index') ? 'bg-gray-700' : '' }}">
                        <x-heroicon-o-video-camera class="w-6 h-6 mr-3" />
                        <span>Video Library</span>
                    </a>
                </li>

            {{-- ========== PARENT MENU ========== --}}
            @elseif($userRole === \App\Enums\Role::PARENT)
                <li>
                     <a href="{{ route('parent.dashboard') }}" class="flex items-center p-2 text-gray-300 hover:bg-gray-700 rounded-md {{ request()->routeIs('parent.dashboard') ? 'bg-gray-700' : '' }}">
                        <x-heroicon-o-home class="w-6 h-6 mr-3" />
                        <span>Dashboard</span>
                    </a>
                </li>

            {{-- ========== ADMIN & STAFF MENU ========== --}}
            @else
                <li>
                    <a href="{{ route('dashboard') }}" class="flex items-center p-2 text-gray-300 hover:bg-gray-700 rounded-md {{ request()->routeIs('dashboard') ? 'bg-gray-700' : '' }}">
                        <x-heroicon-o-home class="w-6 h-6 mr-3" />
                        <span>Dashboard</span>
                    </a>
                </li>

                @if($isAdmin)
                    <li>
                        <x-sidebar-dropdown :active="request()->routeIs(['users.*', 'students.*'])">
                            <x-slot name="trigger"><x-heroicon-o-users class="w-6 h-6 mr-3" /><span>User Management</span></x-slot>
                            <x-slot name="content">
                                <a href="{{ route('users.index') }}" class="block p-2 text-sm text-gray-300 hover:bg-gray-700 rounded-md">All Users</a>
                                <a href="{{ route('users.create') }}" class="block p-2 text-sm text-gray-300 hover:bg-gray-700 rounded-md">Add New User</a>
                                <a href="{{ route('students.index') }}" class="block p-2 text-sm text-gray-300 hover:bg-gray-700 rounded-md">Student Profiles</a>
                            </x-slot>
                        </x-sidebar-dropdown>
                    </li>
                    <li>
                        <x-sidebar-dropdown :active="request()->routeIs(['class-levels.*', 'subjects.*', 'teacher-assignments.*', 'student-assignments.*'])">
                            <x-slot name="trigger"><x-heroicon-o-academic-cap class="w-6 h-6 mr-3" /><span>Academics</span></x-slot>
                            <x-slot name="content">
                                <a href="{{ route('class-levels.index') }}" class="block p-2 text-sm text-gray-300 hover:bg-gray-700 rounded-md">Classes & Streams</a>
                                <a href="{{ route('subjects.index') }}" class="block p-2 text-sm text-gray-300 hover:bg-gray-700 rounded-md">Subjects</a>
                                <a href="{{ route('teacher-assignments.create') }}" class="block p-2 text-sm text-gray-300 hover:bg-gray-700 rounded-md">Assign Teachers</a>
                                <a href="{{ route('student-assignments.index') }}" class="block p-2 text-sm text-gray-300 hover:bg-gray-700 rounded-md">Assign Students</a>
                            </x-slot>
                        </x-sidebar-dropdown>
                    </li>
                @endif

                @if(in_array($userRole, [\App\Enums\Role::ROOT, \App\Enums\Role::HEADTEACHER, \App\Enums\Role::BURSAR]))
                <li>
                    <x-sidebar-dropdown :active="request()->routeIs(['invoices.*', 'fee-structures.*', 'expenses.*', 'reports.*', 'fee-categories.*', 'expense-categories.*'])">
                        <x-slot name="trigger"><x-heroicon-o-banknotes class="w-6 h-6 mr-3" /><span>Finance</span></x-slot>
                        <x-slot name="content">
                            <a href="{{ route('invoices.index') }}" class="block p-2 text-sm text-gray-300 hover:bg-gray-700 rounded-md">Invoices</a>
                            <a href="{{ route('fee-structures.index') }}" class="block p-2 text-sm text-gray-300 hover:bg-gray-700 rounded-md">Fee Structures</a>
                            <a href="{{ route('expenses.index') }}" class="block p-2 text-sm text-gray-300 hover:bg-gray-700 rounded-md">Expenses</a>
                            <a href="{{ route('reports.index') }}" class="block p-2 text-sm text-gray-300 hover:bg-gray-700 rounded-md">Reports</a>
                            <a href="{{ route('fee-categories.index') }}" class="block p-2 text-sm text-gray-300 hover:bg-gray-700 rounded-md border-t border-gray-600 mt-1 pt-1">Fee Categories</a>
                            <a href="{{ route('expense-categories.index') }}" class="block p-2 text-sm text-gray-300 hover:bg-gray-700 rounded-md">Expense Categories</a>
                        </x-slot>
                    </x-sidebar-dropdown>
                </li>
                @endif

                @if(in_array($userRole, [\App\Enums\Role::ROOT, \App\Enums\Role::HEADTEACHER, \App\Enums\Role::LIBRARIAN]))
                <li>
                    <x-sidebar-dropdown :active="request()->routeIs(['books.*', 'checkouts.*'])">
                        <x-slot name="trigger"><x-heroicon-o-book-open class="w-6 h-6 mr-3" /><span>Library</span></x-slot>
                        <x-slot name="content">
                            <a href="{{ route('books.index') }}" class="block p-2 text-sm text-gray-300 hover:bg-gray-700 rounded-md">Book Catalog</a>
                            <a href="{{ route('checkouts.index') }}" class="block p-2 text-sm text-gray-300 hover:bg-gray-700 rounded-md">Book Checkouts</a>
                        </x-slot>
                    </x-sidebar-dropdown>
                </li>
                @endif

                @if(in_array($userRole, [\App\Enums\Role::ROOT, \App\Enums\Role::HEADTEACHER, \App\Enums\Role::BURSAR, \App\Enums\Role::TEACHER]))
                <li>
                    <x-sidebar-dropdown :active="request()->routeIs(['inventory.*', 'bookings.*', 'resources.*'])">
                        <x-slot name="trigger"><x-heroicon-o-archive-box class="w-6 h-6 mr-3" /><span>Resources</span></x-slot>
                        <x-slot name="content">
                            @if(in_array($userRole, [\App\Enums\Role::ROOT, \App\Enums\Role::HEADTEACHER, \App\Enums\Role::TEACHER]))
                                <a href="{{ route('bookings.index') }}" class="block p-2 text-sm text-gray-300 hover:bg-gray-700 rounded-md">Resource Bookings</a>
                            @endif
                            @if(in_array($userRole, [\App\Enums\Role::ROOT, \App\Enums\Role::HEADTEACHER, \App\Enums\Role::BURSAR]))
                                <a href="{{ route('inventory.index') }}" class="block p-2 text-sm text-gray-300 hover:bg-gray-700 rounded-md">General Inventory</a>
                            @endif
                             @if($isAdmin)
                                <a href="{{ route('resources.index') }}" class="block p-2 text-sm text-gray-300 hover:bg-gray-700 rounded-md border-t border-gray-600 mt-1 pt-1">Manage Resources</a>
                            @endif
                        </x-slot>
                    </x-sidebar-dropdown>
                </li>
                @endif

                @if($isAdmin)
                <li>
                    <x-sidebar-dropdown :active="request()->routeIs(['dormitories.*', 'room-assignments.*', 'clubs.*'])">
                        <x-slot name="trigger"><x-heroicon-o-user-group class="w-6 h-6 mr-3" /><span>Welfare & Activities</span></x-slot>
                        <x-slot name="content">
                            <a href="{{ route('dormitories.index') }}" class="block p-2 text-sm text-gray-300 hover:bg-gray-700 rounded-md">Manage Dormitories</a>
                            <a href="{{ route('room-assignments.index') }}" class="block p-2 text-sm text-gray-300 hover:bg-gray-700 rounded-md">Room Assignments</a>
                            <a href="{{ route('clubs.index') }}" class="block p-2 text-sm text-gray-300 hover:bg-gray-700 rounded-md">Manage Clubs</a>
                        </x-slot>
                    </x-sidebar-dropdown>
                </li>
                <li>
                    <a href="{{ route('announcements.index') }}" class="flex items-center p-2 text-gray-300 hover:bg-gray-700 rounded-md {{ request()->routeIs('announcements.*') ? 'bg-gray-700' : '' }}">
                        <x-heroicon-o-megaphone class="w-6 h-6 mr-3" />
                        <span>Announcements</span>
                    </a>
                </li>
                <li>
                    <x-sidebar-dropdown :active="request()->routeIs('documents.*')">
                        <x-slot name="trigger"><x-heroicon-o-document-text class="w-6 h-6 mr-3" /><span>Documents</span></x-slot>
                        <x-slot name="content">
                            <a href="{{ route('documents.id-card.select') }}" class="block p-2 text-sm text-gray-300 hover:bg-gray-700 rounded-md">Generate ID Card</a>
                            <a href="{{ route('documents.report-card.select') }}" class="block p-2 text-sm text-gray-300 hover:bg-gray-700 rounded-md">Generate Report Card</a>
                        </x-slot>
                    </x-sidebar-dropdown>
                </li>
                @endif

                @if(in_array($userRole, [\App\Enums\Role::ROOT, \App\Enums\Role::TEACHER, \App\Enums\Role::HEADTEACHER]))
                <li>
                     <x-sidebar-dropdown :active="request()->routeIs(['teacher.chat.*', 'bulk-messages.*'])">
                        <x-slot name="trigger"><x-heroicon-o-chat-bubble-left-right class="w-6 h-6 mr-3" /><span>Communication</span></x-slot>
                        <x-slot name="content">
                            <a href="{{ route('teacher.chat.index') }}" class="block p-2 text-sm text-gray-300 hover:bg-gray-700 rounded-md">Social Chat</a>
                            <a href="{{ route('bulk-messages.create') }}" class="block p-2 text-sm text-gray-300 hover:bg-gray-700 rounded-md">Bulk Messages</a>
                        </x-slot>
                    </x-sidebar-dropdown>
                </li>
                @endif

                @if(in_array($userRole, [\App\Enums\Role::ROOT, \App\Enums\Role::HEADTEACHER, \App\Enums\Role::TEACHER]))
                <li>
                    <x-sidebar-dropdown :active="request()->routeIs(['marks.*', 'exams.*'])">
                        <x-slot name="trigger"><x-heroicon-o-pencil-square class="w-6 h-6 mr-3" /><span>Examinations</span></x-slot>
                        <x-slot name="content">
                            <a href="{{ route('exams.index') }}" class="block p-2 text-sm text-gray-300 hover:bg-gray-700 rounded-md">View Set Exams</a>
                            <a href="{{ route('exams.create') }}" class="block p-2 text-sm text-gray-300 hover:bg-gray-700 rounded-md">Set Exam</a>
                            <a href="{{ route('marks.index') }}" class="block p-2 text-sm text-gray-300 hover:bg-gray-700 rounded-md border-t border-gray-600 mt-1 pt-1">Mark Entry</a>
                        </x-slot>
                    </x-sidebar-dropdown>
                </li>
                @endif

                {{-- This is the new location for Advanced and About --}}
                @if($isAdmin)
                <li>
                    <x-sidebar-dropdown :active="request()->routeIs(['admin.chat.*', 'admin.backups.*', 'admin.ai.*'])">
                        <x-slot name="trigger"><x-heroicon-o-cog class="w-6 h-6 mr-3" /><span>Advanced</span></x-slot>
                        <x-slot name="content">
                            <a href="{{ route('admin.ai.index') }}" class="block p-2 text-sm text-gray-300 hover:bg-gray-700 rounded-md">AI Reports</a>
                            <a href="{{ route('admin.backups.index') }}" class="block p-2 text-sm text-gray-300 hover:bg-gray-700 rounded-md">Database Backups</a>
                            <a href="{{ route('admin.chat.index') }}" class="block p-2 text-sm text-gray-300 hover:bg-gray-700 rounded-md">Admin Chat</a>
                        </x-slot>
                    </x-sidebar-dropdown>
                </li>
                @endif
                <li>
                    <a href="{{ route('about') }}" class="flex items-center p-2 text-gray-300 hover:bg-gray-700 rounded-md {{ request()->routeIs('about') ? 'bg-gray-700' : '' }}">
                        <x-heroicon-o-information-circle class="w-6 h-6 mr-3" />
                        <span>About</span>
                    </a>
                </li>

            @endif
        </ul>
    </nav>

    <div class="p-4 flex-shrink-0 border-t border-gray-700">
        <div x-data="{ open: false }" class="relative">
            <button @click="open = !open" class="flex items-center w-full p-2 text-gray-300 hover:bg-gray-700 rounded-md">
                <img class="w-8 h-8 rounded-full mr-3" src="{{ $user->getAvatarUrl() }}" alt="User Avatar">
                <span class="flex-1 text-left">{{ $user->name }}</span>
                 <x-heroicon-o-chevron-up-down class="w-5 h-5" />
            </button>
            <div x-show="open" @click.away="open = false" x-transition class="absolute bottom-full w-full mb-2 bg-gray-700 rounded-md shadow-lg" style="display: none;">
                <a href="{{ route('profile.edit') }}" class="block w-full text-left px-4 py-2 text-sm text-gray-300 hover:bg-gray-600">My Profile</a>
                <a href="{{ route('notifications.index') }}" class="block w-full text-left px-4 py-2 text-sm text-gray-300 hover:bg-gray-600">Notifications</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-gray-300 hover:bg-gray-600">
                        Logout
                    </button>
                </form>
            </div>
        </div>
    </div>
</aside>
