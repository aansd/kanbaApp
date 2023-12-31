<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function __construct()
    {

    }
    
    public function index()
    {
        $pageTitle = 'Task List';
        $tasks = Task::all(); 
        return view('tasks.index', [
            'pageTitle' => $pageTitle, 
            'tasks' => $tasks,
        ]);
    }

    public function create(Request $request)
    {
        $pageTitle = 'Create Task';
        $status = $request->status;
        return view('tasks.create', ['pageTitle' => $pageTitle, 'status' => $status]);
    }

    public function edit($id)
    {
        $pageTitle = 'Edit Task';
        $task = Task::find($id);
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
        ]);
        return redirect()->route('tasks.index');
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
        $task->update([
            'name' => $request->name,
            'detail' => $request->detail,
            'due_date' => $request->due_date,
            'status' => $request->status,
        ]
    );
        return redirect()->route('tasks.index');
    }

    public function delete($id)
    {
        $pageTitle = 'Delete Task';
        $task = Task::find($id);
        return view('tasks.delete', ['pageTitle' => $pageTitle, 'task' => $task]);
    }

    public function destroy($id)
    {
        $task = Task::find($id);
        $task->delete();
        return redirect()->route('tasks.index');
    }


    public function progress()
    {
        $title = 'Task Progress';
        $tasks = Task::all();
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
        return view('tasks.progress', [
            'pageTitle' => $title,
            'tasks' => $tasks,
        ]);
    }

    public function move(int $id, Request $request)
    {
    $task = Task::findOrFail($id);

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
}
