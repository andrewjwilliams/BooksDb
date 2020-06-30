<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
		<script src="{{ asset('js/app.js') }}" defer></script>

        <title>BooksDb</title>

        <!-- Fonts -->
        <link href="https://fonts.googleapis.com/css?family=Nunito:200,600" rel="stylesheet">

        <!-- Styles -->

		<link href="{{ asset('css/app.css') }}" rel="stylesheet">
    </head>
	<div id="app">
    
        <!-- Navigation -->
        <navigation></navigation>


        <app ref="app"></app>
					
    </div>

    </body>
</html>
