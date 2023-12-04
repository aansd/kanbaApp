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
        $tasks = Task::all();
    }

    public function create()
    {
        $pageTitle = 'Create Task';
        return view('tasks.create', ['pageTitle' => $pageTitle]);
    }

    public function edit($id)
    {
        
        $taks = Task::find($id);
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
    

}
