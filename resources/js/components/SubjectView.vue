<template>
    <div id="subject-view">
        <h2>{{ subject.name }}</h2>

        <div class="card mb-3">
            <div class="card-header">Books</div>
            <div class="card-body p-0">
                <data-table
                    url="/api/books/datatable"
                    :filters="{ subject_id: subject.id }"
                    :per-page="dt.perPage"
                    :columns="dt.columns"
                    :key="subject.id">
                </data-table>
            </div>
        </div>

        <a href="#" class="btn btn-info mr-2" @click.prevent="closeView()">Back to list</a>
        <a href="#" class="btn btn-secondary mr-2" @click.prevent="editSubject()">
            <font-awesome-icon :icon="['fas', 'pencil-alt']"></font-awesome-icon> Edit
        </a>
        <button type="button" class="btn btn-warning" @click="mergeSelector = !mergeSelector">
            <font-awesome-icon :icon="['fas', 'code-branch']"></font-awesome-icon> Merge into…
        </button>

        <!-- Merge panel -->
        <div v-if="mergeSelector" class="card mt-3">
            <div class="card-header">Merge <strong>{{ subject.name }}</strong> into another subject</div>
            <div class="card-body">
                <p class="text-muted mb-2">All books will move to the selected subject. This subject will be deleted and recorded as an alias so future imports resolve correctly.</p>

                <div v-if="!mergeTarget">
                    <div class="form-group">
                        <label>Select target subject</label>
                        <select class="form-control" @change="selectMergeTarget($event.target.value)">
                            <option value="">— choose —</option>
                            <option v-for="s in otherSubjects" :key="s.id" :value="s.id">{{ s.name }}</option>
                        </select>
                    </div>
                </div>

                <div v-else>
                    <p>Merge <strong>{{ subject.name }}</strong> into <strong>{{ mergeTarget.name }}</strong>?</p>
                    <button type="button" class="btn btn-danger mr-2" @click="confirmMerge" :disabled="merging">
                        <font-awesome-icon :icon="['fas', 'check']"></font-awesome-icon> {{ merging ? 'Merging…' : 'Confirm Merge' }}
                    </button>
                    <button type="button" class="btn btn-secondary" @click="mergeTarget = null">Cancel</button>
                </div>
            </div>
        </div>

        <!-- Aliases recorded for this subject -->
        <div v-if="subject.duplicates && subject.duplicates.length > 0" class="card mt-3">
            <div class="card-header">Aliases (mapped to this subject on import)</div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <thead><tr><th>Alias name</th><th></th></tr></thead>
                    <tbody>
                        <tr v-for="dup in subject.duplicates" :key="dup.id">
                            <td>{{ dup.name }}</td>
                            <td>
                                <button type="button" class="btn btn-danger btn-sm" @click="removeAlias(dup.id)">
                                    <font-awesome-icon :icon="['fas', 'trash']"></font-awesome-icon>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>
<script>
    import DatatableActionButtons from './DatatableActionButtons.vue';

    export default {
        data() {
            return {
                mergeSelector: false,
                mergeTarget: null,
                merging: false,
                allSubjects: [],
                dt: {
                    perPage: ['10', '25', '50'],
                    columns: [
                        { label: '', name: 'id', filterable: true },
                        { label: 'Title', name: 'title', filterable: true },
                        { label: 'Author', name: 'author', filterable: false },
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
            otherSubjects() {
                var self = this;
                return this.allSubjects.filter(function (s) { return s.id !== self.subject.id; });
            }
        },
        watch: {
            'subject.id': function () {
                this.mergeSelector = false;
                this.mergeTarget = null;
            }
        },
        mounted() {
            var self = this;
            axios.get('/api/subjects').then(function (response) {
                self.allSubjects = response.data;
            });
        },
        methods: {
            closeView() {
                this.$root.$refs.app.clearAlert();
                this.$parent.mode = 'index';
            },
            editSubject() {
                this.$parent.mode = 'edit';
            },
            viewBook(data) {
                var self = this;
                this.$root.$refs.app.mode = 'book';
                this.$nextTick(function () {
                    self.$root.$refs.app.$refs.book.displayRow(data);
                });
            },
            selectMergeTarget(id) {
                var self = this;
                id = parseInt(id, 10);
                if (!id) return;
                this.mergeTarget = this.allSubjects.find(function (s) { return s.id === id; }) || null;
            },
            confirmMerge() {
                var self = this;
                var root = this.$root.$refs.app;
                this.merging = true;
                root.setAlert('Merging subjects…', 'loading');

                axios.post('/api/subject-duplicates', {
                    name: this.subject.name,
                    subject_id: this.mergeTarget.id
                }).then(function () {
                    root.setAlert('Merged successfully', 'success');
                    self.$parent.mode = 'index';
                }).catch(function (error) {
                    root.setAlert('Failed to merge subjects', 'danger');
                    console.log(error);
                }).finally(function () {
                    self.merging = false;
                    self.mergeSelector = false;
                    self.mergeTarget = null;
                });
            },
            removeAlias(dupId) {
                var self = this;
                var root = this.$root.$refs.app;
                axios.delete('/api/subject-duplicates/' + dupId).then(function () {
                    self.subject.duplicates = self.subject.duplicates.filter(function (d) { return d.id !== dupId; });
                    root.setAlert('Alias removed', 'success');
                }).catch(function () {
                    root.setAlert('Failed to remove alias', 'danger');
                });
            }
        },
        props: ['subject']
    }
</script>
