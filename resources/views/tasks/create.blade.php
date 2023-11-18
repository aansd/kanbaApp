<!DOCTYPE html>
<html lang="en">

<head>
<meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
  <link rel="stylesheet" href="{{ asset('style.css') }}">
  <title>TaskList</title>
</head>

<body>
    @extends('layouts.master')

    @section('pageTitle', $pageTitle)

    @section('main')
    <div class="form-container">
    <h1 class="form-title">{{ $pageTitle }}</h1>
    <form class="form">
      <div class="form-item">
        <label>Name:</label>
        <input class="form-input" type="text" value="" >
      </div>
  
      <div class="form-item">
        <label>Detail:</label>
        <textarea class="form-text-area"></textarea>
      </div>
  
      <div class="form-item">
        <label>Due Date:</label>
        <input class="form-input" type="date" value="" >
      </div>
  
      <div class="form-item">
        <label>Progress:</label>
        <select class="form-input">
          <option value="not_started">Not Started</option>
          <option value="in_progress">In Progress</option>
          <option value="in_review">Waiting/In Review</option>
          <option value="completed">Completed</option>
        </select>
      </div>
      <button type="button" class="form-button">Submit</button>
    </form>
  </div>
  @endsection
</body>
</html>