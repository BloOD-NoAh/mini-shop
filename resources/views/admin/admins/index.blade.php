<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Admins</h2>
            <a href="{{ route('admins.create') }}" class="px-3 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">Add Admin</a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-4 p-4 rounded bg-green-100 text-green-800">{{ session('status') }}</div>
            @endif
            @if ($errors->any())
                <div class="mb-4 p-4 rounded bg-red-100 text-red-800">
                    <ul class="list-disc ml-5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="bg-white shadow rounded overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach ($admins as $admin)
                            <tr>
                                <td class="px-4 py-2 text-sm text-gray-700">{{ $admin->id }}</td>
                                <td class="px-4 py-2 text-sm text-gray-900">{{ $admin->name }}</td>
                                <td class="px-4 py-2 text-sm text-gray-900">{{ $admin->email }}</td>
                                <td class="px-4 py-2 text-sm text-gray-900">{{ $admin->created_at?->format('Y-m-d') }}</td>
                                <td class="px-4 py-2 text-right space-x-2">
                                    <a href="{{ route('admins.edit', $admin) }}" class="px-3 py-1 bg-gray-800 text-white text-sm rounded hover:bg-gray-900">Edit</a>
                                    <form action="{{ route('admins.destroy', $admin) }}" method="POST" class="inline" onsubmit="return confirm('Delete this admin?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="px-3 py-1 bg-red-600 text-white text-sm rounded hover:bg-red-700">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-4">{{ $admins->links() }}</div>
        </div>
    </div>
</x-admin-layout>

