<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Edit Admin #{{ $admin->id }}</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            @if ($errors->any())
                <div class="mb-4 p-4 rounded bg-red-100 text-red-800">
                    <ul class="list-disc ml-5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('admins.update', $admin) }}" method="POST" class="bg-white shadow rounded p-6 space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label class="block text-sm font-medium text-gray-700">Name</label>
                    <input name="name" value="{{ old('name', $admin->name) }}" class="mt-1 w-full rounded border-gray-300" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Email</label>
                    <input type="email" name="email" value="{{ old('email', $admin->email) }}" class="mt-1 w-full rounded border-gray-300" required>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">New Password (optional)</label>
                        <input type="password" name="password" class="mt-1 w-full rounded border-gray-300">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Confirm Password</label>
                        <input type="password" name="password_confirmation" class="mt-1 w-full rounded border-gray-300">
                    </div>
                </div>
                <div class="flex items-center justify-end gap-2">
                    <a href="{{ route('admins.index') }}" class="px-4 py-2 bg-gray-100 text-gray-700 rounded hover:bg-gray-200">Cancel</a>
                    <button class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">Save</button>
                </div>
            </form>
        </div>
    </div>
</x-admin-layout>

