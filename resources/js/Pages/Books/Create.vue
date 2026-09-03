<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref, onBeforeUnmount } from 'vue';
import axios from 'axios';
import { Html5Qrcode } from "html5-qrcode";
import InputError from '@/Components/InputError.vue';
import { QrCodeIcon } from '@heroicons/vue/24/outline';

const form = useForm({
    title: '',
    author: '',
    isbn: '',
    pages: '',
    format: 'Hardcover',
    published_year: '',
    genre: '',
    status: 'Ungelesen',
    started_at: '',
    finished_at: '',
    notes: '',
    cover_image: '',
    cover_file: null,
});

const searching = ref(false);
const searchResults = ref([]);
const isScannerActive = ref(false);
let html5QrCode = null;

const startScanner = async () => {
    console.log("Scanner V5 starting...");
    isScannerActive.value = true;
    html5QrCode = new Html5Qrcode("reader");
    try {
        const config = {
            fps: 20,
            qrbox: (viewfinderWidth, viewfinderHeight) => {
                const width = Math.min(viewfinderWidth * 0.8, 300);
                const height = 120;
                return { width, height };
            },
            aspectRatio: 1.777778,
            experimentalFeatures: {
                useBarCodeDetectorIfSupported: true
            }
        };

        // Minimal setup to avoid "3 keys" error
        await html5QrCode.start(
            { facingMode: "environment" },
            config,
            (decodedText) => {
                // Validate if it looks like an ISBN (10 or 13 digits)
                const cleanCode = decodedText.replace(/[-\s]/g, "");
                if (cleanCode.length >= 10 && /^\d+$/.test(cleanCode)) {
                    if (window.navigator.vibrate) {
                        window.navigator.vibrate(100);
                    }
                    form.isbn = cleanCode;
                    stopScanner();
                    searchByIsbn();
                }
            },
            (errorMessage) => {
                // Ignore errors
            }
        );
    } catch (err) {
        console.error("Scanner failed", err);
        alert("Fehler: " + err);
        isScannerActive.value = false;
    }
};

const stopScanner = async () => {
    if (html5QrCode) {
        await html5QrCode.stop();
        html5QrCode = null;
    }
    isScannerActive.value = false;
};

onBeforeUnmount(() => {
    if (html5QrCode) {
        html5QrCode.stop();
    }
});

const searchByIsbn = async () => {
    if (!form.isbn) return;
    searching.value = true;
    try {
        const response = await axios.get(route('books.searchByIsbn'), {
            params: { isbn: form.isbn }
        });
        const data = response.data[`ISBN:${form.isbn}`];
        if (data) {
            form.title = data.title;
            form.author = data.authors ? data.authors.map(a => a.name).join(', ') : '';
            form.pages = data.number_of_pages || '';
            form.published_year = data.publish_date ? String(data.publish_date) : '';
            form.cover_image = data.cover ? data.cover.large : '';
        }
    } catch (error) {
        console.error('Search failed', error);
    } finally {
        searching.value = false;
    }
};

const searchByTitle = async () => {
    if (!form.title) return;
    searching.value = true;
    try {
        const response = await axios.get(route('books.search'), {
            params: { title: form.title }
        });
        searchResults.value = response.data.docs;
    } catch (error) {
        console.error('Search failed', error);
    } finally {
        searching.value = false;
    }
};

const selectBook = (book) => {
    form.title = book.title;
    form.author = book.author_name ? book.author_name.join(', ') : '';
    form.isbn = book.isbn ? book.isbn[0] : '';
    form.pages = book.number_of_pages_median || '';
    form.published_year = book.first_publish_year ? String(book.first_publish_year) : '';
    form.cover_image = book.cover_i ? `https://covers.openlibrary.org/b/id/${book.cover_i}-L.jpg` : '';
    searchResults.value = [];
};

const submit = () => {
    form.post(route('books.store'), {
        onSuccess: () => {
            console.log('Book saved successfully');
        },
        onError: (errors) => {
            console.error('Failed to save book', errors);
        }
    });
};
</script>

<template>
    <Head title="Buch hinzufügen" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-serif leading-tight text-base-content">
                Neues Buch hinzufügen
            </h2>
        </template>

        <div class="py-4">
            <div class="mx-auto max-w-2xl sm:px-6 lg:px-8">
                <div class="bg-base-100 overflow-hidden shadow-sm sm:rounded-lg p-6">

                    <!-- Search Bar -->
                    <div class="mb-8">
                        <label class="label">Schnellsuche (ISBN oder Titel)</label>
                        <div class="flex gap-2">
                            <input v-model="form.isbn" type="text" placeholder="ISBN..." class="input input-bordered flex-1" @keyup.enter="searchByIsbn" />
                            <button @click="searchByIsbn" class="btn btn-primary" :disabled="searching">ISBN Suche</button>
                            <button v-if="!isScannerActive" @click="startScanner" class="btn btn-accent" title="Scan QR/ISBN">
                                <QrCodeIcon class="w-6 h-6" />
                            </button>
                            <button v-else @click="stopScanner" class="btn btn-error">Stop</button>
                        </div>

                        <!-- Scanner Container -->
                        <div v-show="isScannerActive" id="reader" class="mt-4 border-2 border-accent rounded overflow-hidden"></div>

                        <div class="flex gap-2 mt-2">
                            <input v-model="form.title" type="text" placeholder="Titel suchen..." class="input input-bordered flex-1" @keyup.enter="searchByTitle" />
                            <button @click="searchByTitle" class="btn btn-secondary" :disabled="searching">Titel Suche</button>
                        </div>
                    </div>

                    <!-- Search Results -->
                    <div v-if="searchResults.length > 0" class="mb-8 p-4 bg-base-200 rounded">
                        <h4 class="font-bold mb-2">Suchergebnisse:</h4>
                        <ul class="space-y-2">
                            <li v-for="book in searchResults" :key="book.key" @click="selectBook(book)" class="cursor-pointer hover:bg-base-300 p-2 rounded text-sm">
                                {{ book.title }} ({{ book.author_name ? book.author_name[0] : 'Unbekannt' }})
                            </li>
                        </ul>
                    </div>

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

                        <div class="pt-4">
                            <button type="submit" class="btn btn-primary w-full" :disabled="form.processing">Speichern</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
