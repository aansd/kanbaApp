    @extends('layouts.master')

    @section('pageTitle', $pageTitle)

    @section('main')
  <div class="task-list-container">
    <h1 class="task-list-heading">Task List</h1>

    <div class="task-list-task-buttons">
        <a href="{{ route('tasks.create') }}">
          <button  class="task-list-button">
            <span class="material-icons">add</span>Add task
          </button>
        </a>
      </div>

    <div class="task-list-table-head">
      <div class="task-list-header-task-name">Task Name</div>
      <div class="task-list-header-detail">Detail</div>
      <div class="task-list-header-due-date">Due Date</div>
      <div class="task-list-header-progress">Progress</div>
    </div>


    @foreach ($tasks as $item)
        
    <div class="table-body">
      <div class="table-body-task-name">
        <div>
          <form method="post" action="{{route('tasks.complete', ['id' => $item->id])}}">
        <button class="material-icons @if ($item->status == 'completed') check-icon-completed @else check-icon @endif" style="border: none; background-color: transparent;">
          check_circle
        </button>
        @method('patch')
        @csrf
      
      </form>
        </div>
        {{$item->name}}
      </div>
      <div class="table-body-detail">{{$item->detail}}</div>
      <div class="table-body-due-date">{{$item->due_date}}</div>
      <div class="table-body-progress">
        @switch($item->status)
            @case('not_started')
                Not Started
                @break
            @case('in_progress')
                In Progress
                @break
            @case('in_review')
                Waiting/In Review
                @break
            @case('completed')
                Complated
        @endswitch
      </div>
          <a href="{{ route('tasks.edit', ['id' => $item->id]) }}">Edit</a>
          &nbsp;
          <a href="{{ route('tasks.delete', ['id' => $item->id]) }}">Delete</a>
    </div>
    @endforeach
  </div>
  @endsection
