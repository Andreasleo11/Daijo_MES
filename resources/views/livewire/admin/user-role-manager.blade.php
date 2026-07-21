<div class="space-y-6">
    <!-- Action Alerts -->
    @if (session()->has('message'))
        <div class="p-4 bg-emerald-50 border-l-4 border-emerald-500 text-emerald-800 rounded-md text-sm shadow-sm"
            role="alert">
            {{ session('message') }}
        </div>
    @endif
    @if (session()->has('error'))
        <div class="p-4 bg-rose-50 border-l-4 border-rose-500 text-rose-800 rounded-md text-sm shadow-sm" role="alert">
            {{ session('error') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- User List Panel -->
        <div
            class="lg:col-span-2 bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden flex flex-col justify-between">
            <div
                class="p-6 border-b border-slate-100 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h3 class="text-lg font-bold text-slate-800">System Users</h3>
                    <p class="text-xs text-slate-500">Manage status, roles, and profiles of all accounts.</p>
                </div>
                <div class="flex items-center gap-2">
                    <button wire:click="$set('showDeprecated', false)"
                        class="px-3 py-1.5 rounded-lg text-xs font-semibold {{ !$showDeprecated ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                        Active Users
                    </button>
                    <button wire:click="$set('showDeprecated', true)"
                        class="px-3 py-1.5 rounded-lg text-xs font-semibold {{ $showDeprecated ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                        Deprecated ({{ \App\Models\User::onlyTrashed()->count() }})
                    </button>
                </div>
            </div>

            <!-- Search & Filters -->
            <div class="px-6 py-4 bg-slate-50 border-b border-slate-100 flex items-center">
                <input wire:model.live.debounce.300ms="search" type="text"
                    placeholder="Search by name, email, username..."
                    class="w-full bg-white border border-slate-200 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-slate-900 focus:border-transparent transition" />
            </div>

            <!-- Users Table -->
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-sm">
                    <thead>
                        <tr class="bg-slate-50 text-slate-600 font-semibold border-b border-slate-100">
                            <th class="px-6 py-3">User Profile</th>
                            <th class="px-6 py-3">Role</th>
                            <th class="px-6 py-3 text-center">Status</th>
                            <th class="px-6 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($users as $user)
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="font-semibold text-slate-800">{{ $user->name }}</div>
                                    <div class="text-xs text-slate-500">{{ $user->email }} · @<span
                                            class="font-mono">{{ $user->username }}</span></div>
                                    @if ($user->zone)
                                        <span
                                            class="inline-block mt-1 text-[10px] bg-sky-50 text-sky-700 px-2 py-0.5 rounded font-medium border border-sky-100">Zone:
                                            {{ $user->zone->name }}</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    @if ($user->id === auth()->id())
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-violet-50 text-violet-700 border border-violet-100">
                                            {{ $user->role->name }} (You)
                                        </span>
                                    @else
                                        <select wire:change="changeRole({{ $user->id }}, $event.target.value)"
                                            class="text-xs bg-white border border-slate-200 rounded px-2 py-1 focus:outline-none focus:ring-1 focus:ring-slate-900">
                                            @foreach ($roles as $role)
                                                <option value="{{ $role->id }}"
                                                    {{ $user->role_id === $role->id ? 'selected' : '' }}>
                                                    {{ $role->name }}</option>
                                            @endforeach
                                        </select>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-center">
                                    @if ($user->id === auth()->id())
                                        <span
                                            class="inline-block w-2.5 h-2.5 bg-emerald-500 rounded-full ring-4 ring-emerald-50"></span>
                                    @else
                                        <button wire:click="toggleActivation({{ $user->id }})"
                                            class="focus:outline-none">
                                            @if ($user->is_active)
                                                <span
                                                    class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-100 hover:bg-emerald-100 transition">Active</span>
                                            @else
                                                <span
                                                    class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-600 border border-slate-200 hover:bg-slate-200 transition">Inactive</span>
                                            @endif
                                        </button>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex justify-end gap-2">
                                        @if ($showDeprecated)
                                            <button wire:click="restoreUser({{ $user->id }})"
                                                class="text-xs font-semibold bg-slate-900 text-white hover:bg-slate-800 px-3 py-1.5 rounded transition">
                                                Restore
                                            </button>
                                        @else
                                            <button wire:click="selectUserForPasswordChange({{ $user->id }})"
                                                class="text-xs font-semibold text-slate-600 hover:text-slate-950 hover:bg-slate-50 px-3 py-1.5 rounded transition border border-slate-200">
                                                Change PW
                                            </button>
                                            @if ($user->id !== auth()->id())
                                                <button wire:click="deprecateUser({{ $user->id }})"
                                                    onclick="confirm('Are you sure you want to deprecate this user? This will soft-delete their profile.') || event.stopImmediatePropagation()"
                                                    class="text-xs font-semibold text-rose-600 hover:text-rose-800 hover:bg-rose-50 px-3 py-1.5 rounded transition">
                                                    Deprecate
                                                </button>
                                            @endif
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-8 text-center text-slate-500">No users found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="p-6 border-t border-slate-100">
                {{ $users->links() }}
            </div>
        </div>

        <!-- Add User Form Panel -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6">
            <h3 class="text-lg font-bold text-slate-800 mb-1">Create Account</h3>
            <p class="text-xs text-slate-500 mb-6 font-medium">Add a new user credential to the MES platform.</p>

            <form wire:submit="createUser" class="space-y-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Username
                        (Optional)</label>
                    <input wire:model="username" type="text"
                        class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-slate-900 focus:outline-none" />
                    @error('username')
                        <span class="text-xs text-rose-600 mt-1 block">{{ $message }}</span>
                    @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Full
                        Name</label>
                    <input wire:model="name" type="text"
                        class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-slate-900 focus:outline-none" />
                    @error('name')
                        <span class="text-xs text-rose-600 mt-1 block">{{ $message }}</span>
                    @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Email
                        Address</label>
                    <input wire:model="email" type="email"
                        class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-slate-900 focus:outline-none" />
                    @error('email')
                        <span class="text-xs text-rose-600 mt-1 block">{{ $message }}</span>
                    @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Password</label>
                    <input wire:model="password" type="password"
                        class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-slate-900 focus:outline-none" />
                    @error('password')
                        <span class="text-xs text-rose-600 mt-1 block">{{ $message }}</span>
                    @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Role
                        Assignment</label>
                    <select wire:model="role_id"
                        class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-slate-900 focus:outline-none">
                        <option value="">Select Role</option>
                        @foreach ($roles as $role)
                            <option value="{{ $role->id }}">{{ $role->name }}</option>
                        @endforeach
                    </select>
                    @error('role_id')
                        <span class="text-xs text-rose-600 mt-1 block">{{ $message }}</span>
                    @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Zone
                        (Optional)</label>
                    <select wire:model="zone_id"
                        class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-slate-900 focus:outline-none">
                        <option value="">Select Zone</option>
                        @foreach ($zones as $zone)
                            <option value="{{ $zone->id }}">{{ $zone->name }}</option>
                        @endforeach
                    </select>
                    @error('zone_id')
                        <span class="text-xs text-rose-600 mt-1 block">{{ $message }}</span>
                    @enderror
                </div>

                <button type="submit"
                    class="w-full bg-slate-950 hover:bg-slate-900 text-white font-bold text-sm py-2.5 px-4 rounded-lg transition-colors mt-6 shadow-sm">
                    Create Account
                </button>
            </form>
        </div>
    </div>

    <!-- Force Change Password Modal -->
    <x-modal name="force-change-password-modal" :show="$errors->has('newPassword')" focusable>
        <form wire:submit="forceChangePassword" class="p-6">
            <h2 class="text-lg font-bold text-slate-800">
                Force Change Password
            </h2>

            <p class="mt-1 text-xs text-slate-500 font-medium">
                Change password for user: <span class="font-bold text-slate-700">{{ $selectedUserName }}</span>
            </p>

            <div class="mt-6">
                <label for="newPassword"
                    class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">New Password</label>

                <x-text-input wire:model="newPassword" id="newPassword" name="newPassword" type="password"
                    class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-slate-900 focus:outline-none"
                    placeholder="Enter new password" />

                <x-input-error :messages="$errors->get('newPassword')" class="mt-2" />
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <x-secondary-button x-on:click="$dispatch('close')">
                    Cancel
                </x-secondary-button>

                <button type="submit"
                    class="bg-slate-950 hover:bg-slate-900 text-white font-semibold text-xs py-2 px-4 rounded-lg transition-colors shadow-sm">
                    Save Password
                </button>
            </div>
        </form>
    </x-modal>
</div>
