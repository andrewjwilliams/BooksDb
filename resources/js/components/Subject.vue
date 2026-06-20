<template>
    <div id="subjects">
        <div class="heading">
            <h1>Subjects</h1>
        </div>
        <div id="subject-index" v-if="mode == 'index'">
            <data-table
                url="/api/subjects/datatable"
                :per-page="dt.perPage"
                :columns="dt.columns"
                order-by="name"
                order-dir="asc">
            </data-table>
            <div>
                <button @click="create" class="btn btn-success"><font-awesome-icon :icon="['fas', 'plus']"></font-awesome-icon> Add</button>
            </div>
        </div>

        <subject-form v-if="mode == 'create' || mode == 'edit'" :subject="subject"></subject-form>
        <subject-view v-if="mode == 'view'" :subject="subject"></subject-view>
    </div>
</template>
<script>

import DatatableActionButtons from './DatatableActionButtons.vue';

export default {
    data() {
        return {
            mode: 'index',
            subject: {},
            dt: {
                perPage: ['10', '25', '50'],
                columns: [
                    { label: '', name: 'id', filterable: true },
                    { label: 'Name', name: 'name', filterable: true, orderable: true },
                    { label: 'Books', name: 'books', filterable: false, orderable: false },
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
                                    classes: { 'btn': true, 'btn-info': true, 'btn-sm': true },
                                    event: 'click',
                                    handler: this.displayRow,
                                    meta: { icon: ['fas', 'search'], title: 'View' },
                                },
                                {
                                    id: 'btnActionsEdit',
                                    name: '',
                                    classes: { 'btn': true, 'btn-info': true, 'btn-sm': true },
                                    event: 'click',
                                    handler: this.editSubject,
                                    meta: { icon: ['fas', 'pencil-alt'], title: 'Edit' },
                                },
                                {
                                    id: 'btnActionsDelete',
                                    name: '',
                                    classes: { 'btn': true, 'btn-danger': true, 'btn-sm': true },
                                    event: 'click',
                                    handler: this.deleteSubject,
                                    meta: { icon: ['fas', 'trash'], title: 'Delete' },
                                },
                            ]
                        }
                    }
                ]
            }
        };
    },
    methods: {
        async displayRow(data) {
            await this.viewSubjectById(data.id);
        },
        async viewSubjectById(id) {
            this.$root.$refs.app.setAlert('Getting subject', 'loading');
            var response = await axios.get('/api/subjects/' + id);
            this.subject = response.data;
            this.mode = 'view';
            this.$root.$refs.app.clearAlert();
        },
        async editSubject(data) {
            this.$root.$refs.app.setAlert('Getting subject', 'loading');
            var response = await axios.get('/api/subjects/' + data.id);
            this.subject = response.data;
            this.mode = 'edit';
            this.$root.$refs.app.clearAlert();
        },
        async deleteSubject(data) {
            if (confirm('Are you sure you want to delete "' + data.name + '"?')) {
                this.$root.$refs.app.setAlert('Deleting subject', 'loading');
                await axios.delete('/api/subjects/' + data.id);
                this.mode = 'xxx';
                this.$nextTick(() => { this.mode = 'index'; });
                this.$root.$refs.app.setAlert('Deleted', 'success');
            }
        },
        create() {
            this.$root.$refs.app.clearAlert();
            this.mode = 'create';
            this.subject = { id: null, name: '' };
        }
    },
    components: {}
}
</script>
