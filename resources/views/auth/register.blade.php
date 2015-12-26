@extends('layouts.default')

@section('body-class') valign-wrapper register @endsection

@section('content')

<main class="row container">
	<section class="card-panel white col l6 offset-l3 s12 valign	">
		<img src="{{ URL::to('/') }}/images/logo.png" class="form-logo"/>

		<h3 class="center-align"> Register </h3>
		@foreach ($errors->all() as $message)
	        <li>{{ $message }}</li>
	    @endforeach

		{!! Form::open([
			"route" => ['user/store'],
			"method" => "POST",
			'class' => 'row col s12'
		]) !!}
		<div class="row">
			<div class="input-field">
				{!! Form::label('student_id', 'Student Number') !!}
				{!! Form::text('student_id', null, ["class" => "example"] ) !!}
			</div>
		</div>
		<div class="row">
			<div class="input-field">
				{!! Form::label('uid', 'Username (lowercase-only)') !!}
				{!! Form::text('uid', null, ["class" => "example"] ) !!}
			</div>
		</div>
		
		<div class="row">
			<div class="input-field">
				{!! Form::label('password', 'Password') !!}
				{!! Form::password('password', null, ["class" => "example"] ) !!}
			</div>
		</div>
		<div class="row">
			<div class="input-field">
				{!! Form::label('password_confirmation', 'Confirm Password') !!}
				{!! Form::password('password_confirmation', null, ["class" => "example"] ) !!}
			</div>
		</div>
		<button class="btn waves-effect waves-light" type="submit" name="action">Register
			<i class="mdi-content-send right"></i>
		</button>
		{!! Form::close() !!}

		<br /> 
	</section>

</main>

@endsection
