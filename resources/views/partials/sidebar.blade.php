
@php
use App\Models\Role;
use App\Models\User;
@endphp
<div class="sidebar-container">
  <a class="sidebar-link" href="{{ route('home') }}">
    <span class="material-icons sidebar-icon">home</span>
    <p class="sidebar-text">Home</p>
  </a>
 
  <a class="sidebar-link" href="{{ route('tasks.index') }}">
    <span class="material-icons sidebar-icon">list</span>
    <p class="sidebar-text">Task List</p>
  </a>

  <a class="sidebar-link" href="{{ route('tasks.progress') }}">
    <span class="material-icons sidebar-icon">check_box</span>
    <p class="sidebar-text">Task Progress</p>
  </a>
  @canany(['viewUserAndRole', 'performAsTaskOwner'], User::class)
  <a class="sidebar-link" href="{{ route('users.index') }}">
    <span class="material-icons sidebar-icon">group</span>
    <p class="sidebar-text">Users</p>
  </a>
  @endcan
  @if (Gate::any(['viewAnyRole', 'performAsTaskOwner'], Role::class))
  <a class="sidebar-link" href="{{ route('roles.index') }}">
    <span class="material-icons sidebar-icon">settings</span>
    <p class="sidebar-text">Roles</p>
  </a>
  @endif
  @if (Auth::check())
  <a class="sidebar-link" href=""
    onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
    <span class="material-icons sidebar-icon">logout</span>
    <p class="sidebar-text">Logout</p>
  </a>
  <form id="logout-form" action="{{route('auth.logout')}}" method="POST" style="display: none;">
    @csrf
  </form>
@endif
</div>