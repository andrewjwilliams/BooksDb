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
							'alert-secondary': alert.type == 'secondary',
							'alert-secondary': alert.type == 'loading',
							'alert-info': alert.type == 'info',
							'alert-success': alert.type == 'success',
							'alert-warning': alert.type == 'warning',
							'alert-danger': alert.type == 'danger',
							'd.none': alert.hidden
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

							<GChart
								type="BarChart"
								:data="chartData"
								:options="chartOptions"
							/>
						</div>
						<book v-if="mode=='book'"></book>
						<author v-if="mode=='author'"></author>
					</div><!-- /col -->
				</div><!-- /row -->
			</div><!-- /container -->
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
        setAlert : function(msg, type) {
            this.alert.msg = msg;
            this.alert.type = type;
            this.alert.hidden = false;
        },
        clearAlert : function() {
			this.alert.hidden = true;
			this.alert.msg = '';
            this.alert.type = '';
		},
		refreshGraph :  function() {
			var self = this;

			var response = axios.get('/api/books/count').then(function (response) {
				self.num.books  =  response.data.count;
				self.chartData[1][1] =  parseInt(response.data.count);
			})
			.catch(function (error) {
				console.log(error);
			});

			var response = axios.get('/api/authors/count').then(function (response) {
				self.num.authors  =  response.data.count;
				self.chartData[2][1] =  parseInt(response.data.count);
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