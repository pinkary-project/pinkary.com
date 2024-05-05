<x-app-layout>

    <div class="py-12">
        <div class="mx-auto max-w-2xl space-y-6 sm:px-6 lg:px-8">

            <h1 class="font-mona text-2xl font-medium text-slate-200">Settings</h1>

            <div x-data="{ selectedTab: 'profile-info' }" class="w-full">

                <div
                    @keydown.right.prevent="$focus.wrap().next()"
                    @keydown.left.prevent="$focus.wrap().previous()"
                    class="flex overflow-x-auto border-b border-slate-700 justify-center"
                    role="tablist"
                    aria-label="tab options"
                >

                    <button
                        @click="selectedTab = 'profile-info'"
                        :aria-selected="selectedTab === 'profile-info'"
                        :tabindex="selectedTab === 'profile-info' ? '0' : '-1'"
                        :class="selectedTab === 'profile-info' ? 'text-pink-500 border-b-2 border-pink-500' : 'text-slate-300 hover:border-b-slate-300 hover:border-b-slate-800 hover:text-white'"
                        class="h-min px-4 py-2 text-sm flex items-center"
                        type="button"
                        role="tab"
                        aria-controls="profile-info-tab"
                    >
                        <x-icons.user class="h-6 w-6 xsm:mr-3" />
                        <span class="hidden xsm:inline">Profile</span>
                    </button>

                    <button
                        @click="selectedTab = 'update-password'"
                        :aria-selected="selectedTab === 'update-password'"
                        :tabindex="selectedTab === 'update-password' ? '0' : '-1'"
                        :class="selectedTab === 'update-password' ? 'text-pink-500 border-b-2 border-pink-500' : 'text-slate-300 hover:border-b-slate-300 hover:border-b-slate-800 hover:text-white'"
                        class="h-min px-4 py-2 text-sm flex items-center"
                        type="button"
                        role="tab"
                        aria-controls="update-password-tab"
                    >
                        <x-icons.key class="h-6 w-6 xsm:mr-3" />
                        <span class="hidden xsm:inline">Password</span>
                    </button>

                    <button
                        @click="selectedTab = 'get-verified'"
                        :aria-selected="selectedTab === 'get-verified'"
                        :tabindex="selectedTab === 'get-verified' ? '0' : '-1'"
                        :class="selectedTab === 'get-verified' ? 'text-pink-500 border-b-2 border-pink-500' : 'text-slate-300 hover:border-b-slate-300 hover:border-b-slate-800 hover:text-white'"
                        class="h-min px-4 py-2 text-sm flex items-center"
                        type="button"
                        role="tab"
                        aria-controls="get-verified-tab"
                    >
                        <x-icons.verified :color="$user->is_verified ? $user->right_color : 'gray'" class="h-6 w-6 xsm:mr-3"/>
                        <span class="hidden xsm:inline">Get Verified</span>
                    </button>

                    <button
                        @click="selectedTab = 'delete-account'"
                        :aria-selected="selectedTab === 'delete-account'"
                        :tabindex="selectedTab === 'delete-account' ? '0' : '-1'"
                        :class="selectedTab === 'delete-account' ? 'text-pink-500 border-b-2 border-pink-500' : 'text-slate-300 hover:border-b-slate-300 hover:border-b-slate-800 hover:text-white'"
                        class="h-min px-4 py-2 text-sm flex items-center"
                        type="button"
                        role="tab"
                        aria-controls="delete-account-tab"
                    >
                        <x-icons.trash class="h-6 w-6 xsm:mr-3" />
                        <span class="hidden xsm:inline">Delete Account</span>
                    </button>
                </div>

                <div class="px-2 py-4 text-slate-700 dark:text-slate-300">
                    <div
                        x-show="selectedTab === 'profile-info'"
                        id="profile-info-tab"
                        role="tabpanel"
                        aria-label="profile information"
                    >
                        <div class="p-4 shadow sm:rounded-lg sm:p-8">
                            <div class="max-w-xl">
                                @include('profile.partials.upload-profile-photo-form')
                            </div>
                        </div>
                        <div class="p-4 shadow sm:rounded-lg sm:p-8">
                            <div class="max-w-xl">
                                @include('profile.partials.update-profile-information-form')
                            </div>
                        </div>
                    </div>

                    <div
                        x-show="selectedTab === 'update-password'"
                        id="update-password-tab"
                        role="tabpanel"
                        aria-label="get verified"
                    >
                         <div class="p-4 shadow sm:rounded-lg sm:p-8">
                            <div class="max-w-xl">
                                @include('profile.partials.update-password-form')
                            </div>
                        </div>
                    </div>

                    <div
                        x-show="selectedTab === 'get-verified'"
                        id="get-verified-tab"
                        role="tabpanel"
                        aria-label="get verified"
                    >
                         <div class="p-4 shadow sm:rounded-lg sm:p-8">
                            <div class="max-w-xl">
                                @include('profile.partials.verified-form')
                            </div>
                        </div>
                    </div>

                    <div
                        x-show="selectedTab === 'delete-account'"
                        id="delete-account-tab"
                        role="tabpanel"
                        aria-label="get verified"
                    >
                         <div class="p-4 shadow sm:rounded-lg sm:p-8">
                            <div class="max-w-xl">
                                @include('profile.partials.delete-user-form')
                            </div>
                        </div>
                    </div>

                </div>

            </div>
        </div>
    </div>
</x-app-layout>
