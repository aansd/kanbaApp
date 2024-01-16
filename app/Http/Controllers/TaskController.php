<?php

namespace App\Http\Controllers;

use App\Models\Task;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Redis;


class TaskController extends Controller
{
    public function __construct()
    {

    }
    
    public function index(Request $request)
    {
        $pageTitle = 'Task List';
        if (Gate::allows('viewAnyTask', Task::class)) {
            $tasks = Task::all();
        } else {
            $tasks = Task::where('user_id', Auth::user()->id)->get();
        }
        $request->session()->put('previousPage', 'tasks.index');
        return view('tasks.index', [
            'pageTitle' => $pageTitle, 
            'tasks' => $tasks,
        ]);
    }

    public function create(Request $request)
    {
        $pageTitle = 'Create Task';
        $status = $request->status;
        session()->put('previousPage', url()->previous());
        return view('tasks.create', ['pageTitle' => $pageTitle, 'status' => $status]);
    }

    public function edit($id)
    {
        $pageTitle = 'Edit Task';
        $task = Task::find($id);
        if (Gate::denies('performAsTaskOwner', $task)) {
            Gate::authorize('updateAnyTask', Task::class);
        }
        session()->put('previousPage', url()->previous());
        return view('tasks.edit', ['pageTitle' => $pageTitle, 'task' => $task]);
    }

    public function store(Request $request)
    {
        $request->validate(
            [
                'name' => 'required',
                'due_date' => 'required',
                'status' => 'required',
            ],
            $request->all()
        );
    
        Task::create([
            'name' => $request->name,
            'detail' => $request->detail,
            'due_date' => $request->due_date,
            'status' => $request->status,
            'user_id' => Auth::user()->id

        ]);
        $previousPage = session('previousPage');

      
        session()->forget('previousPage');

        
        return redirect()->to($previousPage ?: route('tasks.index'));

    }

    public function update(Request $request, $id)
    {
        $request->validate(
            [
                'name' => 'required',
                'due_date' => 'required',
                'status' => 'required',
            ],
            $request->all()
        );

        $task = Task::find($id);
        if (Gate::denies('performAsTaskOwner', $task)) {
            Gate::authorize('updateAnyTask', Task::class);
        }
        $task->update([
            'name' => $request->name,
            'detail' => $request->detail,
            'due_date' => $request->due_date,
            'status' => $request->status,
            
        ]
    );
    $previousPage = session('previousPage');


       
        session()->forget('previousPage');

        
        return redirect()->to($previousPage ?: route('tasks.index'));
    }

    public function delete($id)
    {
        $pageTitle = 'Delete Task';
        $task = Task::find($id);
        if (Gate::denies('performAsTaskOwner', $task)) {
            Gate::authorize('deleteAnyTask', Task::class);
        }
        return view('tasks.delete', ['pageTitle' => $pageTitle, 'task' => $task]);
    }

    public function destroy($id)
    {
        $task = Task::find($id);
        if (Gate::denies('performAsTaskOwner', $task)) {
            Gate::authorize('deleteAnyTask', Task::class);
        }
        $task->delete();
        return redirect()->route('tasks.index');
    }


    public function progress(Request $request)
    {
        $title = 'Task Progress';
        if (Gate::allows('viewAnyTask', Task::class)) {
            $tasks = Task::all();
        } else {
            $tasks = Task::where('user_id', Auth::user()->id)->get();
        }
       
        $filteredTasks = $tasks->groupBy('status');
        // $tasks = [
            //     'not_started' => $filteredTasks->get('not_started' , []),
            //     'in_progress' => $filteredTasks->get('in_progress', []),
            //     'completed' => $filteredTasks->get('completed', []),
            //     'in_review' => $filteredTasks->get('in_review', []),
            // ];
            $tasks = [
                Task::STATUS_NOT_STARTED => $filteredTasks->get(
                    Task::STATUS_NOT_STARTED, []
                ),
                Task::STATUS_IN_PROGRESS => $filteredTasks->get(
                Task::STATUS_IN_PROGRESS, []
            ),
            Task::STATUS_IN_REVIEW => $filteredTasks->get(
                Task::STATUS_IN_REVIEW, []
            ),
            Task::STATUS_COMPLETED => $filteredTasks->get(
                Task::STATUS_COMPLETED, []
            ),
        ];
        $request->session()->put('previousPage', 'tasks.progress');
        return view('tasks.progress', [
            'pageTitle' => $title,
            'tasks' => $tasks,
        ]);
    }

    public function move(int $id, Request $request)
    {
    $task = Task::findOrFail($id);
    if (Gate::denies('performAsTaskOwner', $task)) {
        Gate::authorize('updateAnyTask', Task::class);
    }
    $task->update([
        'status' => $request->status,
    ]);

    $url = url()->previous();
    if (strpos($url, route('tasks.progress')) )
     {
        return redirect()->route('tasks.progress');
     } 
    elseif (strpos($url, route('tasks.index')) ) 
     {
        return redirect()->route('tasks.index');
     } 
     else 
     {
        return back()->withInput();
     }
    }

    public function home()
    {
    $tasks = Task::where('user_id', auth()->id())->get();

    $completed_count = $tasks
        ->where('status', Task::STATUS_COMPLETED)
        ->count();

    $uncompleted_count = $tasks
        ->whereNotIn('status', Task::STATUS_COMPLETED)
        ->count();

    return view('home', [
        'completed_count' => $completed_count,
        'uncompleted_count' => $uncompleted_count,
    ]);
    }
}
