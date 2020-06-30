<template>
    <div id="author-view">
        <h2>{{ author.name }}</h2>

        <br>

        <div class="card">
            <div class="card-header">
                External Links
            </div><!-- /card-header -->
            <div class="card-body">
                <div v-if="author.open_library_ref"><a :href="'https://openlibrary.org/authors/'+author.open_library_ref" target="_blank">Open Library</a></div>
            </div><!-- /card-body -->
        </div><!-- /card -->

        <br>

        <a href="#" v-on:click="closeView()" class="btn btn-info">Back to list</a>
    </div>
</template>

<script>
	export default {
		data() {
			return {}
        },
        methods: {
            closeView() {
				this.$root.$refs.app.clearAlert();
				this.$parent.mode = 'index';
			}
        },
        props: ['author']
    }
</script>
