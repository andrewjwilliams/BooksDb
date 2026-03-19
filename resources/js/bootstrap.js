import _ from 'lodash';
window._ = _;

import Popper from 'popper.js';
window.Popper = Popper;

import $ from 'jquery';
window.$ = window.jQuery = $;

import 'bootstrap';

import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
