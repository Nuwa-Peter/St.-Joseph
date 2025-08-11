<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('System Audit Trail') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-700 mb-4">All Recorded Actions</h3>

                    <div class="overflow-x-auto bg-white rounded-lg shadow overflow-y-auto relative">
                        <table class="border-collapse table-auto w-full whitespace-no-wrap bg-white table-striped relative">
                            <thead>
                                <tr class="text-left">
                                    <th class="bg-gray-50 sticky top-0 border-b border-gray-200 px-6 py-3 text-gray-600 font-bold tracking-wider uppercase text-xs">Timestamp</th>
                                    <th class="bg-gray-50 sticky top-0 border-b border-gray-200 px-6 py-3 text-gray-600 font-bold tracking-wider uppercase text-xs">User</th>
                                    <th class="bg-gray-50 sticky top-0 border-b border-gray-200 px-6 py-3 text-gray-600 font-bold tracking-wider uppercase text-xs">Action</th>
                                    <th class="bg-gray-50 sticky top-0 border-b border-gray-200 px-6 py-3 text-gray-600 font-bold tracking-wider uppercase text-xs">Target</th>
                                    <th class="bg-gray-50 sticky top-0 border-b border-gray-200 px-6 py-3 text-gray-600 font-bold tracking-wider uppercase text-xs">Changes</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($logs as $log)
                                    <tr>
                                        <td class="border-dashed border-t border-gray-200 px-6 py-3">{{ $log->created_at->format('Y-m-d H:i:s') }}</td>
                                        <td class="border-dashed border-t border-gray-200 px-6 py-3">{{ $log->user->name ?? 'System' }}</td>
                                        <td class="border-dashed border-t border-gray-200 px-6 py-3">
                                            <span class="font-semibold capitalize">{{ $log->action }}</span>
                                        </td>
                                        <td class="border-dashed border-t border-gray-200 px-6 py-3">
                                            {{ Str::afterLast($log->auditable_type, '\\') }} #{{ $log->auditable_id }}
                                        </td>
                                        <td class="border-dashed border-t border-gray-200 px-6 py-3 text-xs">
                                            @if($log->old_values)
                                                <strong>Old:</strong> <pre class="whitespace-pre-wrap bg-red-50 p-1 rounded"><code>{{ json_encode($log->old_values, JSON_PRETTY_PRINT) }}</code></pre>
                                            @endif
                                            @if($log->new_values)
                                                <strong>New:</strong> <pre class="whitespace-pre-wrap bg-green-50 p-1 rounded"><code>{{ json_encode($log->new_values, JSON_PRETTY_PRINT) }}</code></pre>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-10 px-6 text-gray-500">
                                            No audit logs found.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                     <div class="mt-4">
                        {{ $logs->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
