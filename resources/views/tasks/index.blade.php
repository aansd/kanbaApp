@extends('layouts.master')

    @section('pageTitle', $pageTitle)

    @section('main')
    @php
    use App\Models\Task;
    @endphp
 
    <div class="task-list-container">
      <h1 class="task-list-heading">Task List</h1>
  
      <div class="task-list-table-head">
        <div class="task-list-header-task-name">Task Name</div>
        <div class="task-list-header-detail">Detail</div>
        <div class="task-list-header-due-date">Due Date</div>
        <div class="task-list-header-progress">Progress</div>
      </div>
  
      @foreach ($tasks as $item)
        <div class="table-body">
          <div class="table-body-task-name">
            @if ($item->status == 'completed')
            <span class="material-icons check-icon-completed check-icon " >
              check_circle
            </span>
            @else
            <form method="post" action="{{ route('tasks.move', ['id' => $item->id, 'status' =>Task::STATUS_COMPLETED]) }}" id="setcompleted-{{$item->id}}">
              @method('patch')
              @csrf
            <span class="material-icons check-icon " onclick="document.getElementById('setcompleted-{{$item->id}}').submit()">check_circle</span>
          </form>
            @endif
            {{  $item->name }}
          </div>
          <div class="table-body-detail"> {{ $item->detail }} </div>
          <div class="table-body-due-date"> {{ $item->due_date }} </div>
          <div class="table-body-progress">
            @switch($item->status)
              @case('in_progress')
                In Progress
                @break
              @case('in_review')
                Waiting/In Review
                @break
              @case('completed')
                Completed
                @break
              @default
                Not Started
            @endswitch
          </div>
          <a href="{{ route('tasks.edit', ['id' => $item->id]) }}">Edit</a>
          &nbsp;
          <a href="{{ route('tasks.delete', ['id' => $item->id]) }}">Delete</a>
        </div>
        @endforeach
        </div>
      </div>
   @endsection
  