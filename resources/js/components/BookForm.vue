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
							<button id="btn_isdn" v-on:click="getByIsbn()" class="btn btn-info" type="button" :disabled=!validIsbn(book.isbn)>Lookup</button>
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
									<label for='goodreads'>Goodreads</label><input type="text" v-model="book.goodreads" id="goodreads" name="goodreads" class="form-control" placeholder="Goodreads" aria-label="Goodreads">`
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

<script>
	import DatatableActionButton from './DatatableActionButton.vue';

	export default {
		data() {
			return {
				author: {},
				authorSelector : false,
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

			if (this.book.author_id) {
				var response = axios.get('/api/authors/' + this.book.author_id).then(function (response) {
					self.author  =  response.data ;
				})
				.catch(function (error) {
					console.log(error);
				});
			}
		},
		
		methods: {
			validIsbn : function(i) {
				if (i==null) return false;
				return isValidIsbn(i);
			},
			getByIsbn : function() {
				var parent = this.$parent;
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

						if (typeof bookObj.publishers !== 'undefined') {
							self.book.lc_classification = bookObj.publishers.join();
						} else {
							self.book.lc_classification = '';
						}

						if (typeof bookObj.identifiers !== 'undefined') {
							$.each(bookObj.identifiers, function(k, v) {					// Loop each identifier
								self.book[k] = v.join();
							});
						}

						if (typeof bookObj.authors !== 'undefined' && typeof bookObj.authors[0] !== 'undefined') {		// Is there an author set?
							var authorOlKey = bookObj.authors[0].url;													// Yes - so get the author details from open library

							axios.get('/api/authors/open_library_ref:' + authorOlKey.split("/")[4]).then(function (olResponse) {
								
								if (olResponse.data.length === 0) {		// No response so new (to us) author
									axios.post('/api/authors', {name: bookObj.authors[0].name, open_library_ref:authorOlKey.split("/")[4]}).then(function (sResponse, status, request) {
										self.book.author_id = sResponse.data.id;
										self.author  =  sResponse.data;

										root.setAlert('Found book and author from ISBN.', 'success');	
									}).catch(function (error) {
										root.setAlert("Found book from ISBN, can't get author", 'warning');	
										console.log(error);
									});
								} else {
									self.book.author_id = olResponse.data[0].id;
									self.author  =  olResponse.data[0];

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
				lookupIsbn(lookupIsbnCallback, $('#isbn').val());
			},
			checkForm: function (e) {
				e.preventDefault();

				var root = this.$root.$refs.app;
				var parent = this.$parent;
				var formFields = JSON.parse(JSON.stringify(this.book));	// Get fields from the form ( copy not by reference so we can manipulate before saving)
				var id = this.book.id;								// and the id

				this.errors = [];
				if (!formFields.title) { 
					root.setAlert('Title required', 'danger');
					
				} else {
					root.setAlert('Saving', 'loading');

					delete formFields.id;							// We dont want id
					delete formFields.created_at;					// or the created
					delete formFields.updated_at;					// or updated times

					if (this.$parent.mode == 'edit') {
						axios.put('/api/books/'+id, formFields ).then(response => {
							root.setAlert('Saved record', 'success');
							console.log(response);
							parent.mode = 'index';
						}).catch(error => {
							root.setAlert('Unable to save record', 'danger');
							console.log(error);
						});
					} else {
						axios.post('/api/books', formFields).then(function (response, status, request) {
							root.setAlert('Created new book record', 'success');
							parent.mode = 'index';
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
			selectAuthor: function (data) {
				var self = this;

				this.book.author_id = data.id;

				var response = axios.get('/api/authors/' + data.id).then(function (response) {
					self.author  =  response.data;
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

				if (newAuthor != null && newAuthor  != "") {
					axios.post('/api/authors', {name: newAuthor}).then(function (response, status, request) {
						self.book.author_id = response.data.id;
						self.author  =  response.data;

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
	
	function lookupIsbn(callback, value) {
		$.ajax({
			type: 'get',
			url: 'https://openlibrary.org/api/books.json?bibkeys=ISBN:' + value + '&jscmd=data',
			data: {},
			success: function (response) {
				var r;

				if (typeof response['ISBN:' + value] !== 'undefined') {
					r = response['ISBN:' + value];
				} else {
					r = false;
				}
				callback(r);
			},
			error: function (xhr, status, error) {
				console.log('lookupIsbn: ' + status);
				console.log('lookupIsbn: ' + error);
				callback(false);
			}
		});
	}

	var isValidIsbn = function(str) {

		var sum,
			weight,
			digit,
			check,
			i;

		str = str.replace(/[^0-9X]/gi, '');

		if (str.length != 10 && str.length != 13) {
			return false;
		}

		if (str.length == 13) {
			sum = 0;
			for (i = 0; i < 12; i++) {
				digit = parseInt(str[i]);
				if (i % 2 == 1) {
					sum += 3*digit;
				} else {
					sum += digit;
				}
			}
			check = (10 - (sum % 10)) % 10;
			return (check == str[str.length-1]);
		}

		if (str.length == 10) {
			weight = 10;
			sum = 0;
			for (i = 0; i < 9; i++) {
				digit = parseInt(str[i]);
				sum += weight*digit;
				weight--;
			}
			check = 11 - (sum % 11);
			if (check == 10) {
				check = 'X';
			}
			return (check == str[str.length-1].toUpperCase());
		}
	}
</script>