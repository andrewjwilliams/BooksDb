<template>
    <div id="author-view">

        <!-- Book detail panel -->
        <div v-if="selectedBook">
            <button type="button" class="btn btn-info mb-3" @click="selectedBook = null">
                <font-awesome-icon :icon="['fas', 'arrow-left']"></font-awesome-icon> Back to Author
            </button>

            <img v-if="selectedBookCoverUrl && !coverFailed" :src="selectedBookCoverUrl" @error="coverFailed = true" :alt="'Cover of ' + selectedBook.title" class="book-cover">

            <h2>{{ selectedBook.title }}</h2>

            <div v-if="selectedBook.publisher || selectedBook.publish_year || selectedBook.page_count || selectedBook.language" class="book-meta mb-2">
                <span v-if="selectedBook.publisher">{{ selectedBook.publisher }}</span><span v-if="selectedBook.publisher && selectedBook.publish_year">, </span><span v-if="selectedBook.publish_year">{{ selectedBook.publish_year }}</span>
                <span v-if="selectedBook.page_count" class="ml-3">{{ selectedBook.page_count }} pages</span>
                <span v-if="selectedBook.language" class="ml-3 text-muted">{{ selectedBook.language }}</span>
            </div>

            <p v-if="selectedBook.description">{{ selectedBook.description }}</p>

            <div v-if="selectedBook.subjects && selectedBook.subjects.length > 0" class="mb-3">
                <span v-for="subject in selectedBook.subjects" :key="subject.id" class="badge badge-secondary mr-1">{{ subject.name }}</span>
            </div>

            <p>

            <div class="card mb-3">
                <div class="card-header">Classification</div>
                <div class="card-body">
                    <div v-if="selectedBook.dewey_classification">Dewey: {{ selectedBook.dewey_classification }}</div>
                    <div v-if="selectedBook.lc_classification">LC: {{ selectedBook.lc_classification }}</div>
                    <div v-if="selectedBook.isbn_10">ISBN 10: {{ selectedBook.isbn_10 }}</div>
                    <div v-if="selectedBook.isbn_13">ISBN 13: {{ selectedBook.isbn_13 }}</div>
                </div>
            </div>

            <div v-if="selectedBook.openlibrary || selectedBook.lccn || selectedBook.amazon || selectedBook.oclc || selectedBook.google || selectedBook.librarything || selectedBook.project_gutenberg || selectedBook.goodreads" class="card mb-3">
                <div class="card-header">External Links</div>
                <div class="card-body">
                    <div v-if="selectedBook.openlibrary"><a :href="'https://openlibrary.org/books/'+selectedBook.openlibrary" target="_blank">Open Library</a></div>
                    <div v-if="selectedBook.lccn"><a :href="'https://lccn.loc.gov/'+selectedBook.lccn" target="_blank">USA Library of Congress</a></div>
                    <div v-if="selectedBook.amazon"><a :href="'https://www.amazon.co.uk/gp/product/'+selectedBook.amazon" target="_blank">Amazon</a></div>
                    <div v-if="selectedBook.oclc"><a :href="'https://www.worldcat.org/oclc/'+selectedBook.oclc+'?tab=details'" target="_blank">OCLC/WorldCat</a></div>
                    <div v-if="selectedBook.google"><a :href="'https://books.google.co.uk/books?id='+selectedBook.google" target="_blank">Google Books</a></div>
                    <div v-if="selectedBook.librarything"><a :href="'https://www.librarything.com/work/'+selectedBook.librarything" target="_blank">Library Thing</a></div>
                    <div v-if="selectedBook.project_gutenberg"><a :href="'https://www.gutenberg.org/ebooks/'+selectedBook.project_gutenberg" target="_blank">Project Gutenberg</a></div>
                    <div v-if="selectedBook.goodreads"><a :href="'https://www.goodreads.com/book/show/'+selectedBook.goodreads" target="_blank">Good Reads</a></div>
                </div>
            </div>

            <div v-if="selectedBook.ebooks && selectedBook.ebooks.length > 0" class="card mb-3">
                <div class="card-header">eBooks</div>
                <div class="card-body">
                    <div v-for="ebook in selectedBook.ebooks" :key="ebook.id">
                        <a :href="ebook.url" target="_blank">{{ ebook.site_name }}</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Author detail panel -->
        <div v-else>
            <h2>{{ author.name }}</h2>
            <h4 v-if="author.fuller_name" class="text-muted">{{ author.fuller_name }}</h4>

            <div v-if="author.birth_date || author.death_date" class="mb-2">
                <span v-if="author.birth_date">b. {{ author.birth_date }}</span>
                <span v-if="author.birth_date && author.death_date"> — </span>
                <span v-if="author.death_date">d. {{ author.death_date }}</span>
            </div>

            <p v-if="author.bio" class="mt-2">{{ author.bio }}</p>

            <p>

            <div v-if="hasRemoteIds || (author.links && author.links.length > 0) || author.open_library_ref" class="card mb-3">
                <div class="card-header">External Links</div>
                <div class="card-body">
                    <div v-if="author.open_library_ref">
                        <a :href="'https://openlibrary.org/authors/' + author.open_library_ref" target="_blank">Open Library</a>
                    </div>
                    <template v-if="author.remote_ids">
                        <div v-for="(id, key) in author.remote_ids" :key="key">
                            <a v-if="remoteIdUrl(key, id)" :href="remoteIdUrl(key, id)" target="_blank">{{ remoteIdLabel(key) }}</a>
                            <span v-else>{{ remoteIdLabel(key) }}: {{ id }}</span>
                        </div>
                    </template>
                    <div v-for="link in author.links" :key="link.id">
                        <a :href="link.url" target="_blank">{{ link.title }}</a>
                    </div>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header">Books</div>
                <div class="card-body p-0">
                    <data-table
                        url="/api/books/datatable"
                        :filters="{ author_id: author.id }"
                        :per-page="dt.perPage"
                        :columns="dt.columns"
                        :key="author.id">
                    </data-table>
                </div>
            </div>

            <a href="#" v-on:click="closeView()" class="btn btn-info">Back to list</a>
            <button v-if="author.open_library_ref" type="button" class="btn btn-warning ml-2" v-on:click="fetchFromOl()" :disabled="fetching">
                <font-awesome-icon :icon="['fas', 'sync']"></font-awesome-icon> {{ fetching ? 'Fetching…' : 'Fetch from Open Library' }}
            </button>
        </div>

    </div>
</template>

<script>
    import { lookupOlAuthor, REMOTE_ID_SERVICES } from '../authorLookup.js';
    import DatatableActionButtons from './DatatableActionButtons.vue';

    export default {
        data() {
            return {
                fetching: false,
                selectedBook: null,
                coverFailed: false,
                dt: {
                    perPage: ['10', '25', '50'],
                    columns: [
                        { label: '', name: 'id', filterable: true },
                        { label: 'Title', name: 'title', filterable: true },
                        {
                            label: '',
                            name: 'actions',
                            component: DatatableActionButtons,
                            width: 20,
                            meta: {
                                buttons: [
                                    {
                                        id: 'btnView',
                                        name: '',
                                        classes: { 'btn': true, 'btn-info': true, 'btn-sm': true },
                                        event: 'click',
                                        handler: this.viewBook,
                                        meta: { icon: ['fas', 'search'], title: 'View' },
                                    }
                                ]
                            }
                        }
                    ]
                }
            };
        },
        computed: {
            hasRemoteIds() {
                return this.author.remote_ids && Object.keys(this.author.remote_ids).length > 0;
            },
            selectedBookCoverUrl() {
                if (!this.selectedBook) return null;
                var isbn = this.selectedBook.isbn_13 || this.selectedBook.isbn || this.selectedBook.isbn_10;
                if (!isbn) return null;
                var cleaned = String(isbn).replace(/[^0-9Xx]/g, '');
                if (!cleaned) return null;
                return 'https://covers.openlibrary.org/b/isbn/' + cleaned + '-M.jpg?default=false';
            }
        },
        watch: {
            'author.id': function () {
                this.selectedBook = null;
            }
        },
        methods: {
            async viewBook(data) {
                this.$root.$refs.app.setAlert('Getting book', 'loading');
                this.coverFailed = false;
                var response = await axios.get('/api/books/' + data.id);
                this.selectedBook = response.data;
                this.$root.$refs.app.clearAlert();
                window.scrollTo(0, 0);
            },
            closeView() {
                this.$root.$refs.app.clearAlert();
                this.$parent.mode = 'index';
            },
            remoteIdUrl(key, id) {
                var svc = REMOTE_ID_SERVICES[key];
                return svc ? svc[0] + id : null;
            },
            remoteIdLabel(key) {
                var svc = REMOTE_ID_SERVICES[key];
                return svc ? svc[1] : key;
            },
            fetchFromOl() {
                var self = this;
                var root = this.$root.$refs.app;

                this.fetching = true;
                root.setAlert('Fetching from Open Library…', 'loading');

                lookupOlAuthor(this.author.open_library_ref, function (data) {
                    if (!data) {
                        root.setAlert('Could not fetch from Open Library', 'warning');
                        self.fetching = false;
                        return;
                    }

                    var updates = {};
                    if (data.fuller_name  && !self.author.fuller_name)  updates.fuller_name  = data.fuller_name;
                    if (data.birth_date   && !self.author.birth_date)   updates.birth_date   = data.birth_date;
                    if (data.death_date   && !self.author.death_date)   updates.death_date   = data.death_date;
                    if (data.bio          && !self.author.bio)          updates.bio          = data.bio;
                    if (data.remote_ids)  updates.remote_ids = data.remote_ids;
                    if (data.links && data.links.length > 0 && (!self.author.links || self.author.links.length === 0)) {
                        updates.links = data.links;
                    }

                    axios.put('/api/authors/' + self.author.id, updates).then(function (response) {
                        self.$parent.author = response.data;
                        root.setAlert('Author updated from Open Library', 'success');
                    }).catch(function () {
                        root.setAlert('Failed to save author data', 'danger');
                    }).finally(function () {
                        self.fetching = false;
                    });
                });
            }
        },
        props: ['author']
    }
</script>

<style scoped>
.book-cover {
    float: right;
    max-width: 200px;
    max-height: 300px;
    margin: 0 0 1rem 1rem;
    border: 1px solid #ddd;
    box-shadow: 0 2px 4px rgba(0,0,0,0.15);
}
</style>
