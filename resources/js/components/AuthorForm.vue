<template>
	<form @submit="checkForm" id="author-form" action="">
		<h2 v-if="this.$parent.mode == 'edit'">Edit {{ author.name }} </h2>
		<h2 v-if="this.$parent.mode == 'create'">Create New Author</h2>

        <div v-if="this.$parent.mode == 'edit'">
            <input type="hidden" v-model="author.id" name="id" ref="id">
        </div>

        <div class="col-xs-12 col-sm-12 col-md-12">
            <div class="form-group">
                <label for="name">Name</label>
                <input type="text" v-model="author.name" name="name" id="name" ref="name" v-bind:class="{'form-control':true, 'is-invalid' : !author.name, 'is-valid' : author.name}" placeholder="Name" aria-label="Name">
            </div>
        </div>

        <div class="col-xs-12 col-sm-12 col-md-12">
            <div class="form-group">
                <label for="open_library_ref">Open Library Ref</label>
                <input type="text" v-model="author.open_library_ref" name="open_library_ref" id="open_library_ref" ref="open_library_ref" class="form-control" placeholder="Open Library Ref" aria-label="Open Library Ref">
            </div>
        </div>

        <div class="col-xs-12 col-sm-12 col-md-12 text-center">
            <button v-if="this.$parent.mode == 'edit'" type="submit" class="btn btn-success"><font-awesome-icon :icon="['fas', 'check']"></font-awesome-icon> Save</button>
            <button v-if="this.$parent.mode == 'create'" type="submit" class="btn btn-success"><font-awesome-icon :icon="['fas', 'plus']"></font-awesome-icon> Create</button>

            <button type="button" class="btn btn-danger" v-on:click="closeForm()"><font-awesome-icon :icon="['fas', 'times']"></font-awesome-icon> Cancel</button>
        </div>

    </form>
</template>

<script>
	export default {
		data() {
			return {}
        },
        methods: {
            closeForm() {
				this.$root.$refs.app.clearAlert();
				this.$parent.mode = 'index';
            },
            checkForm(e) {
				e.preventDefault();

				var root = this.$root.$refs.app;
				var parent = this.$parent;
				var formFields = JSON.parse(JSON.stringify(this.author));
				var id = this.author.id;

				this.errors = [];
				if (!formFields.name) {
					root.setAlert('Name required', 'danger');

				} else {
					root.setAlert('Saving', 'loading');

					delete formFields.id;
					delete formFields.created_at;
					delete formFields.updated_at;

					if (this.$parent.mode == 'edit') {
						axios.put('/api/authors/'+id, formFields).then(response => {
							root.setAlert('Saved record', 'success');
							parent.mode = 'index';
						}).catch(error => {
							root.setAlert('Unable to save record', 'danger');
							console.log(error);
						});
					} else {
						axios.post('/api/authors', formFields).then(function (response) {
							root.setAlert('Created new author record', 'success');
							parent.mode = 'index';
						}, function (error) {
							root.setAlert('Unable to save record', 'danger');
							console.log(error);
						});
					}
				}
			},
        },
        props: ['author']
    }
</script>
