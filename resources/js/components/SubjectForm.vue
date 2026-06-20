<template>
    <div id="subject-form">
        <h2>{{ subject.id ? 'Edit Subject' : 'New Subject' }}</h2>
        <form @submit.prevent="checkForm">
            <div class="form-group">
                <label>Name</label>
                <input type="text" class="form-control" v-model="form.name" required>
            </div>
            <button type="submit" class="btn btn-primary mr-2">Save</button>
            <a href="#" class="btn btn-secondary" @click.prevent="cancel">Cancel</a>
        </form>
    </div>
</template>
<script>
export default {
    data() {
        return {
            form: { name: this.subject.name || '' }
        };
    },
    watch: {
        subject(val) {
            this.form.name = val.name || '';
        }
    },
    methods: {
        checkForm() {
            var self = this;
            var root = this.$root.$refs.app;
            var parent = this.$parent;

            root.setAlert('Saving…', 'loading');

            var request = parent.mode === 'edit'
                ? axios.put('/api/subjects/' + this.subject.id, this.form)
                : axios.post('/api/subjects', this.form);

            request.then(function (response) {
                root.setAlert('Saved', 'success');
                parent.subject = response.data;
                parent.mode = 'view';
            }).catch(function () {
                root.setAlert('Failed to save subject', 'danger');
            });
        },
        cancel() {
            this.$parent.mode = this.subject.id ? 'view' : 'index';
        }
    },
    props: ['subject']
}
</script>
