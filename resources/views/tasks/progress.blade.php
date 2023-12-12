@extends('layouts.master')

@section('pageTitle', $pageTitle)

@section('main')
@php
use App\Models\Task;
@endphp
<div class="task-list-container">
<h1 class="task-list-heading">{{ $pageTitle }}</h1>

<div class="task-progress-board">
  @include('partials.task_column', [
    'title' => 'Not Started',
    'status' => Task::STATUS_NOT_STARTED,
    'tasks' => $tasks[Task::STATUS_NOT_STARTED],
    'leftStatus' => null,
    'rightStatus' => Task::STATUS_IN_PROGRESS,
    ])
    
    @include('partials.task_column', [
      'title' => 'In Progress',
      'status' => Task::STATUS_IN_PROGRESS,
      'tasks' => $tasks[Task::STATUS_IN_PROGRESS],
      'leftStatus' => Task::STATUS_NOT_STARTED,
      'rightStatus' => Task::STATUS_IN_REVIEW,
      ])
      
      @include('partials.task_column', [
        'title' => 'In Review',
        'status' => Task::STATUS_IN_REVIEW,
        'tasks' => $tasks[Task::STATUS_IN_REVIEW],
        'leftStatus' => Task::STATUS_IN_PROGRESS,
        'rightStatus' => Task::STATUS_COMPLETED,
        ])
        
        @include('partials.task_column', [
          'title' => 'Completed',
          'status' => Task::STATUS_COMPLETED,
          'tasks' => $tasks[Task::STATUS_COMPLETED],
          'leftStatus' => Task::STATUS_IN_REVIEW,
          'rightStatus' => null,
          ])
</div>
</div>
@endsection