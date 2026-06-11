<template>
    <div id="book-view">
        <img v-if="coverUrl && !coverFailed" :src="coverUrl" @error="coverFailed = true" :alt="'Cover of ' + book.title" class="book-cover">

        <h2>{{ book.title }} </h2>

        <h3>{{ author.name }}</h3>

        {{ book.description }}

        <p>

        <div v-if="book.publisher || book.publish_year || book.page_count || book.language" class="book-meta mb-2">
            <span v-if="book.publisher">{{ book.publisher }}</span><span v-if="book.publisher && book.publish_year">, </span><span v-if="book.publish_year">{{ book.publish_year }}</span>
            <span v-if="book.page_count" class="ml-3">{{ book.page_count }} pages</span>
            <span v-if="book.language" class="ml-3 text-muted">{{ book.language }}</span>
        </div>

        <div v-if="book.subjects && book.subjects.length > 0" class="mb-3">
            <span v-for="subject in book.subjects" :key="subject.id" class="badge badge-secondary mr-1">{{ subject.name }}</span>
        </div>

        <p>

        <div class="card">
            <div class="card-header">
                Classification
            </div>
            <div class="card-body">
                <div v-if="book.dewey_classification">Dewey Decimal Classification: {{ book.dewey_classification }}</div>
                <div v-if="book.lc_classification">USA Library of Congress Classification: {{ book.lc_classification }}</div>

                <div v-if="book.isbn_10">ISBN 10: {{ book.isbn_10 }}</div>
                <div v-if="book.isbn_13">ISBN 13: {{ book.isbn_13 }}</div>
            </div>
        </div>

        <p>

        <div class="card">
            <div class="card-header">
                External Links
            </div>
            <div class="card-body">
                <div v-if="book.openlibrary"><a :href="'https://openlibrary.org/books/'+book.openlibrary" target="_blank">Open Library</a></div>
                <div v-if="book.lccn"><a :href="'https://lccn.loc.gov/'+book.lccn" target="_blank">USA Library of Congress</a></div>
                <div v-if="book.amazon"><a :href="'https://www.amazon.co.uk/gp/product/'+book.amazon" target="_blank">Amazon</a></div>
                <div v-if="book.oclc"><a :href="'https://www.worldcat.org/oclc/'+book.oclc+'?tab=details'" target="_blank">OCLC/WorldCat</a></div>
                <div v-if="book.google"><a :href="'https://books.google.co.uk/books?id='+book.google" target="_blank">Google Books</a></div>
                <div v-if="book.librarything"><a :href="'https://www.librarything.com/work/'+book.librarything" target="_blank">Library Thing</a></div>
                <div v-if="book.project_gutenberg"><a :href="'https://www.gutenberg.org/ebooks/'+book.project_gutenberg" target="_blank">Project Gutenberg</a></div>
                <div v-if="book.goodreads"><a :href="'https://www.goodreads.com/book/show/'+book.goodreads" target="_blank">Good Reads</a></div>
            </div>
        </div>

        <br>

        <a href="#" v-on:click="closeView()" class="btn btn-info">Back to list</a>
        <button v-if="book.isbn" type="button" class="btn btn-warning" v-on:click="refreshFromIsbn()" :disabled="refreshing">
            <font-awesome-icon :icon="['fas', 'sync']"></font-awesome-icon> {{ refreshing ? 'Refreshing…' : 'Refresh from ISBN' }}
        </button>
        <a :href="'/books/' + book.id + '/label'" target="_blank" class="btn btn-secondary"><font-awesome-icon :icon="['fas', 'print']"></font-awesome-icon> Print Label</a>
    </div>
</template>

<script>
	import { lookupIsbn } from '../isbnLookup.js';

	export default {
		data() {
			return {
                author: {},
                coverFailed: false,
                refreshing: false,
            }
        },
        computed: {
            coverUrl() {
                var isbn = this.book.isbn_13 || this.book.isbn || this.book.isbn_10;
                if (!isbn) return null;
                var cleaned = String(isbn).replace(/[^0-9Xx]/g, '');
                if (!cleaned) return null;
                return 'https://covers.openlibrary.org/b/isbn/' + cleaned + '-M.jpg?default=false';
            }
        },
        watch: {
            'book.id': function () {
                this.coverFailed = false;
            }
        },
        mounted() {
			var self = this;

			window.scrollTo(0,0);

			if (this.book.author_id) {
				axios.get('/api/authors/' + this.book.author_id).then(function (response) {
					self.author = response.data;
				})
				.catch(function (error) {
					console.log(error);
				});
			}
        },
        methods: {
            closeView() {
				this.$root.$refs.app.clearAlert();
				this.$parent.mode = 'index';
			},
            refreshFromIsbn() {
                var self = this;
                var root = this.$root.$refs.app;
                var isbn = this.book.isbn;

                if (!isbn) return;

                this.refreshing = true;
                root.setAlert('Looking up ISBN…', 'loading');

                lookupIsbn(function (bookObj) {
                    if (!bookObj) {
                        root.setAlert("Can't find book from ISBN", 'warning');
                        self.refreshing = false;
                        return;
                    }

                    var updates = {};

                    if (bookObj.title && !self.book.title) updates.title = bookObj.title;
                    if (bookObj.description && !self.book.description) updates.description = bookObj.description;

                    if (bookObj.publishers && bookObj.publishers.length > 0 && !self.book.publisher) {
                        var pub = bookObj.publishers[0];
                        updates.publisher = typeof pub === 'object' ? pub.name : pub;
                    }

                    if (!self.book.publish_year) {
                        if (bookObj.publish_year) {
                            updates.publish_year = bookObj.publish_year;
                        } else if (bookObj.publish_date) {
                            var ym = String(bookObj.publish_date).match(/(\d{4})/);
                            if (ym) updates.publish_year = parseInt(ym[1], 10);
                        }
                    }
                    if (!self.book.page_count) {
                        if (bookObj.page_count) updates.page_count = bookObj.page_count;
                        else if (bookObj.number_of_pages) updates.page_count = bookObj.number_of_pages;
                    }
                    if (!self.book.language && bookObj.language) updates.language = bookObj.language;

                    if (bookObj.classifications) {
                        if (!self.book.dewey_classification && bookObj.classifications.dewey_decimal_class) {
                            updates.dewey_classification = bookObj.classifications.dewey_decimal_class.join();
                        }
                        if (!self.book.lc_classification && bookObj.classifications.lc_classifications) {
                            updates.lc_classification = bookObj.classifications.lc_classifications.join();
                        }
                    }

                    if (bookObj.identifiers) {
                        $.each(bookObj.identifiers, function (k, v) {
                            if (!self.book[k]) updates[k] = v.join();
                        });
                    }

                    var newSubjects = (bookObj.subjects_list || []).filter(function (s) {
                        var existing = (self.book.subjects || []).map(function (x) { return x.name.toLowerCase(); });
                        return existing.indexOf(s.toLowerCase()) === -1;
                    });
                    if (newSubjects.length > 0) {
                        var merged = (self.book.subjects || []).map(function (s) { return s.name; });
                        merged = merged.concat(newSubjects);
                        updates.subjects = merged;
                    } else {
                        updates.subjects = (self.book.subjects || []).map(function (s) { return s.name; });
                    }

                    axios.put('/api/books/' + self.book.id, updates).then(function (response) {
                        self.$parent.book = response.data;
                        root.setAlert('Book refreshed from ISBN', 'success');
                    }).catch(function (error) {
                        root.setAlert('Failed to save refreshed data', 'danger');
                        console.log(error);
                    }).finally(function () {
                        self.refreshing = false;
                    });

                }, isbn);
            }
        },
        props: ['book']
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
