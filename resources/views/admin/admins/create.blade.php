<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Add Admin</h2>
    </x-slot>

    <div class="py-6">
        <div class="container-narrow">
            @if ($errors->any())
                <div class="mb-4 p-4 rounded bg-red-100 text-red-800">
                    <ul class="list-disc ml-5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('admins.store') }}" method="POST" class="card space-y-4">
                @csrf
                <div>
                    <label class="input-label">Name</label>
                    <input name="name" value="{{ old('name') }}" class="input-field" required>
                </div>
                <div>
                    <label class="input-label">Email</label>
                    <input type="email" name="email" value="{{ old('email') }}" class="input-field" required>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="input-label">Password</label>
                        <input type="password" name="password" class="input-field" required>
                    </div>
                    <div>
                        <label class="input-label">Confirm Password</label>
                        <input type="password" name="password_confirmation" class="input-field" required>
                    </div>
                </div>
                <div class="flex items-center justify-end gap-2">
                    <a href="{{ route('admins.index') }}" class="btn-muted">Cancel</a>
                    <button class="btn-primary">Create Admin</button>
                </div>
            </form>
        </div>
    </div>
</x-admin-layout>


