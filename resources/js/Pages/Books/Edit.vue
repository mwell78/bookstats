<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';

const props = defineProps({
    book: Object,
});

const form = useForm({
    title: props.book.title,
    subtitle: props.book.subtitle || '',
    author: props.book.author || '',
    isbn: props.book.isbn || '',
    pages: props.book.pages || '',
    format: props.book.format || 'Hardcover',
    published_year: props.book.published_year || '',
    genre: props.book.genre || '',
    status: props.book.status || 'Ungelesen',
    started_at: props.book.started_at || '',
    finished_at: props.book.finished_at || '',
    notes: props.book.notes || '',
    cover_image: props.book.cover_image || '',
    cover_file: null,
});

const submit = () => {
    form.transform((data) => ({
        ...data,
        _method: 'PATCH',
    })).post(route('books.update', props.book.id), {
        onSuccess: () => {
            console.log('Book updated successfully');
        },
    });
};
</script>

<template>
    <Head title="Buch bearbeiten" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-base-content">
                Buch bearbeiten: {{ book.title }}
            </h2>
        </template>

        <div class="py-4">
            <div class="mx-auto max-w-2xl sm:px-6 lg:px-8">
                <div class="bg-base-100 overflow-hidden shadow-sm sm:rounded-lg p-6">

                    <form @submit.prevent="submit" class="space-y-4">
                        <div v-if="form.cover_image" class="flex justify-center mb-4">
                            <img :src="form.cover_image" alt="Cover Preview" class="h-48 shadow-lg" />
                        </div>

                        <div>
                            <label class="label">Titel</label>
                            <input v-model="form.title" type="text" class="input input-bordered w-full" required />
                            <InputError :message="form.errors.title" class="mt-2" />
                        </div>

                        <div>
                            <label class="label">Untertitel</label>
                            <input v-model="form.subtitle" type="text" class="input input-bordered w-full" />
                            <InputError :message="form.errors.subtitle" class="mt-2" />
                        </div>

                        <div>
                            <label class="label">Autor</label>
                            <input v-model="form.author" type="text" class="input input-bordered w-full" />
                            <InputError :message="form.errors.author" class="mt-2" />
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="label">Seiten</label>
                                <input v-model="form.pages" type="number" class="input input-bordered w-full" />
                                <InputError :message="form.errors.pages" class="mt-2" />
                            </div>
                            <div>
                                <label class="label">Status</label>
                                <select v-model="form.status" class="select select-bordered w-full">
                                    <option>Ungelesen</option>
                                    <option>Am lesen</option>
                                    <option>Gelesen</option>
                                    <option>Pausiert</option>
                                    <option>Abgebrochen</option>
                                </select>
                                <InputError :message="form.errors.status" class="mt-2" />
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="label">Format</label>
                                <select v-model="form.format" class="select select-bordered w-full">
                                    <option>Hardcover</option>
                                    <option>Taschenbuch</option>
                                    <option>E-Book</option>
                                    <option>Hörbuch</option>
                                </select>
                                <InputError :message="form.errors.format" class="mt-2" />
                            </div>
                            <div>
                                <label class="label">Erscheinungsjahr</label>
                                <input v-model="form.published_year" type="text" class="input input-bordered w-full" placeholder="z.B. 2023" />
                                <InputError :message="form.errors.published_year" class="mt-2" />
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="label">Gestartet am</label>
                                <input v-model="form.started_at" type="date" class="input input-bordered w-full" />
                                <InputError :message="form.errors.started_at" class="mt-2" />
                            </div>
                            <div>
                                <label class="label">Beendet am</label>
                                <input v-model="form.finished_at" type="date" class="input input-bordered w-full" />
                                <InputError :message="form.errors.finished_at" class="mt-2" />
                            </div>
                        </div>

                        <div>
                            <label class="label">Notizen</label>
                            <textarea v-model="form.notes" class="textarea textarea-bordered w-full" rows="3"></textarea>
                            <InputError :message="form.errors.notes" class="mt-2" />
                        </div>

                        <div>
                            <label class="label">Cover</label>
                            <div class="space-y-2">
                                <input v-model="form.cover_image" type="text" placeholder="Cover URL..." class="input input-bordered w-full" />
                                <div class="flex items-center gap-2">
                                    <div class="h-px bg-base-content/10 flex-1"></div>
                                    <span class="text-xs text-base-content/40 uppercase font-bold">oder</span>
                                    <div class="h-px bg-base-content/10 flex-1"></div>
                                </div>
                                <input type="file" @input="form.cover_file = $event.target.files[0]" class="file-input file-input-bordered w-full" accept="image/*" />
                            </div>
                            <InputError :message="form.errors.cover_image" class="mt-2" />
                            <InputError :message="form.errors.cover_file" class="mt-2" />
                        </div>

                        <div class="pt-4 flex gap-4">
                            <Link :href="route('books.index')" class="btn btn-ghost flex-1">Abbrechen</Link>
                            <button type="submit" class="btn btn-primary flex-1" :disabled="form.processing">Speichern</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
