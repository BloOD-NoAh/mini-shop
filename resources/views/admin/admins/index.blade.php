<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Admins</h2>
            <a href="{{ route('admins.create') }}" class="btn-primary">Add Admin</a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="container-wide">
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

            <div class="card overflow-x-auto">
                <table class="table">
                    <thead class="thead">
                        <tr>
                            <th class="th">ID</th>
                            <th class="th">Name</th>
                            <th class="th">Email</th>
                            <th class="th">Created</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="tbody">
                        @foreach ($admins as $admin)
                            <tr>
                                <td class="td">{{ $admin->id }}</td>
                                <td class="td">{{ $admin->name }}</td>
                                <td class="td">{{ $admin->email }}</td>
                                <td class="td">{{ $admin->created_at?->format('Y-m-d') }}</td>
                                <td class="px-4 py-2 text-right space-x-2">
                                    <a href="{{ route('admins.edit', $admin) }}" class="btn-muted">Edit</a>
                                    <form action="{{ route('admins.destroy', $admin) }}" method="POST" class="inline" onsubmit="return confirm('Delete this admin?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn-danger">Delete</button>
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


