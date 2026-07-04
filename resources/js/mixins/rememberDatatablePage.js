// Remembers the current page of a <data-table ref="dataTable" :url="dtUrl">.
//
// The table auto-fetches page 1 as soon as its `url` prop is truthy (in its
// own created() hook), so if we set both the saved page and the url at the
// same time we get two concurrent requests; the library's response guard
// (a "draw" counter that isn't incremented until a response lands) then
// keeps whichever reply arrives first and drops the other, which in
// practice was always the page-1 auto-fetch. To avoid the race, the host
// component's `dtUrl` data property must start empty ('') so the table
// mounts without fetching, and we only assign the real URL - after seeding
// `table.page` - here in mounted().
export default function rememberDatatablePage(storageKey, baseUrl) {
    return {
        data() {
            return { dtUrl: '' };
        },
        mounted() {
            this.$nextTick(() => {
                const table = this.$refs.dataTable;

                if (!table) {
                    return;
                }

                table.page = parseInt(localStorage.getItem(storageKey), 10) || 1;

                table.$watch('page', (page) => {
                    localStorage.setItem(storageKey, page);
                });

                // Absolute URL: the table's `url` watcher parses it with
                // `new URL()`, which throws on a bare relative path.
                this.dtUrl = window.location.origin + baseUrl;
            });
        }
    };
}
