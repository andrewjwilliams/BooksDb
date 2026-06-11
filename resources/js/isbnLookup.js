export function lookupIsbn(callback, value) {
	lookupIsbnOnce(value, function (result) {
		if (result) {
			enrichBook(result, value, callback);
			return;
		}

		var alt = alternateIsbnForm(value);
		if (!alt) {
			lookupGoogleBooks(value, function (gb) { callback(gb || false); });
			return;
		}

		console.log('lookupIsbn: retrying with ' + alt);
		lookupIsbnOnce(alt, function (altResult) {
			if (altResult) {
				enrichBook(altResult, value, callback);
			} else {
				lookupGoogleBooks(value, function (gb) { callback(gb || false); });
			}
		});
	});
}

function enrichBook(book, isbn, callback) {
	var afterWorks = function () {
		var needsMore = !book.description
			|| !book.publishers
			|| !book.identifiers
			|| !book.identifiers.isbn_10
			|| !book.identifiers.isbn_13
			|| !book.publish_year
			|| !book.page_count
			|| !book.language
			|| !book.subjects_list
			|| book.subjects_list.length === 0;

		if (!needsMore) {
			callback(book);
			return;
		}

		lookupGoogleBooks(isbn, function (gb) {
			if (gb) {
				if (!book.description && gb.description) book.description = gb.description;
				if (!book.publishers && gb.publishers) book.publishers = gb.publishers;
				if (!book.publish_year && gb.publish_year) book.publish_year = gb.publish_year;
				if (!book.page_count && gb.page_count) book.page_count = gb.page_count;
				if (!book.language && gb.language) book.language = gb.language;
				if ((!book.subjects_list || book.subjects_list.length === 0) && gb.subjects_list && gb.subjects_list.length > 0) {
					book.subjects_list = gb.subjects_list;
				}
				if (gb.identifiers) {
					book.identifiers = book.identifiers || {};
					$.each(gb.identifiers, function (k, v) {
						if (!book.identifiers[k]) book.identifiers[k] = v;
					});
				}
			}
			callback(book);
		});
	};

	// OL jscmd=data returns subjects as [{name, url}] on the edition record itself
	if (!book.subjects_list && book.subjects && book.subjects.length > 0) {
		book.subjects_list = book.subjects.map(function (s) {
			return typeof s === 'object' ? s.name : s;
		}).slice(0, 15);
	}

	if (book.works && book.works[0] && book.works[0].key) {
		$.ajax({
			type: 'get',
			url: 'https://openlibrary.org' + book.works[0].key + '.json',
			success: function (works) {
				if (!book.description) {
					if (typeof works.description === 'string') {
						book.description = works.description;
					} else if (works.description && works.description.value) {
						book.description = works.description.value;
					}
				}
				// Works subjects are flat strings; use them if edition had none
				if (!book.subjects_list && works.subjects && works.subjects.length > 0) {
					book.subjects_list = works.subjects.slice(0, 15);
				}
				afterWorks();
			},
			error: function () { afterWorks(); }
		});
	} else {
		afterWorks();
	}
}

function lookupGoogleBooks(isbn, callback) {
	$.ajax({
		type: 'get',
		url: 'https://www.googleapis.com/books/v1/volumes?q=isbn:' + isbn,
		success: function (response) {
			if (!response.items || response.items.length === 0) {
				callback(null);
				return;
			}
			var v = response.items[0].volumeInfo || {};
			var converted = {
				title: v.title,
				authors: (v.authors || []).map(function (n) { return { name: n }; }),
				description: v.description || null,
				identifiers: {},
				publish_year: null,
				page_count: v.pageCount || null,
				language: v.language || null,
				subjects_list: v.categories || [],
			};
			if (v.publisher) converted.publishers = [v.publisher];
			if (v.publishedDate) {
				var yearMatch = String(v.publishedDate).match(/(\d{4})/);
				if (yearMatch) converted.publish_year = parseInt(yearMatch[1], 10);
			}
			if (v.industryIdentifiers) {
				v.industryIdentifiers.forEach(function (id) {
					if (id.type === 'ISBN_10') converted.identifiers.isbn_10 = [id.identifier];
					if (id.type === 'ISBN_13') converted.identifiers.isbn_13 = [id.identifier];
				});
			}
			callback(converted);
		},
		error: function (xhr, status, error) {
			console.log('lookupGoogleBooks: ' + status + ' ' + error);
			callback(null);
		}
	});
}

function lookupIsbnOnce(value, callback) {
	$.ajax({
		type: 'get',
		url: 'https://openlibrary.org/api/books.json?bibkeys=ISBN:' + value + '&jscmd=data',
		data: {},
		success: function (response) {
			if (typeof response['ISBN:' + value] !== 'undefined') {
				callback(response['ISBN:' + value]);
			} else {
				callback(false);
			}
		},
		error: function (xhr, status, error) {
			console.log('lookupIsbn: ' + status);
			console.log('lookupIsbn: ' + error);
			callback(false);
		}
	});
}

function alternateIsbnForm(value) {
	var clean = String(value).replace(/[^0-9Xx]/g, '').toUpperCase();
	if (clean.length === 13 && clean.slice(0, 3) === '978') {
		return isbn13to10(clean);
	}
	if (clean.length === 10) {
		return isbn10to13(clean);
	}
	return null;
}

function isbn13to10(isbn13) {
	var core = isbn13.slice(3, 12);
	var sum = 0;
	for (var i = 0; i < 9; i++) sum += parseInt(core.charAt(i), 10) * (10 - i);
	var check = 11 - (sum % 11);
	var checkChar = check === 10 ? 'X' : (check === 11 ? '0' : String(check));
	return core + checkChar;
}

function isbn10to13(isbn10) {
	var core = '978' + isbn10.slice(0, 9);
	var sum = 0;
	for (var i = 0; i < 12; i++) {
		var d = parseInt(core.charAt(i), 10);
		sum += (i % 2 === 0) ? d : d * 3;
	}
	var check = (10 - (sum % 10)) % 10;
	return core + String(check);
}

export function isValidIsbn(str) {
	var sum, weight, digit, check, i;

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
