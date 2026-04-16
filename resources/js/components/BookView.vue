<template>
    <div id="book-view">
        <h2>{{ book.title }} </h2>

        <h3>{{ author.name }}</h3>

        {{ book.description }}

        <div v-if="book.publisher">Publisher: {{ book.publisher }}</div>

        <div class="card">
            <div class="card-header">
                Classification
            </div>
            <div class="card-body">
                <div v-if="book.dewey_classification">Dewy Decimal Classification: {{ book.dewey_classification }}</div>
                <div v-if="book.lc_classification">USA Library of Congress Classification: {{ book.lc_classification }}</div>

                <div v-if="book.isbn_10">ISBN 10: {{ book.isbn_10 }}</div>
                <div v-if="book.isbn_13">ISBN 13: {{ book.isbn_13 }}</div>
            </div>
        </div>

        <br>

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
        <a :href="'/books/' + book.id + '/label'" target="_blank" class="btn btn-secondary"><font-awesome-icon :icon="['fas', 'print']"></font-awesome-icon> Print Label</a>
    </div>
</template>

<script>
	export default {
		data() {
			return {
                author: {}
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
			}
        },
        props: ['book']
    }
</script>
