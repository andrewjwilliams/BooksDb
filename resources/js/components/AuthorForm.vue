<template>
    <form @submit="checkForm" id="author-form" action="">
        <h2 v-if="this.$parent.mode == 'edit'">Edit {{ author.name }}</h2>
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
                <label for="fuller_name">Fuller Name</label>
                <input type="text" v-model="author.fuller_name" name="fuller_name" id="fuller_name" class="form-control" placeholder="Fuller Name">
            </div>
        </div>

        <div class="row">
            <div class="col-sm-6">
                <div class="form-group">
                    <label for="birth_date">Date of Birth</label>
                    <input type="text" v-model="author.birth_date" name="birth_date" id="birth_date" class="form-control" placeholder="e.g. 31 July 1965">
                </div>
            </div>
            <div class="col-sm-6">
                <div class="form-group">
                    <label for="death_date">Date of Death</label>
                    <input type="text" v-model="author.death_date" name="death_date" id="death_date" class="form-control" placeholder="e.g. 1 January 2000">
                </div>
            </div>
        </div>

        <div class="col-xs-12 col-sm-12 col-md-12">
            <div class="form-group">
                <label for="bio">Biography</label>
                <textarea v-model="author.bio" name="bio" id="bio" class="form-control" rows="4" placeholder="Biography"></textarea>
            </div>
        </div>

        <div class="col-xs-12 col-sm-12 col-md-12">
            <div class="form-group">
                <label for="open_library_ref">Open Library Ref</label>
                <input type="text" v-model="author.open_library_ref" name="open_library_ref" id="open_library_ref" class="form-control" placeholder="e.g. OL23919A">
            </div>
        </div>

        <div class="col-xs-12 col-sm-12 col-md-12">
            <label>Links</label>
            <div v-for="(link, index) in authorLinks" :key="index" class="form-row mb-1">
                <div class="col">
                    <input type="text" v-model="link.title" class="form-control" placeholder="Title (e.g. Official Site)">
                </div>
                <div class="col">
                    <input type="text" v-model="link.url" class="form-control" placeholder="URL">
                </div>
                <div class="col-auto">
                    <button type="button" class="btn btn-danger btn-sm" @click="removeLink(index)">
                        <font-awesome-icon :icon="['fas', 'times']"></font-awesome-icon>
                    </button>
                </div>
            </div>
            <button type="button" class="btn btn-secondary btn-sm mt-1" @click="addLink()">
                <font-awesome-icon :icon="['fas', 'plus']"></font-awesome-icon> Add Link
            </button>
        </div>

        <div class="col-xs-12 col-sm-12 col-md-12 text-center mt-3">
            <button v-if="this.$parent.mode == 'edit'" type="submit" class="btn btn-success"><font-awesome-icon :icon="['fas', 'check']"></font-awesome-icon> Save</button>
            <button v-if="this.$parent.mode == 'create'" type="submit" class="btn btn-success"><font-awesome-icon :icon="['fas', 'plus']"></font-awesome-icon> Create</button>
            <button type="button" class="btn btn-danger ml-2" v-on:click="closeForm()"><font-awesome-icon :icon="['fas', 'times']"></font-awesome-icon> Cancel</button>
        </div>

    </form>
</template>

<script>
    export default {
        data() {
            return {
                authorLinks: [],
            };
        },
        watch: {
            'author.id': {
                immediate: true,
                handler() {
                    this.authorLinks = (this.author.links || []).map(function (l) {
                        return { title: l.title, url: l.url };
                    });
                }
            }
        },
        methods: {
            addLink() {
                this.authorLinks.push({ title: '', url: '' });
            },
            removeLink(index) {
                this.authorLinks.splice(index, 1);
            },
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
                    return;
                }

                root.setAlert('Saving', 'loading');

                delete formFields.id;
                delete formFields.created_at;
                delete formFields.updated_at;
                delete formFields.links;

                formFields.links = this.authorLinks
                    .filter(function (l) { return l.url; })
                    .map(function (l) { return { title: l.title || l.url, url: l.url }; });

                if (this.$parent.mode == 'edit') {
                    axios.put('/api/authors/' + id, formFields).then(response => {
                        root.setAlert('Saved record', 'success');
                        parent.author = response.data;
                        parent.mode = 'view';
                    }).catch(error => {
                        root.setAlert('Unable to save record', 'danger');
                        console.log(error);
                    });
                } else {
                    axios.post('/api/authors', formFields).then(function (response) {
                        root.setAlert('Created new author record', 'success');
                        parent.author = response.data;
                        parent.mode = 'view';
                    }, function (error) {
                        root.setAlert('Unable to save record', 'danger');
                        console.log(error);
                    });
                }
            },
        },
        props: ['author']
    }
</script>
