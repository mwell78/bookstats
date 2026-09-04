<script setup>
import { ref } from 'vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import { Link } from '@inertiajs/vue3';
import { UserIcon, Square3Stack3DIcon, PlusIcon } from '@heroicons/vue/24/outline';

const showingNavigationDropdown = ref(false);
</script>

<template>
    <div>
        <div class="min-h-screen bg-base-200">
            <nav
                class="border-b border-base-content/10 bg-base-100 text-base-content"
            >
                <!-- Primary Navigation Menu -->
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="flex h-16 justify-between">
                        <div class="flex">
                            <!-- Logo -->
                            <div class="flex shrink-0 items-center">
                                <Link :href="route('dashboard')">
                                    <ApplicationLogo
                                        class="block h-9 w-9"
                                    />
                                </Link>
                                <h3 class="ml-2 text-xl font-serif">BookStats</h3>
                            </div>

                            <!-- Navigation Links -->
                            <div
                                class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex"
                            >
                                <NavLink
                                    :href="route('dashboard')"
                                    :active="route().current('dashboard')"
                                >
                                    Start
                                </NavLink>
                                <NavLink
                                    :href="route('books.index')"
                                    :active="route().current('books.index')"
                                >
                                    {{ __('Books') }}
                                </NavLink>
                                <NavLink
                                    :href="route('books.create')"
                                    :active="route().current('books.create')"
                                >
                                    {{ __('Add') }}
                                </NavLink>
                            </div>
                        </div>

                        <div class="hidden sm:ms-6 sm:flex sm:items-center">
                            <!-- Settings Dropdown -->
                            <div class="relative ms-3">
                                <Dropdown align="right" width="48">
                                    <template #trigger>
                                        <span class="inline-flex rounded-md">
                                            <button
                                                type="button"
                                                class="inline-flex items-center rounded-md border border-transparent px-3 py-2 text-sm font-medium leading-4 text-base-content/70 hover:text-base-content transition duration-150 ease-in-out"
                                            >
                                                {{ $page.props.auth.user.name }}

                                                <svg
                                                    class="-me-0.5 ms-2 h-4 w-4"
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    viewBox="0 0 20 20"
                                                    fill="currentColor"
                                                >
                                                    <path
                                                        fill-rule="evenodd"
                                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                        clip-rule="evenodd"
                                                    />
                                                </svg>
                                            </button>
                                        </span>
                                    </template>

                                    <template #content>
                                        <DropdownLink
                                            :href="route('profile.edit')"
                                        >
                                            {{ __('Profile') }}
                                        </DropdownLink>
                                        <DropdownLink
                                            :href="route('logout')"
                                            method="post"
                                            as="button"
                                        >
                                            {{ __('Log Out') }}
                                        </DropdownLink>
                                    </template>
                                </Dropdown>
                            </div>
                        </div>

                        <!-- Hamburger / Mobile Menu -->
                        <div class="-me-2 flex items-center sm:hidden gap-1">
                            <Link
                                :href="route('books.index')"
                                class="inline-flex items-center justify-center rounded-md p-2 text-base-content/60 hover:bg-base-200 hover:text-base-content transition duration-150 ease-in-out"
                                :class="{ 'text-primary': route().current('books.index') }"
                                title="Bücher"
                            >
                                <Square3Stack3DIcon class="h-6 w-6" />
                            </Link>
                            <Link
                                :href="route('books.create')"
                                class="inline-flex items-center justify-center rounded-md p-2 text-base-content/60 hover:bg-base-200 hover:text-base-content transition duration-150 ease-in-out"
                                :class="{ 'text-primary': route().current('books.create') }"
                                title="Hinzufügen"
                            >
                                <PlusIcon class="h-6 w-6" />
                            </Link>

                            <button
                                @click="
                                    showingNavigationDropdown =
                                        !showingNavigationDropdown
                                "
                                class="inline-flex items-center justify-center rounded-md p-2 text-base-content/60 hover:bg-base-200 hover:text-base-content transition duration-150 ease-in-out"
                                :class="{ 'bg-base-200 text-base-content': showingNavigationDropdown }"
                            >
                                <UserIcon class="h-6 w-6" />
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Responsive Navigation Menu -->
                <div
                    :class="{
                        block: showingNavigationDropdown,
                        hidden: !showingNavigationDropdown,
                    }"
                    class="sm:hidden"
                >
                    <!-- Responsive Settings Options -->
                    <div
                        class="border-t border-base-content/10"
                    >
                        <div class="px-4 py-3">
                            <div
                                class="text-base font-medium text-base-content"
                            >
                                {{ $page.props.auth.user.name }}
                            </div>
                            <div class="text-sm font-medium text-base-content/60">
                                {{ $page.props.auth.user.email }}
                            </div>
                        </div>

                        <div class="space-y-1 pb-3">
                            <ResponsiveNavLink :href="route('profile.edit')" :active="route().current('profile.edit')">
                                {{ __('Profile') }}
                            </ResponsiveNavLink>
                            <ResponsiveNavLink
                                :href="route('logout')"
                                method="post"
                                as="button"
                            >
                                {{ __('Log Out') }}
                            </ResponsiveNavLink>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Page Heading -->
            <header
                class=""
                v-if="$slots.header"
            >
                <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                    <slot name="header" />
                </div>
            </header>

            <!-- Page Content -->
            <main>
                <slot />
            </main>
        </div>
    </div>
</template>
