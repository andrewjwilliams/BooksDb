/**
 * Fetch author metadata from Open Library and return a normalised object
 * suitable for posting to /api/authors.
 *
 * @param {string} olRef  - Open Library author key, e.g. "OL23919A"
 * @param {function} callback - called with the enriched author object, or null on failure
 */
export function lookupOlAuthor(olRef, callback) {
    $.ajax({
        type: 'get',
        url: 'https://openlibrary.org/authors/' + olRef + '.json',
        success: function (data) {
            var author = {};

            if (data.name)         author.name         = data.name;
            if (data.fuller_name)  author.fuller_name  = data.fuller_name;
            if (data.personal_name && !data.fuller_name) author.fuller_name = data.personal_name;
            if (data.birth_date)   author.birth_date   = data.birth_date;
            if (data.death_date)   author.death_date   = data.death_date;

            if (data.bio) {
                author.bio = typeof data.bio === 'string' ? data.bio : (data.bio.value || '');
            }

            if (data.remote_ids && typeof data.remote_ids === 'object') {
                author.remote_ids = data.remote_ids;
            }

            if (data.links && data.links.length > 0) {
                author.links = data.links
                    .filter(function (l) { return l.url; })
                    .map(function (l) { return { title: l.title || l.url, url: l.url }; });
            }

            callback(author);
        },
        error: function () {
            callback(null);
        }
    });
}

// Known remote ID services: { key: [urlPrefix, label] }
export const REMOTE_ID_SERVICES = {
    viaf:         ['https://viaf.org/viaf/',                          'VIAF'],
    wikidata:     ['https://www.wikidata.org/wiki/',                  'Wikidata'],
    goodreads:    ['https://www.goodreads.com/author/show/',          'Goodreads'],
    librarything: ['https://www.librarything.com/author/',            'LibraryThing'],
    imdb:         ['https://www.imdb.com/name/',                      'IMDb'],
    isni:         ['https://isni.org/isni/',                          'ISNI'],
    lc_naf:       ['https://id.loc.gov/authorities/names/',           'LC Name Authority'],
    musicbrainz:  ['https://musicbrainz.org/artist/',                 'MusicBrainz'],
    storygraph:   ['https://app.thestorygraph.com/authors/',          'StoryGraph'],
};
