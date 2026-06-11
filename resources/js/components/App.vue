<template>
	<div>
		<main class="py-4">
			<div class="container">
				<div class="row justify-content-center">
					<div class="col-md-10">
						<br>
						<div v-bind:class="{
							'alert': true,
							'alert-primary': alert.type == 'primary',
							'alert-secondary': alert.type == 'secondary' || alert.type == 'loading',
							'alert-info': alert.type == 'info',
							'alert-success': alert.type == 'success',
							'alert-warning': alert.type == 'warning',
							'alert-danger': alert.type == 'danger',
							'd-none': alert.hidden
						}"
						role="alert">
							<div v-if="alert.type=='loading'" class="spinner-border" role="status">
								<span class="sr-only">Loading...</span>
							</div>
							{{ alert.msg }}
						</div>

						<div v-if="mode=='index'">
							<h1>Welcome!</h1>

							<p>
								Welcome to BooksDb. Please make your choice from the menu above.
							</p>
							
							<div class="row">
								<div class="col-md-6 mb-3">
									<div class="card h-100">
										<div class="card-body d-flex justify-content-between align-items-center">
											<div>
												<h5 class="card-title"><font-awesome-icon :icon="['fas', 'book']"></font-awesome-icon> Books</h5>
												<span class="badge bg-secondary fs-5">{{ num.books }}</span>
											</div>
											<div class="d-flex flex-column gap-2">
												<a href="#" class="btn btn-sm btn-outline-primary" v-on:click="mode = 'book'"><font-awesome-icon :icon="['fas', 'list']"></font-awesome-icon> List</a>
												<a href="#" class="btn btn-sm btn-outline-success" v-on:click="addBook"><font-awesome-icon :icon="['fas', 'plus']"></font-awesome-icon> Add</a>
											</div>
										</div>
									</div>
								</div>
								<div class="col-md-6 mb-3">
									<div class="card h-100">
										<div class="card-body d-flex justify-content-between align-items-center">
											<div>
												<h5 class="card-title"><font-awesome-icon :icon="['fas', 'user']"></font-awesome-icon> Authors</h5>
												<span class="badge bg-secondary fs-5">{{ num.authors }}</span>
											</div>
											<div class="d-flex flex-column gap-2">
												<a href="#" class="btn btn-sm btn-outline-primary" v-on:click="mode = 'author'"><font-awesome-icon :icon="['fas', 'list']"></font-awesome-icon> List</a>
											</div>
										</div>
									</div>
								</div>
							</div>

							<GChart
								type="BarChart"
								:data="chartData"
								:options="chartOptions"
							/>
						</div>
						<book v-if="mode=='book'" ref="book"></book>
						<author v-if="mode=='author'"></author>
					</div>
				</div>
			</div>
    </main>
	</div>
</template>
<script>

import { GChart } from 'vue-google-charts'

export default {
    data() {
        return {
			mode: 'index',
			num: {
				books: 0,
				authors: 0
			},
			chartData: [
				['Item', ''],
				['Books', 0],
				['Authors', 0],
			],
			chartOptions: {
				chart: {
					title: 'Items in Database'
				},
				hAxis: {
					baseline: 0
				},
				legend: {
					position: 'none'
				}
			},
			alert: {
				msg: '',
				type: '',
				hidden: true
			},
        }
    },
    methods: {
        setAlert(msg, type) {
            this.alert.msg = msg;
            this.alert.type = type;
            this.alert.hidden = false;
        },
        clearAlert() {
			this.alert.hidden = true;
			this.alert.msg = '';
            this.alert.type = '';
		},
		addBook() {
			this.mode = 'book';
			this.$nextTick(() => this.$refs.book.create());
		},
		refreshGraph() {
			var self = this;

			axios.get('/api/books/count').then(function (response) {
				self.num.books = response.data.count;
				self.chartData[1][1] = parseInt(response.data.count);
			})
			.catch(function (error) {
				console.log(error);
			});

			axios.get('/api/authors/count').then(function (response) {
				self.num.authors = response.data.count;
				self.chartData[2][1] = parseInt(response.data.count);
			})
			.catch(function (error) {
				console.log(error);
			});
		}
	},
	mounted() {
		this.refreshGraph();
	},
	components: {
		GChart
	}
}
</script>
