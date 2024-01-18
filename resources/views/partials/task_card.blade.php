@php
use App\Models\Task;
@endphp
<div class="task-progress-card">
    <div class="task-progress-card-left">
      @canany(['updateAnyTask', 'performAsTaskOwner'], $task)
      @if ($task->status == 'completed')
        <div class="material-icons task-progress-card-top-checked" >check_circle</div>      
        @else       
        <form method="post" action="{{ route('tasks.move', ['id' => $task->id, 'status' =>Task::STATUS_COMPLETED]) }}" id="setcompleted-{{$task->id}}">
          @method('patch')
          @csrf
          <div class="material-icons task-progress-card-top-check" onclick="document.getElementById('setcompleted-{{$task->id}}').submit()">check_circle</div>
          @endcan
          @endif
      </form>
      
        @canany(['updateAnyTask', 'performAsTaskOwner'], $task)
      <a href="{{ route('tasks.edit', ['id' => $task->id]) }}" class="material-icons task-progress-card-top-edit">more_vert</a>
      @endcan
    </div>
    <p class="task-progress-card-title">{{ $task->name }}</p>
    <div>
      <p>{{ $task->detail }}</p>
    </div>
    <div>
      <p>Due on {{ $task->due_date }}</p>
    </div>
    <div>
      <p>Owner: <strong>{{ $task->user->name }}</strong></p>
    </div>
    @if ($task->files)
    <div>
      @foreach ($task->files as $file)
      <div class="task-progress-card-file">
        <span class="material-icons">file_open</span>
        <a
          target="_blank"
          href="{{ route('tasks.files.show', ['task_id' => $task->id, 'id' => $file->id]) }}"
        >
          {{ $file->filename }}
        </a>
      </div>
      @endforeach
    </div>
  @endif

    <div class="@if ($leftStatus) task-progress-card-left @else task-progress-card-right @endif">
      @canany(['updateAnyTask', 'performAsTaskOwner'], $task)
      @if ($leftStatus)
      <form
      action="{{ route('tasks.move', ['id' => $task->id, 'status' => $leftStatus]) }}" 
      method="POST">
      @method('PATCH')
      @csrf
        <button class="material-icons">chevron_left</button>
      </form>
      @endcan
      @endif
      @canany(['updateAnyTask', 'performAsTaskOwner'], $task)
      @if ($rightStatus)
      <form
        action="{{ route('tasks.move', ['id' => $task->id, 'status' => $rightStatus]) }}"
        method="POST">
        @method('PATCH')
        @csrf
        <button class="material-icons">chevron_right</button>
      </form>
      @endcan
        @endif
      </div>
    </div>