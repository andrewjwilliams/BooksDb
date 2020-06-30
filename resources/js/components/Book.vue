<template>
	<div id="books">
		<div class="heading">
		  <h1>Books</h1>
		</div>
		<div id="books-index" v-if="this.mode == 'index'">
			<data-table
				url="/api/books/datatable"
				:per-page="dt.perPage"
				:columns="dt.columns">
			</data-table>  
			<div>
			  <button @click="create" class="btn btn-success"><font-awesome-icon :icon="['fas', 'plus']"></font-awesome-icon> Add</button>
			</div>
		</div><!-- /books-index -->

		<book-form v-if="this.mode == 'create' || this.mode == 'edit'" :book="book"></book-form>

		<book-view v-if="this.mode == 'view'" :book="book"></book-view>
	</div><!-- /books -->
</template>
<script>

import DatatableActionButtons from './DatatableActionButtons.vue';

export default {
    data() {
        return {
			mode: 'index',
			book: {},
			dt: {
				perPage: ['10', '25', '50'],
				columns: [
					{
						label: '',
						name: 'id',
						filterable: true,
					},
					{
						label: 'Title',
						name: 'title',
						filterable: true,
					},
					{
						label: 'Author',
						name: 'author',
						filterable: true,
					},
					{
						label: '',
						name: 'actions',
						component: DatatableActionButtons,
						width: 20,
						meta: {
							buttons: [
								{
									id: 'btnActionsView',
									name: '',
									classes: { 
										'btn': true,
										'btn-info': true,
										'btn-sm': true,
									},
									event: "click",
									handler: this.displayRow,
									meta: {
										icon: ['fas', 'search'],
										title: "View"
									},
								},
								{
									id: 'btnActionsEdit',
									name: '',
									classes: { 
										'btn': true,
										'btn-info': true,
										'btn-sm': true,
									},
									event: "click",
									handler: this.editBook,
									meta: {
										icon: ['fas', 'pencil-alt'],
										title: "Edit"
									},
								},
								{
									id: 'btnActionsDelete',
									name: '',
									classes: { 
										'btn': true,
										'btn-danger': true,
										'btn-sm': true,
									},
									event: "click",
									handler: this.deleteBook,
									meta: {
										icon: ['fas', 'trash'],
										title: "Edit"
									},
								}
							]
						}
					}
				]
			}
        };

    },
    methods: {
		async displayRow(data) {
            this.$root.$refs.app.setAlert('Getting book', 'loading');

			var response = await axios.get('/api/books/' + data.id);
			this.book  =  response.data ;

			this.mode = 'view';

			this.$root.$refs.app.clearAlert();
        },
		async editBook(data) {
			this.$root.$refs.app.setAlert('Getting book', 'loading');

			var response = await axios.get('/api/books/' + data.id);
			this.book  =  response.data ;

			this.mode = 'edit';

			this.$root.$refs.app.clearAlert();
        },
		async deleteBook(data) {
			if (confirm(`Are you sure you want to delete ${data.name} ? `)) {
				this.$root.$refs.app.setAlert('Delete book', 'loading');

				var response = await axios.delete('/api/books/' + data.id);

				this.mode = 'xxx';
				this.$nextTick(() => {
					this.mode = 'index';
				});

				this.$root.$refs.app.setAlert('Deleted', 'success');
			}
        },
		create() {
			this.$root.$refs.app.clearAlert();
			this.mode = 'create';
			this.book = {
				id: null,
				title: "",
				author_id: null,
				isbn: "",
				dewey_classification: null,
				lc_classification: null,
				publisher: null,
				openlibrary: null,
				google: null,
				lccn: null,
				isbn_13: null,
				amazon: null,
				isbn_10: null,
				oclc: null,
				librarything: null,
				project_gutenberg: null,
				goodreads: null
			};  
		}
    },
    components: {}
  }
</script>