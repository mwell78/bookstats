<script setup>
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    canLogin: {
        type: Boolean,
    },
    canRegister: {
        type: Boolean,
    },
});
</script>

<template>
    <Head title="Welcome" />
    <div class="hero min-h-screen bg-base-200">
        <div class="hero-content text-center">
            <div class="max-w-md">
                <div class="flex justify-center mb-4">
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-16 w-16 text-primary"
                        viewBox="0 0 24 24"
                        fill="currentColor"
                    >
                        <path
                            d="M11.25 4.533A9.707 9.707 0 0 0 6 3a9.735 9.735 0 0 0-3.25.555.75.75 0 0 0-.5.707v14.25a.75.75 0 0 0 1 .707A8.237 8.237 0 0 1 6 18.75c1.995 0 3.823.707 5.25 1.886V4.533ZM12.75 20.636A8.214 8.214 0 0 1 18 18.75c1.053 0 2.042.201 2.941.565a.75.75 0 0 0 1.059-.688V4.381a.75.75 0 0 0-.512-.706A9.73 9.73 0 0 0 18 3c-1.815 0-3.513.498-4.97 1.358A1.25 1.25 0 0 1 12.75 5.48v15.156Z"
                        />
                    </svg>
                </div>
                <h1 class="text-5xl font-bold font-serif">BookStats</h1>
                <p class="py-6 text-lg">
                    Verwalte deine persönliche Bibliothek und tracke deine Lese-Statistiken.
                </p>
                
                <div v-if="canLogin" class="flex justify-center gap-4">
                    <Link
                        v-if="$page.props.auth.user"
                        :href="route('dashboard')"
                        class="btn btn-primary"
                    >
                        Zum Dashboard
                    </Link>

                    <template v-else>
                        <Link
                            :href="route('login')"
                            class="btn btn-primary"
                        >
                            Login
                        </Link>

                        <Link
                            v-if="canRegister"
                            :href="route('register')"
                            class="btn btn-outline btn-primary"
                        >
                            Registrieren
                        </Link>
                    </template>
                </div>
            </div>
        </div>
    </div>
</template>
