<template>
	<form @submit="checkForm" id="book-form" action="">
		<h2 v-if="this.$parent.mode == 'edit'">Edit {{ book.title }} </h2>
		<h2 v-if="this.$parent.mode == 'create'">Create New Book</h2>

		<div class="row">
			<div v-if="this.$parent.mode == 'edit'">
				<input type="hidden" v-model="book.id" name="id" ref="id">
			</div>

            <div class="col-xs-12 col-sm-12 col-md-12">
                <div class="form-group">
					<label for="isbn">ISBN</label>

					<div class="input-group mb-3">
						<input type="text" v-model="book.isbn" name="isbn" id="isbn" ref="isbn" v-bind:class="{'form-control':true, 'is-invalid':book.isbn && !validIsbn(book.isbn), 'is-valid' : validIsbn(book.isbn)}" placeholder="ISBN" aria-label="ISBN">
						<div class="input-group-append">
							<button v-if="scannerSupported" id="btn_scan_isbn" v-on:click="openScanner()" class="btn btn-secondary" type="button" title="Scan with camera"><font-awesome-icon :icon="['fas', 'camera']"></font-awesome-icon> Scan</button>
							<button id="btn_isdn" v-on:click="getByIsbn()" class="btn btn-info" type="button" :disabled=!validIsbn(book.isbn)>Lookup</button>
						</div>
					</div>

					<div v-if="scannerOpen" class="isbn-scanner">
						<div class="isbn-scanner__backdrop" v-on:click="closeScanner()"></div>
						<div class="isbn-scanner__panel">
							<h4>Scan ISBN barcode</h4>
							<div id="isbn-scanner-stream" class="isbn-scanner__video"></div>
							<p v-if="scannerError" class="text-danger mb-2">{{ scannerError }}</p>
							<p v-else-if="scannerHint" class="text-muted mb-2">{{ scannerHint }}</p>
							<button type="button" v-on:click="closeScanner()" class="btn btn-danger">Cancel</button>
						</div>
					</div>

                </div>
            </div>

			<div class="col-xs-12 col-sm-12 col-md-12">
                <div class="form-group">
					<label for="title">Title</label>
					<input type="text" v-model="book.title" name="title" id="title" ref="title" v-bind:class="{'form-control':true, 'is-invalid' : !book.title, 'is-valid' : book.title}" placeholder="Title" aria-label="Title">
				</div>
			</div>

			<input type="hidden" v-model="book.author_id" name="author_id" ref="author_id">

			<div class="col-xs-12 col-sm-12 col-md-12">
				<div class="form-group">
					<label for="title">Author</label>
					<div v-if="book.author_id && !authorSelector" class="input-group mb-3">
						<input type="text"  v-model="author.name" id="author" class="form-control" disabled>
						<div class="input-group-append">
							<button id="btn_author" v-on:click="authorSelector = true" class="btn btn-info" type="button">Change</button>
						</div>
					</div>

					<div v-if="!book.author_id || authorSelector" class="col-xs-12 col-sm-12 col-md-12">
						<data-table
							url="/api/authors/datatable"
							:per-page="authorsDt.perPage"
							:columns="authorsDt.columns">
						</data-table>

						<button @click="createAuthor" class="btn btn-success" type="button"><font-awesome-icon :icon="['fas', 'plus']"></font-awesome-icon> Add</button>

						<button v-on:click="authorSelector = false" class="btn btn-danger" type="button" :disabled=!book.author_id><font-awesome-icon :icon="['fas', 'times']"></font-awesome-icon> Close Selector</button>
					</div>
				</div>
			</div>

			<div class="col-xs-12 col-sm-12 col-md-12">
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea v-bind:class="{'form-control':true,'is-valid':book.description}" v-model="book.description" style="height:150px" name="description" placeholder="Description"></textarea>
                </div>
            </div>

			<div class="col-xs-12 col-sm-12 col-md-12">
                <div class="form-group">
					<label for="publisher">Publisher</label>
					<input type="text" v-model="book.publisher" name="publisher" id="publisher" class="form-control" placeholder="Publisher" aria-label="Publisher">
				</div>
			</div>

			<div class="col-xs-12 col-sm-4 col-md-4">
				<div class="form-group">
					<label for="publish_year">Year Published</label>
					<input type="number" v-model="book.publish_year" name="publish_year" id="publish_year" class="form-control" placeholder="Year" min="1000" max="2100">
				</div>
			</div>

			<div class="col-xs-12 col-sm-4 col-md-4">
				<div class="form-group">
					<label for="page_count">Pages</label>
					<input type="number" v-model="book.page_count" name="page_count" id="page_count" class="form-control" placeholder="Page count" min="1">
				</div>
			</div>

			<div class="col-xs-12 col-sm-4 col-md-4">
				<div class="form-group">
					<label for="language">Language</label>
					<input type="text" v-model="book.language" name="language" id="language" class="form-control" placeholder="e.g. en" maxlength="10">
				</div>
			</div>

			<div class="col-xs-12 col-sm-12 col-md-12">
				<div class="form-group">
					<label>Subjects</label>
					<div class="subject-tags mb-2">
						<span v-for="(subject, idx) in bookSubjects" :key="idx" class="badge badge-secondary subject-tag">
							{{ subject.name }}
							<button type="button" class="subject-tag__remove" @click="removeSubject(idx)" aria-label="Remove">&times;</button>
						</span>
						<span v-if="bookSubjects.length === 0" class="text-muted">No subjects</span>
					</div>
					<div class="input-group">
						<input type="text" v-model="newSubject" class="form-control" placeholder="Add subject…" @keydown.enter.prevent="addSubject">
						<div class="input-group-append">
							<button type="button" class="btn btn-outline-secondary" @click="addSubject">Add</button>
						</div>
					</div>
				</div>
			</div>

			<div class="col-xs-12 col-sm-12 col-md-12">
                <div class="form-group">
					<fieldset>
						<legend>Classification</legend>
						<div class="row">
							<div class="col-xs-12 col-sm-12 col-md-6">
								<div class="form-group">
									<label for="dewey_classification">Dewey Classification</label>
									<input type="text" v-model="book.dewey_classification" name="dewey_classification" id="dewey_classification" class="form-control" placeholder="Dewey Classification" aria-label="Dewey Classification">
								</div>
							</div>

							<div class="col-xs-12 col-sm-12 col-md-6">
								<div class="form-group">
									<label for="lc_classification">US Library of Congress Classification</label>
									<input type="text" v-model="book.lc_classification" name="lc_classification" id="lc_classification" class="form-control" placeholder="LC Classification" aria-label="LC Classification">
								</div>
							</div>
						</div>
					</fieldset>
				</div>
			</div>

			<div class="col-xs-12 col-sm-12 col-md-12">
                <div class="form-group">
					<fieldset>
						<legend>Index</legend>

						<div class="row">
							<div class="col-xs-12 col-sm-12 col-md-6">
								<div class="form-group">
									<label for='isbn_10'>ISBN 10</label>
									<input type="text" v-model="book.isbn_10" id="isbn_10" name="isbn_10" class="form-control" placeholder="ISBN 10" aria-label="ISBN 10">
								</div>
							</div>

							<div class="col-xs-12 col-sm-12 col-md-6">
								<div class="form-group">
									<label for='isbn_13'>ISBN 13</label>
									<input type="text" v-model="book.isbn_13" id="isbn_13" name="isbn_13" class="form-control" placeholder="ISBN 13" aria-label="ISBN 13">
								</div>
							</div>

							<div class="col-xs-12 col-sm-12 col-md-6">
								<div class="form-group">
									<label for="openlibrary">Open Library</label>
									<input type="text" v-model="book.openlibrary" name="openlibrary" id="openlibrary" class="form-control" placeholder="Open Library" aria-label="Open Library">
								</div>
							</div>

							<div class="col-xs-12 col-sm-12 col-md-6">
								<div class="form-group">
									<label for="google">Google Books</label>
									<input type="text" v-model="book.google" name="google" id="google" class="form-control" placeholder="Google Books" aria-label="Google Books">
								</div>
							</div>

							<div class="col-xs-12 col-sm-12 col-md-6">
								<div class="form-group">
									<label for='lccn'>US Library of Congress Catalogue Number</label>
									<input type="text" v-model="book.lccn" id="lccn" name="lccn" class="form-control" placeholder="LCCN" aria-label="LCCN">
								</div>
							</div>

							<div class="col-xs-12 col-sm-12 col-md-6">
								<div class="form-group">
									<label for='amazon'>Amazon</label>
									<input type="text" v-model="book.amazon" id="amazon" name="amazon" class="form-control" placeholder="Amazon" aria-label="Amazon">
								</div>
							</div>

							<div class="col-xs-12 col-sm-12 col-md-6">
								<div class="form-group">
									<label for='oclc'>Online Computer Library Centre</label>
									<input type="text" v-model="book.oclc" id="oclc" name="oclc" class="form-control" placeholder="OCLC" aria-label="OCLC">
								</div>
							</div>

							<div class="col-xs-12 col-sm-12 col-md-6">
								<div class="form-group">
									<label for='librarything'>Library Thing</label>
									<input type="text" v-model="book.librarything" id="librarything" name="librarything" class="form-control" placeholder="Library Thing" aria-label="Library Thing">
								</div>
							</div>

							<div class="col-xs-12 col-sm-12 col-md-6">
								<div class="form-group">
									<label for='project_gutenberg'>Project Gutenberg</label>
									<input type="text" v-model="book.project_gutenberg" id="project_gutenberg" name="project_gutenberg" class="form-control" placeholder="Project Gutenberg" aria-label="Project Gutenberg">
								</div>
							</div>

							<div class="col-xs-12 col-sm-12 col-md-6">
								<div class="form-group">
									<label for='goodreads'>Goodreads</label>
									<input type="text" v-model="book.goodreads" id="goodreads" name="goodreads" class="form-control" placeholder="Goodreads" aria-label="Goodreads">
								</div>
							</div>
						</div>
					</fieldset>
				</div>
			</div>

            <div class="col-xs-12 col-sm-12 col-md-12 text-center">
                <button v-if="this.$parent.mode == 'edit'" type="submit" class="btn btn-success"><font-awesome-icon :icon="['fas', 'check']"></font-awesome-icon> Save</button>
				<button v-if="this.$parent.mode == 'create'" type="submit" class="btn btn-success"><font-awesome-icon :icon="['fas', 'plus']"></font-awesome-icon> Create</button>

				<button type="button" class="btn btn-danger" v-on:click="closeForm()"><font-awesome-icon :icon="['fas', 'times']"></font-awesome-icon> Cancel</button>
            </div>
        </div>

    </form>
</template>

<style scoped>
.isbn-scanner {
	position: fixed;
	inset: 0;
	z-index: 1050;
	display: flex;
	align-items: center;
	justify-content: center;
}
.isbn-scanner__backdrop {
	position: absolute;
	inset: 0;
	background: rgba(0, 0, 0, 0.6);
}
.isbn-scanner__panel {
	position: relative;
	background: #fff;
	padding: 1rem;
	border-radius: 0.5rem;
	max-width: 95vw;
	width: 480px;
	box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
}
.isbn-scanner__video {
	width: 100%;
	max-height: 60vh;
	background: #000;
	border-radius: 0.25rem;
	margin-bottom: 0.5rem;
}
.subject-tags {
	display: flex;
	flex-wrap: wrap;
	gap: 0.4rem;
	min-height: 2rem;
}
.subject-tag {
	display: inline-flex;
	align-items: center;
	gap: 0.3rem;
	font-size: 0.85rem;
	padding: 0.3rem 0.5rem;
}
.subject-tag__remove {
	background: none;
	border: none;
	padding: 0;
	line-height: 1;
	cursor: pointer;
	color: inherit;
	opacity: 0.7;
	font-size: 1rem;
}
.subject-tag__remove:hover {
	opacity: 1;
}
</style>

<script>
	import DatatableActionButton from './DatatableActionButton.vue';
	import { lookupIsbn, isValidIsbn } from '../isbnLookup.js';

	export default {
		data() {
			return {
				author: {},
				authorSelector : false,
				scannerSupported: false,
				scannerOpen: false,
				scannerError: null,
				scannerHint: 'Point your camera at the barcode on the back of the book.',
				scannerControls: null,
				newSubject: '',
				authorsDt: {
					perPage: ['10', '25', '50'],
					columns: [
						{
							label: '',
							name: 'id',
							filterable: false,
						},
						{
							label: 'Author',
							name: 'name',
							filterable: true,
						},
						{
							label: '',
							name: 'Select',
							component: DatatableActionButton,
							classes: {
								'btn': true,
								'btn-info': true,
								'btn-sm': true,
							},
							event: "click",
							handler: this.selectAuthor,
							meta: {
								title: "Select"
							}
						}
					]
				}
			};
        },
		mounted() {
			var self = this;

			window.scrollTo(0,0);

			this.scannerSupported = !!(navigator.mediaDevices && typeof navigator.mediaDevices.getUserMedia === 'function');

			if (this.book.author_id) {
				axios.get('/api/authors/' + this.book.author_id).then(function (response) {
					self.author = response.data;
				})
				.catch(function (error) {
					console.log(error);
				});
			}
		},
		beforeDestroy() {
			this.closeScanner();
		},

		computed: {
			bookSubjects() {
				var subjects = this.book.subjects;
				if (!subjects || subjects.length === 0) return [];
				return subjects.map(function (s) {
					return typeof s === 'string' ? { name: s } : s;
				});
			}
		},

		methods: {
			addSubject() {
				var name = this.newSubject.trim();
				if (!name) return;
				var exists = this.bookSubjects.some(function (s) {
					return s.name.toLowerCase() === name.toLowerCase();
				});
				if (!exists) {
					if (!this.book.subjects) this.book.subjects = [];
					this.book.subjects.push({ name: name });
				}
				this.newSubject = '';
			},
			removeSubject(idx) {
				this.book.subjects.splice(idx, 1);
			},
			validIsbn(i) {
				if (i == null) return false;
				return isValidIsbn(i);
			},
			async openScanner() {
				this.scannerOpen = true;
				this.scannerError = null;
				this.scannerHint = 'Point your camera at the barcode on the back of the book.';
				await this.$nextTick();

				try {
					const { Html5Qrcode, Html5QrcodeSupportedFormats } = await import('html5-qrcode');
					const self = this;
					const scanner = new Html5Qrcode('isbn-scanner-stream', { verbose: false });
					this.scannerControls = scanner;
					var tickCount = 0;

					await scanner.start(
						{ facingMode: 'environment' },
						{
							fps: 15,
							qrbox: function (vw, vh) {
								var w = Math.floor(vw * 0.9);
								var h = Math.floor(Math.min(vh * 0.5, 200));
								return { width: w, height: h };
							},
							videoConstraints: {
								facingMode: { ideal: 'environment' },
								width: { ideal: 1920 },
								height: { ideal: 1080 },
								advanced: [{ focusMode: 'continuous' }],
							},
							formatsToSupport: [
								Html5QrcodeSupportedFormats.EAN_13,
								Html5QrcodeSupportedFormats.EAN_8,
								Html5QrcodeSupportedFormats.UPC_A,
								Html5QrcodeSupportedFormats.UPC_E,
							],
							experimentalFeatures: {
								useBarCodeDetectorIfSupported: true,
							},
						},
						function (decodedText) {
							console.log('scanner decode: ' + decodedText);
							var cleaned = decodedText.replace(/[^0-9Xx]/g, '');
							if (isValidIsbn(cleaned)) {
								self.book.isbn = cleaned;
								self.closeScanner();
							} else {
								self.scannerHint = 'Read "' + cleaned + '" but it is not a valid ISBN — keep trying.';
							}
						},
						function (errorMessage) {
							tickCount++;
							if (tickCount % 30 === 0) {
								console.log('scanner tick ' + tickCount + ': ' + errorMessage);
							}
						}
					);

					console.log('scanner started via html5-qrcode');
					try {
						var track = scanner.getRunningTrackSettings ? scanner.getRunningTrackSettings() : null;
						console.log('scanner video settings: ' + JSON.stringify(track));
					} catch (e) { /* ignore */ }
				} catch (err) {
					console.log('scanner start: ' + err);
					this.scannerError = (err && err.message) ? err.message : 'Unable to access the camera.';
				}
			},
			closeScanner() {
				var scanner = this.scannerControls;
				this.scannerControls = null;
				this.scannerOpen = false;
				this.scannerError = null;

				if (scanner) {
					var cleanup = function () { try { scanner.clear(); } catch (e) { /* ignore */ } };
					scanner.stop().then(cleanup).catch(cleanup);
				}
			},
			getByIsbn() {
				var root = this.$root.$refs.app;
				var self = this;

				function lookupIsbnCallback(bookObj) {
					if (bookObj) {
						self.book.title = bookObj.title;
						if (typeof bookObj.classifications !== 'undefined' && typeof bookObj.classifications.dewey_decimal_class !== 'undefined') {
							self.book.dewey_classification = bookObj.classifications.dewey_decimal_class.join();
						} else {
							self.book.dewey_classification = null;
						}
						if (typeof bookObj.classifications !== 'undefined' && typeof bookObj.classifications.lc_classifications !== 'undefined') {
							self.book.lc_classification = bookObj.classifications.lc_classifications.join();
						} else {
							self.book.lc_classification = null;
						}

						if (bookObj.publishers && bookObj.publishers.length > 0) {
							var pub = bookObj.publishers[0];
							self.book.publisher = typeof pub === 'object' ? pub.name : pub;
						} else {
							self.book.publisher = '';
						}

						if (bookObj.description) {
							self.book.description = bookObj.description;
						}

						// publish_year: prefer already-normalised value (from GB path), fall back to OL's publish_date string
						if (bookObj.publish_year) {
							self.book.publish_year = bookObj.publish_year;
						} else if (bookObj.publish_date) {
							var yearMatch = String(bookObj.publish_date).match(/(\d{4})/);
							if (yearMatch) self.book.publish_year = parseInt(yearMatch[1], 10);
						}

						// page_count: prefer normalised value (from GB path), fall back to OL's number_of_pages
						if (bookObj.page_count) {
							self.book.page_count = bookObj.page_count;
						} else if (bookObj.number_of_pages) {
							self.book.page_count = bookObj.number_of_pages;
						}

						if (bookObj.language) {
							self.book.language = bookObj.language;
						}

						if (bookObj.subjects_list && bookObj.subjects_list.length > 0) {
							self.book.subjects = bookObj.subjects_list.map(function (s) { return { name: s }; });
						}

						if (typeof bookObj.identifiers !== 'undefined') {
							$.each(bookObj.identifiers, function(k, v) {
								self.book[k] = v.join();
							});
						}

						if (typeof bookObj.authors !== 'undefined' && typeof bookObj.authors[0] !== 'undefined') {
							var authorOlKey = bookObj.authors[0].url;

							axios.get('/api/authors/open_library_ref:' + authorOlKey.split("/")[4]).then(function (olResponse) {

								if (olResponse.data.length === 0) {
									axios.post('/api/authors', {name: bookObj.authors[0].name, open_library_ref:authorOlKey.split("/")[4]}).then(function (sResponse) {
										self.book.author_id = sResponse.data.id;
										self.author = sResponse.data;

										root.setAlert('Found book and author from ISBN.', 'success');
									}).catch(function (error) {
										root.setAlert("Found book from ISBN, can't get author", 'warning');
										console.log(error);
									});
								} else {
									self.book.author_id = olResponse.data[0].id;
									self.author = olResponse.data[0];

									root.setAlert('Found book and author from ISBN.', 'success');
								}
							})
							.catch(function (error) {
								console.log(error);
								root.setAlert("Found book from ISBN, can't get author", 'warning');
							});

						} else {
							root.setAlert('Found book from ISBN', 'success');
						}

					} else {
						root.setAlert("Can't find book from ISBN", 'warning');
					}
				}

				root.setAlert('Looking up ISBN', 'loading');
				lookupIsbn(lookupIsbnCallback, self.book.isbn);
			},
			checkForm(e) {
				e.preventDefault();

				var root = this.$root.$refs.app;
				var parent = this.$parent;
				var formFields = JSON.parse(JSON.stringify(this.book));
				var id = this.book.id;

				this.errors = [];
				if (!formFields.title) {
					root.setAlert('Title required', 'danger');

				} else {
					root.setAlert('Saving', 'loading');

					delete formFields.id;
					delete formFields.created_at;
					delete formFields.updated_at;

					// Send subjects as plain name strings; server does find-or-create
					formFields.subjects = this.bookSubjects.map(function (s) { return s.name; });

					if (this.$parent.mode == 'edit') {
						axios.put('/api/books/'+id, formFields).then(response => {
							root.setAlert('Saved record', 'success');
							parent.book = response.data;
							parent.mode = 'view';
						}).catch(error => {
							root.setAlert('Unable to save record', 'danger');
							console.log(error);
						});
					} else {
						axios.post('/api/books', formFields).then(function (response) {
							root.setAlert('Created new book record', 'success');
							parent.book = response.data;
							parent.mode = 'view';
						}, function (error) {
							root.setAlert('Unable to save record', 'danger');
							console.log(error);
						});
					}
				}
			},
			closeForm() {
				this.$root.$refs.app.clearAlert();
				this.$parent.mode = 'index';
			},
			selectAuthor(data) {
				var self = this;

				this.book.author_id = data.id;

				axios.get('/api/authors/' + data.id).then(function (response) {
					self.author = response.data;
				})
				.catch(function (error) {
					console.log(error);
				});

				this.authorSelector = false;
			},
			createAuthor() {
				var self = this;
				var root = this.$root.$refs.app;
				var newAuthor = prompt("Please enter Author's name", "");

				root.setAlert('Adding author', 'loading');

				if (newAuthor != null && newAuthor != "") {
					axios.post('/api/authors', {name: newAuthor}).then(function (response) {
						self.book.author_id = response.data.id;
						self.author = response.data;

						root.setAlert('Added author for book', 'success');
					}).catch(function (error) {
						root.setAlert("Can't add author", 'danger');
						console.log(error);
					});
				}

				this.authorSelector = false;
			}
		},
		props: ['book']
	}


</script>
