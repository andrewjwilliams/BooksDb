<template>
	<div id="author">
		<div class="heading">
		  <h1>Authors</h1>
		</div>
		<div id="author-index" v-if="this.mode == 'index'">
			<data-table
				id="author-index-table"
				url="/api/authors/datatable"
				:per-page="dt.perPage"
				:columns="dt.columns">
			</data-table>  
			<div>
			  <button @click="create" class="btn btn-success"><font-awesome-icon :icon="['fas', 'plus']"></font-awesome-icon> Add</button>
			</div>
		</div><!-- /author-index -->

		<author-form v-if="this.mode == 'create' || this.mode == 'edit'" :author="author"></author-form>
		<author-view v-if="this.mode == 'view'" :author="author"></author-view>
		
	</div><!-- /author -->
</template>
<script>

import DatatableActionButtons from './DatatableActionButtons.vue';

export default {
    data() {
        return {
			componentKey: 0,
			mode: 'index',
			author: {},
			dt: {
				perPage: ['10', '25', '50'],
				columns: [
					{
						label: '',
						name: 'id',
						filterable: true,
					},
					{
						label: 'Name',
						name: 'name',
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
									handler: this.editAuthor,
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
									handler: this.deleteAuthor,
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
		async  displayRow(data) {
			this.$root.$refs.app.setAlert('Getting author', 'loading');

			var response = await axios.get('/api/authors/' + data.id);
			this.author  =  response.data ;

			this.mode = 'view';

			this.$root.$refs.app.clearAlert();
        },
		async editAuthor(data) {
			this.$root.$refs.app.setAlert('Getting author', 'loading');

			var response = await axios.get('/api/authors/' + data.id);
			this.author  =  response.data ;

			this.mode = 'edit';

			this.$root.$refs.app.clearAlert();
        },
		async deleteAuthor(data) {
			if (confirm(`Are you sure you want to delete ${data.name} ? `)) {
				this.$root.$refs.app.setAlert('Delete author', 'loading');

				var response = await axios.delete('/api/authors/' + data.id);

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
			this.author = {
				id: null,
				name: "",
				openlibrary: null,
			};  
		}
    },
    components: {}
  }
</script>